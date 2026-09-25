<?php
/**
 * Classe para gerar QR codes em formato SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Carregar FPDF
require_once __DIR__ . '/fpdf.php';

class QR_Etiqueta_Generator {
	/** Permitir compensar a orientação de alimentação configurada na impressora. */
	public function adicionar_controle_orientacao( $html, $width, $height ) {
		$width = floatval( $width );
		$height = floatval( $height );
		$controls = '<style>
		.orientation-control{position:fixed;left:8px;bottom:8px;z-index:1001;background:white;padding:6px;font:13px Arial}
		@media print{.orientation-control{display:none!important}}
		</style><style id="qr-orientation-style"></style>
		<div class="orientation-control"><label for="qr-orientation">Orientação: </label><select id="qr-orientation"><option value="landscape">Paisagem</option><option value="portrait">Retrato</option></select></div>
		<script>(function(){
		var width=' . $width . ', height=' . $height . ';
		var sheet=document.querySelector(".label-sheet, .individual-sheet");
		var select=document.getElementById("qr-orientation");
		function applyOrientation(){
			var rotated=(select.value==="landscape") !== (width>=height);
			var w=rotated?height:width, h=rotated?width:height;
			document.getElementById("qr-orientation-style").textContent=
				"@page{size:"+w+"mm "+h+"mm;margin:0}"+
				"@media print{html,body{width:"+w+"mm!important;height:"+h+"mm!important;max-height:"+h+"mm!important;overflow:hidden!important}}";
			sheet.style.transformOrigin="top left";
			sheet.style.transform=rotated?"translateX("+height+"mm) rotate(90deg)":"none";
			if(sheet.parentElement!==document.body){sheet.parentElement.style.overflow="visible";}
		}
		select.addEventListener("change",applyOrientation);
		applyOrientation();
		})();</script>';
		return str_replace( '</body>', $controls . '</body>', $html );
	}
	
	/** Layout compartilhado pela visualização, impressão e PDF. */
	public function layout_individual( $data ) {
		$codes = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $data ) ), 'strlen' ) );
		if ( count( $codes ) < 1 || count( $codes ) > 10 || count( array_unique( $codes ) ) !== count( $codes ) ) {
			throw new InvalidArgumentException( 'Informe de 1 a 10 números diferentes para o bipe individual.' );
		}
		foreach ( $codes as $code ) {
			if ( ! preg_match( '/^\d+$/', $code ) ) {
				throw new InvalidArgumentException( 'Cada linha deve conter apenas números.' );
			}
		}
		$s = qrEtiquetaGetSettings();
		$w = $s['paper_width_mm'];
		$h = $s['paper_height_mm'];
		$m = max( 2, $s['safe_margin_mm'] );
		// O modo individual usa a etiqueta inteira para acomodar os dez códigos.
		$best = null;
		for ( $cols = 1; $cols <= count( $codes ); $cols++ ) {
			$rows = ceil( count( $codes ) / $cols );
			$cw = ( $w - 2 * $m ) / $cols;
			$ch = ( $h - 2 * $m ) / $rows;
			$font = min( 3.0, ( $cw - 1 ) / ( max( array_map( 'strlen', $codes ) ) * 0.6 ) );
			$qr = min( $cw - 1, $ch - $font * 1.3 - 1, $s['qr_size_mm'] );
			if ( null === $best || $qr > $best['qr'] ) {
				$best = compact( 'codes', 'w', 'h', 'm', 'cols', 'rows', 'cw', 'ch', 'font', 'qr' );
			}
		}
		if ( $best['qr'] < 8 || $best['font'] < 1.2 ) {
			throw new InvalidArgumentException( 'Os códigos não cabem com tamanho legível nesta etiqueta. Use menos códigos ou aumente as dimensões da etiqueta.' );
		}
		// Centralizar a grade compacta como um bloco, com folga igual nas bordas.
		$best['cw'] = max( $best['qr'], max( array_map( 'strlen', $codes ) ) * $best['font'] * 0.6 ) + 1;
		$best['ch'] = $best['qr'] + $best['font'] * 1.3 + 1;
		$best['offset_x'] = ( $w - $best['cw'] * $best['cols'] ) / 2;
		$best['offset_y'] = ( $h - $best['ch'] * $best['rows'] ) / 2;
		return $best;
	}

	public function gerar_grade_individual( $data ) {
		$l = $this->layout_individual( $data );
		$html = '<div style="max-width:100%;overflow:auto"><div class="individual-sheet" style="box-sizing:border-box;background:white;width:' . $l['w'] . 'mm;height:' . $l['h'] . 'mm;padding:' . $l['m'] . 'mm;display:grid;justify-content:center;align-content:center;grid-template-columns:repeat(' . $l['cols'] . ',' . $l['cw'] . 'mm);grid-template-rows:repeat(' . $l['rows'] . ',' . $l['ch'] . 'mm)">';
		foreach ( $l['codes'] as $code ) {
			$url = $this->gerar_url_google_charts( $code, 300 ) . '&ecLevel=M';
			$html .= '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:0">';
			$html .= '<img alt="QR ' . esc_attr( $code ) . '" src="' . esc_attr( $url ) . '" style="display:block;margin:0;width:' . $l['qr'] . 'mm;height:' . $l['qr'] . 'mm">';
			$html .= '<div style="margin:0;white-space:nowrap;color:#000;font-family:Courier,monospace;font-weight:700;text-align:center;font-size:' . $l['font'] . 'mm;line-height:1.3">' . esc_html( $code ) . '</div></div>';
		}
		return $html . '</div></div>';
	}

	public function gerar_html_individual( $data ) {
		$l = $this->layout_individual( $data );
		return '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Etiqueta - Bipe individual</title><style>
		@page {size:' . $l['w'] . 'mm ' . $l['h'] . 'mm;margin:0}
		html,body{margin:0;padding:0} .individual-sheet{break-inside:avoid}
		.print-controls{margin:12px} @media print{.print-controls{display:none}}
		</style><body>' . $this->gerar_grade_individual( $data ) . '<div class="print-controls"><button id="print" disabled onclick="window.print()">Carregando QR codes...</button><span id="error"></span></div>
		<script>window.addEventListener("load",function(){var ok=Array.from(document.images).every(function(img){return img.complete && img.naturalWidth>0;});document.getElementById("print").disabled=!ok;document.getElementById("print").textContent="Imprimir";if(!ok)document.getElementById("error").textContent="Falha ao carregar QR codes. Recarregue a página antes de imprimir.";});</script></body></html>';
	}

	public function gerar_pdf_individual( $data ) {
		$l = $this->layout_individual( $data );
		require_once __DIR__ . '/individual-pdf.php';
		$pdf = new QR_Etiqueta_Individual_PDF( $l['w'], $l['h'] );
		$pdf->AddPage();
		foreach ( $l['codes'] as $i => $code ) {
			$file = $this->download_qr_image( $this->gerar_url_google_charts( $code, 300 ) . '&ecLevel=M' );
			if ( ! $file ) {
				throw new RuntimeException( 'Não foi possível carregar todos os QR codes. Tente novamente.' );
			}
			try {
				$x = $l['offset_x'] + ( $i % $l['cols'] ) * $l['cw'];
				$y = $l['offset_y'] + floor( $i / $l['cols'] ) * $l['ch'] + ( $l['ch'] - $l['qr'] - $l['font'] * 1.3 ) / 2;
				$pdf->Image( $file, $x + ( $l['cw'] - $l['qr'] ) / 2, $y, $l['qr'], $l['qr'], 'PNG' );
				$pdf->numero( $x + ( $l['cw'] - strlen( $code ) * $l['font'] * 0.6 ) / 2, $y + $l['qr'] + $l['font'], $code, $l['font'] );
			} finally {
				unlink( $file );
			}
		}
		$pdf->Output( 'D', 'etiqueta-individual-' . date( 'YmdHis' ) . '.pdf' );
	}

	/**
	 * Gerar QR code em SVG
	 *
	 * @param string $data Dados para codificar (número variável)
	 * @param int $size Tamanho do QR code
	 * @return array Dados do QR code
	 */
	public function gerar( $data, $size = 200 ) {
		// Validar entrada (número com 1 ou mais dígitos)
		if ( empty( $data ) || ! preg_match( '/^\d+$/', $data ) ) {
			return [
				'success' => false,
				'message' => __( 'Dados inválidos. Deve conter apenas números', 'qr-etiqueta' ),
			];
		}

		// Gerar QR code usando Google Charts API (alternativa confiável)
		$qr_image_url = $this->gerar_url_google_charts( $data, $size );
		$qr_svg = $this->gerar_svg_local( $data, $size );

		return [
			'success'    => true,
			'data'       => $data,
			'image_url'  => $qr_image_url,
			'svg'        => $qr_svg,
			'url_print'  => add_query_arg( 'qr_print', $data, admin_url( 'admin.php?page=qr-etiqueta' ) ),
			'timestamp'  => current_time( 'Y-m-d H:i:s' ),
		];
	}

	/**
	 * Gerar URL QR via QuickChart API
	 * 
	 * @param string $data Dados
	 * @param int $size Tamanho
	 * @return string URL da imagem
	 */
	public function gerar_url_google_charts( $data, $size ) {
		$encoded = rawurlencode( $data );
		return "https://quickchart.io/qr?text={$encoded}&size={$size}&margin=2";
	}

	/**
	 * Gerar QR code SVG local usando algoritmo simples
	 * Esta é uma método alternativo para casos offline
	 *
	 * @param string $data Dados
	 * @param int $size Tamanho
	 * @return string SVG do QR code
	 */
	private function gerar_svg_local( $data, $size = 200 ) {
		// Usar biblioteca QR Code se disponível, caso contrário usar placeholder
		if ( class_exists( 'QRcode' ) ) {
			return $this->gerar_svg_qrcode_lib( $data, $size );
		}

		// Fallback: criar SVG placeholder
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
		$svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';
		$svg .= '<text x="50%" y="50%" font-size="12" text-anchor="middle" dominant-baseline="middle">';
		$svg .= htmlspecialchars( $data );
		$svg .= '</text>';
		$svg .= '</svg>';

		return $svg;
	}

	/**
	 * Gerar SVG usando biblioteca QRcode (se disponível)
	 */
	private function gerar_svg_qrcode_lib( $data, $size ) {
		// Gerar arquivo temporário
		$temp_file = wp_tempnam();
		QRcode::png( $data, $temp_file, QR_ECLEVEL_L, 10, 0 );

		// Converter PNG para base64 ou retornar path
		if ( file_exists( $temp_file ) ) {
			$image_data = file_get_contents( $temp_file );
			unlink( $temp_file );
			return 'data:image/png;base64,' . base64_encode( $image_data );
		}

		return '<svg></svg>';
	}

	/**
	 * Gerar HTML para impressão em etiqueta Argox 2140 (106x52mm)
	 *
	 * @param string $data Dados do QR code
	 * @param string $image_url URL da imagem do QR
	 * @return string HTML
	 */
	public function gerar_html_impressao( $data, $image_url ) {
		$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [
			'paper_width_mm'  => 100,
			'paper_height_mm' => 50.3,
			'safe_margin_mm'  => 0.5,
			'qr_size_mm'      => 49.3,
			'print_mode'      => 'full',
		];

		$paper_width_mm = isset( $settings['paper_width_mm'] ) ? floatval( $settings['paper_width_mm'] ) : 106.0;
		$paper_height_mm = isset( $settings['paper_height_mm'] ) ? floatval( $settings['paper_height_mm'] ) : 52.0;
		$safe_margin_mm = isset( $settings['safe_margin_mm'] ) ? floatval( $settings['safe_margin_mm'] ) : 1.0;
		$requested_qr_size_mm = isset( $settings['qr_size_mm'] ) ? floatval( $settings['qr_size_mm'] ) : 50.0;
		$print_mode = isset( $settings['print_mode'] ) ? sanitize_key( $settings['print_mode'] ) : 'full';

		if ( ! in_array( $print_mode, [ 'full', 'half_left' ], true ) ) {
			$print_mode = 'full';
		}

		$target_width_mm = ( 'half_left' === $print_mode ) ? ( $paper_width_mm / 2 ) : $paper_width_mm;
		$available_width_mm = max( 10, $target_width_mm - ( $safe_margin_mm * 2 ) );
		$available_height_mm = max( 10, $paper_height_mm - ( $safe_margin_mm * 2 ) );
		$min_text_width_mm = min( max( 18, $available_width_mm * 0.28 ), max( 18, $available_width_mm - 12 ) );
		$gap_mm = max( 0.8, min( 2.0, $available_width_mm * 0.02 ) );
		$max_qr_by_width_mm = max( 10, $available_width_mm - $min_text_width_mm - $gap_mm );
		$max_qr_size_mm = min( $available_height_mm, $max_qr_by_width_mm );
		$qr_size_mm = min( $max_qr_size_mm, max( 10, $requested_qr_size_mm ) );
		$text_width_mm = max( 12, $available_width_mm - $qr_size_mm - $gap_mm );

		$raw_codes = preg_split( '/\r\n|\r|\n/', (string) $data );
		$codes = [];
		foreach ( (array) $raw_codes as $line ) {
			$line = trim( (string) $line );
			if ( '' !== $line ) {
				$codes[] = $line;
			}
		}
		if ( empty( $codes ) ) {
			$codes[] = trim( (string) $data );
		}

		$max_code_length = 1;
		foreach ( $codes as $code_line ) {
			$max_code_length = max( $max_code_length, strlen( (string) $code_line ) );
		}

		$total_lines = max( 1, count( $codes ) );
		$font_by_height_mm = $available_height_mm / ( $total_lines * 1.20 );
		$font_by_width_mm = $text_width_mm / ( max( 1, $max_code_length ) * 0.58 );
		$code_font_size_mm = max( 1.2, min( 4.0, $font_by_height_mm, $font_by_width_mm ) );
		$code_line_height_mm = max( 1.4, $code_font_size_mm * 1.18 );

		$codes_html = '';
		foreach ( $codes as $code_line ) {
			$codes_html .= '<div class="code-line">' . esc_html( $code_line ) . '</div>';
		}

		$paper_width_css = number_format( $paper_width_mm, 2, '.', '' );
		$paper_height_css = number_format( $paper_height_mm, 2, '.', '' );
		$target_width_css = number_format( $target_width_mm, 2, '.', '' );
		$safe_margin_css = number_format( $safe_margin_mm, 2, '.', '' );
		$gap_css = number_format( $gap_mm, 2, '.', '' );
		$qr_size_css = number_format( $qr_size_mm, 2, '.', '' );
		$text_width_css = number_format( $text_width_mm, 2, '.', '' );
		$code_font_size_css = number_format( $code_font_size_mm, 2, '.', '' );
		$code_line_height_css = number_format( $code_line_height_mm, 2, '.', '' );

		$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Etiqueta QR</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		html,
		body {
			width: ' . $paper_width_css . 'mm;
			height: ' . $paper_height_css . 'mm;
			max-height: ' . $paper_height_css . 'mm;
			font-family: Arial, sans-serif;
			background: white;
			overflow: hidden;
			page-break-after: avoid;
			break-after: avoid;
		}

		@page {
			size: ' . $paper_width_css . 'mm ' . $paper_height_css . 'mm;
			margin: 0;
		}

		@media print {
			html,
			body {
				width: ' . $paper_width_css . 'mm !important;
				height: ' . $paper_height_css . 'mm !important;
				max-height: ' . $paper_height_css . 'mm !important;
				margin: 0;
				padding: 0;
				overflow: hidden !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
				page-break-after: avoid !important;
				break-after: avoid !important;
			}
			.label-sheet {
				width: ' . $paper_width_css . 'mm;
				height: ' . $paper_height_css . 'mm;
				max-height: ' . $paper_height_css . 'mm;
				page-break-after: avoid !important;
				page-break-before: avoid !important;
				page-break-inside: avoid !important;
				break-after: avoid !important;
				break-before: avoid !important;
				break-inside: avoid !important;
			}
		}

		/* Folha inteira da etiqueta */
		.label-sheet {
			width: ' . $paper_width_css . 'mm;
			height: ' . $paper_height_css . 'mm;
			position: relative;
			background: white;
		}

		/* Área útil para posicionar o QR */
		.label-target {
			position: absolute;
			left: 0;
			top: 0;
			width: ' . $target_width_css . 'mm;
			height: ' . $paper_height_css . 'mm;
			display: flex;
			flex-direction: row;
			align-items: flex-start;
			justify-content: flex-start;
			gap: ' . $gap_css . 'mm;
			padding: ' . $safe_margin_css . 'mm;
			overflow: hidden;
		}

		.qr-code {
			width: ' . $qr_size_css . 'mm;
			height: ' . $qr_size_css . 'mm;
			flex: 0 0 ' . $qr_size_css . 'mm;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}

		.qr-code img {
			width: 100%;
			height: 100%;
			object-fit: contain;
			image-rendering: pixelated;
		}

		.codes-panel {
			width: ' . $text_width_css . 'mm;
			max-width: ' . $text_width_css . 'mm;
			height: 100%;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			align-items: flex-end;
			justify-content: flex-start;
			margin-left: auto;
			text-align: right;
			font-family: "Courier New", monospace;
			font-size: ' . $code_font_size_css . 'mm;
			line-height: ' . $code_line_height_css . 'mm;
			font-weight: 700;
			letter-spacing: 0.05mm;
		}

		.code-line {
			width: 100%;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: clip;
			text-align: right;
		}

		@media print {
			.print-button {
				display: none;
			}
		}

		.print-button {
			position: fixed;
			bottom: 10px;
			right: 10px;
			padding: 10px 20px;
			background: #007cba;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			z-index: 1000;
		}

		.print-button:hover {
			background: #005a87;
		}
	</style>
</head>
<body>
	<div class="label-sheet">
		<div class="label-target">
			<div class="qr-code">
				<img src="' . esc_attr( $image_url ) . '" alt="QR Code">
			</div>
			<div class="codes-panel">' . $codes_html . '</div>
		</div>
	</div>

	<button class="print-button" onclick="window.print()">' . __( 'Imprimir', 'qr-etiqueta' ) . '</button>

	<script>
		window.addEventListener("load", function() {
			// Auto-print quando a página carrega (comentar se não quiser)
			// window.print();
		});
	</script>
</body>
</html>';

		return $html;
	}

	/**
	 * Gerar HTML para impressão em lote (múltiplas etiquetas)
	 *
	 * @param array $codigos Array com múltiplos códigos
	 * @return string HTML para impressão
	 */
	public function gerar_html_impressao_lote( $codigos ) {
		$html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Etiquetas QR - Lote</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: Arial, sans-serif;
			background: white;
		}

		@page {
			size: 106mm 52mm;
			margin: 2mm;
		}

		@media print {
			body {
				margin: 0;
				padding: 0;
			}
			.label {
				width: 106mm;
				height: 52mm;
				page-break-after: always;
			}
		}

		.label {
			width: 106mm;
			height: 52mm;
			border: 1px solid #ddd;
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 2mm;
			background: white;
			page-break-after: always;
			margin-bottom: 2mm;
		}

		.qr-code {
			width: 48mm;
			height: 48mm;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}

		.qr-code img {
			width: 100%;
			height: 100%;
			object-fit: contain;
		}

		.info {
			flex: 1;
			padding: 0 2mm;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			height: 100%;
		}

		.numero {
			font-size: 10pt;
			font-weight: bold;
			word-break: break-all;
			margin-bottom: 1mm;
		}

		.numero-grande {
			font-size: 16pt;
			font-weight: bold;
			letter-spacing: 1px;
			margin-bottom: 2mm;
			font-family: monospace;
		}

		.timestamp {
			font-size: 7pt;
			color: #666;
			text-align: right;
		}

		@media print {
			.print-button {
				display: none;
			}
		}

		.print-button {
			position: fixed;
			bottom: 10px;
			right: 10px;
			padding: 10px 20px;
			background: #007cba;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			z-index: 1000;
		}

		.print-button:hover {
			background: #005a87;
		}
	</style>
</head>
<body>';

		foreach ( $codigos as $codigo ) {
			$qr_image_url = $this->gerar_url_google_charts( $codigo, 200 );
			$html .= '
	<div class="label">
		<div class="qr-code">
			<img src="' . esc_attr( $qr_image_url ) . '" alt="QR Code ' . esc_attr( $codigo ) . '">
		</div>
		<div class="info">
			<div>
				<div class="numero-grande">' . esc_html( $this->formatar_numero( $codigo ) ) . '</div>
				<div class="numero">' . esc_html( $codigo ) . '</div>
			</div>
			<div class="timestamp">' . wp_strip_all_tags( current_time( 'd/m/Y H:i' ) ) . '</div>
		</div>
	</div>';
		}

		$html .= '

	<button class="print-button" onclick="window.print()">' . __( 'Imprimir', 'qr-etiqueta' ) . '</button>

	<script>
		window.addEventListener("load", function() {
			// Auto-print quando a página carrega (comentar se não quiser)
			// window.print();
		});
	</script>
</body>
</html>';

		return $html;
	}

	/**
	 * Gerar PDF para download com etiqueta QR
	 * 
	 * @param string $qr_data Dados do QR code
	 * @param string $image_url URL da imagem do QR
	 * @return void (Gera output diretamente)
	 */
	public function gerar_pdf_download( $qr_data, $image_url ) {
		try {
			// Baixar imagem do QR
			$temp_file = $this->download_qr_image( $image_url );
			
			if ( ! $temp_file ) {
				wp_die( __( 'Erro ao baixar imagem do QR', 'qr-etiqueta' ) );
			}

			$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [
				'paper_width_mm'  => 100,
				'paper_height_mm' => 50.3,
				'safe_margin_mm'  => 0.5,
				'qr_size_mm'      => 49.3,
				'print_mode'      => 'full',
			];

			$paper_width_mm = isset( $settings['paper_width_mm'] ) ? floatval( $settings['paper_width_mm'] ) : 106.0;
			$paper_height_mm = isset( $settings['paper_height_mm'] ) ? floatval( $settings['paper_height_mm'] ) : 52.0;
			$safe_margin_mm = isset( $settings['safe_margin_mm'] ) ? floatval( $settings['safe_margin_mm'] ) : 1.0;
			$requested_qr_size_mm = isset( $settings['qr_size_mm'] ) ? floatval( $settings['qr_size_mm'] ) : 50.0;
			$print_mode = isset( $settings['print_mode'] ) ? sanitize_key( $settings['print_mode'] ) : 'full';

			if ( ! in_array( $print_mode, [ 'full', 'half_left' ], true ) ) {
				$print_mode = 'full';
			}

			$target_width_mm = ( 'half_left' === $print_mode ) ? ( $paper_width_mm / 2 ) : $paper_width_mm;
			$available_width_mm = max( 10, $target_width_mm - ( $safe_margin_mm * 2 ) );
			$available_height_mm = max( 10, $paper_height_mm - ( $safe_margin_mm * 2 ) );
			$min_text_width_mm = min( max( 18, $available_width_mm * 0.28 ), max( 18, $available_width_mm - 12 ) );
			$gap_mm = max( 0.8, min( 2.0, $available_width_mm * 0.02 ) );
			$max_qr_by_width_mm = max( 10, $available_width_mm - $min_text_width_mm - $gap_mm );
			$max_qr_size_mm = min( $available_height_mm, $max_qr_by_width_mm );
			$qr_size_mm = min( $max_qr_size_mm, max( 10, $requested_qr_size_mm ) );

			$orientation = ( $paper_width_mm >= $paper_height_mm ) ? 'L' : 'P';

			// Criar PDF no tamanho configurado
			$pdf = new FPDF( $orientation, 'mm', array( $paper_width_mm, $paper_height_mm ) );
			$pdf->AddPage();
			$pdf->SetMargins( 0, 0, 0 );
			$pdf->SetAutoPageBreak( false, 0 );

			$qr_x = $safe_margin_mm;
			$qr_y = $safe_margin_mm;

			$pdf->Image( $temp_file, $qr_x, $qr_y, $qr_size_mm, $qr_size_mm, 'PNG' );
			
			// Limpar arquivo temporário
			@unlink( $temp_file );
			
			// Nome do arquivo
			$filename = 'etiqueta-qr-' . date( 'YmdHis' ) . '.pdf';
			
			// Enviar PDF para download
			$pdf->Output( 'D', $filename );
			exit;
			
		} catch ( Throwable $e ) {
			wp_die( __( 'Erro ao gerar PDF: ', 'qr-etiqueta' ) . $e->getMessage() );
		}
	}

	/**
	 * Baixar imagem do QR code para arquivo temporário
	 * 
	 * @param string $url URL da imagem
	 * @return string|false Caminho do arquivo temporário ou false em erro
	 */
	private function download_qr_image( $url ) {
		// Usar wp_remote_get para baixar a imagem
		$response = wp_remote_get( $url, array(
			'timeout' => 15,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$image_data = wp_remote_retrieve_body( $response );
		
		if ( empty( $image_data ) ) {
			return false;
		}

		// Criar arquivo temporário
		$temp_file = wp_tempnam( 'qr-' );
		
		if ( ! $temp_file ) {
			return false;
		}

		// Salvar imagem no arquivo temporário
		if ( file_put_contents( $temp_file, $image_data ) === false ) {
			@unlink( $temp_file );
			return false;
		}

		return $temp_file;
	}
	
	private function formatar_numero( $numero ) {
		// Para números pequenos, mostra inteiro
		// Para maiores, formata em grupos
		if ( strlen( $numero ) <= 7 ) {
			return $numero;
		}
		// Formata em grupos de 3-4 dígitos
		return implode( '-', str_split( $numero, 3 ) );
	}
}
