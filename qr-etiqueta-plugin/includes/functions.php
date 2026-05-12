<?php
/**
 * Funções auxiliares do plugin QR Etiqueta
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validar se é um código numérico válido
 */
function qr_etiqueta_validar_codigo( $codigo ) {
	return preg_match( '/^\d+$/', trim( $codigo ) ) ? true : false;
}

/**
 * Sanitizar código QR
 */
function qr_etiqueta_sanitizar_codigo( $codigo ) {
	return preg_replace( '/[^\d]/', '', $codigo );
}

/**
 * Formatar código para exibição
 */
function qr_etiqueta_formatar_codigo( $codigo ) {
	return implode( '-', str_split( $codigo, 3 ) );
}

/**
 * Obter histórico de QR codes gerados
 */
function qr_etiqueta_obter_historico( $limit = 50, $offset = 0 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'qr_etiqueta_historico';

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, codigo, data_criacao, usuario_id FROM $table ORDER BY data_criacao DESC LIMIT %d OFFSET %d",
			intval( $limit ),
			intval( $offset )
		)
	);

	return $results ? $results : [];
}

/**
 * Contar total de QR codes gerados
 */
function qr_etiqueta_contar_historico() {
	global $wpdb;
	$table = $wpdb->prefix . 'qr_etiqueta_historico';

	return intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );
}

/**
 * Excluir registro do histórico
 */
function qr_etiqueta_excluir_historico( $id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'qr_etiqueta_historico';

	return $wpdb->delete( $table, [ 'id' => intval( $id ) ], [ '%d' ] );
}

/**
 * Limpar histórico completo
 */
function qr_etiqueta_limpar_historico() {
	global $wpdb;
	$table = $wpdb->prefix . 'qr_etiqueta_historico';

	return $wpdb->query( "TRUNCATE TABLE $table" );
}

/**
 * Exportar histórico como CSV
 */
function qr_etiqueta_exportar_csv() {
	global $wpdb;
	$table = $wpdb->prefix . 'qr_etiqueta_historico';

	$results = $wpdb->get_results( "SELECT codigo, data_criacao, usuario_id FROM $table ORDER BY data_criacao DESC" );

	if ( empty( $results ) ) {
		return '';
	}

	$csv = "Código;Data de Criação;ID do Usuário\n";

	foreach ( $results as $row ) {
		$user = get_user_by( 'id', $row->usuario_id );
		$user_name = $user ? $user->user_login : 'Desconhecido';

		$csv .= '"' . $row->codigo . '";';
		$csv .= '"' . date_i18n( 'd/m/Y H:i:s', strtotime( $row->data_criacao ) ) . '";';
		$csv .= '"' . $user_name . "\"\n";
	}

	return $csv;
}

/**
 * Configurações padrão de impressão.
 *
 * @return array
 */
function qrEtiquetaDefaultSettings() {
	return [
		'paper_width_mm'  => 100,
		'paper_height_mm' => 50.3,
		'safe_margin_mm'  => 0.5,
		'qr_size_mm'      => 49.3,
		'print_mode'      => 'full',
	];
}

/**
 * Sanitizar valor numérico em milímetros.
 *
 * @param mixed $value Valor informado.
 * @param float $default Padrão.
 * @param float $min Valor mínimo.
 * @param float $max Valor máximo.
 * @return float
 */
function qrEtiquetaSanitizeMmValue( $value, $default, $min, $max ) {
	$number = is_numeric( $value ) ? floatval( $value ) : floatval( $default );
	$number = max( $min, min( $max, $number ) );
	return round( $number, 2 );
}

/**
 * Sanitizar configurações do plugin.
 *
 * @param array $input Configurações recebidas.
 * @return array
 */
function qrEtiquetaSanitizeSettings( $input ) {
	$defaults = qrEtiquetaDefaultSettings();
	$input = is_array( $input ) ? $input : [];

	$paper_width_mm = qrEtiquetaSanitizeMmValue(
		$input['paper_width_mm'] ?? $defaults['paper_width_mm'],
		$defaults['paper_width_mm'],
		30,
		150
	);

	$paper_height_mm = qrEtiquetaSanitizeMmValue(
		$input['paper_height_mm'] ?? $defaults['paper_height_mm'],
		$defaults['paper_height_mm'],
		20,
		150
	);

	$max_safe_margin = max( 0, min( 10, ( min( $paper_width_mm, $paper_height_mm ) / 2 ) - 5 ) );
	$safe_margin_mm = qrEtiquetaSanitizeMmValue(
		$input['safe_margin_mm'] ?? $defaults['safe_margin_mm'],
		$defaults['safe_margin_mm'],
		0,
		$max_safe_margin
	);

	$max_qr_mm = max( 10, min( $paper_width_mm, $paper_height_mm ) - ( $safe_margin_mm * 2 ) );
	$qr_size_mm = qrEtiquetaSanitizeMmValue(
		$input['qr_size_mm'] ?? $defaults['qr_size_mm'],
		$defaults['qr_size_mm'],
		10,
		$max_qr_mm
	);

	$print_mode = isset( $input['print_mode'] ) ? sanitize_key( $input['print_mode'] ) : $defaults['print_mode'];
	if ( ! in_array( $print_mode, [ 'full', 'half_left' ], true ) ) {
		$print_mode = $defaults['print_mode'];
	}

	return [
		'paper_width_mm'  => $paper_width_mm,
		'paper_height_mm' => $paper_height_mm,
		'safe_margin_mm'  => $safe_margin_mm,
		'qr_size_mm'      => $qr_size_mm,
		'print_mode'      => $print_mode,
	];
}

/**
 * Retornar configurações atuais do plugin.
 *
 * @return array
 */
function qrEtiquetaGetSettings() {
	$defaults = qrEtiquetaDefaultSettings();
	$stored = get_option( 'qr_etiqueta_settings', [] );
	$merged = wp_parse_args( is_array( $stored ) ? $stored : [], $defaults );
	return qrEtiquetaSanitizeSettings( $merged );
}

/**
 * Salvar configurações do plugin.
 *
 * @param array $input Configurações informadas.
 * @return array
 */
function qrEtiquetaUpdateSettings( $input ) {
	$settings = qrEtiquetaSanitizeSettings( $input );
	update_option( 'qr_etiqueta_settings', $settings );
	return $settings;
}

/**
 * Converter mm para pixels considerando 203 dpi (Argox 2140).
 *
 * @param float $millimeters Tamanho em mm.
 * @return int
 */
function qrEtiquetaMmToPixels203dpi( $millimeters ) {
	$px = ( floatval( $millimeters ) / 25.4 ) * 203;
	$px = max( 200, min( 900, $px ) );
	return intval( round( $px ) );
}
