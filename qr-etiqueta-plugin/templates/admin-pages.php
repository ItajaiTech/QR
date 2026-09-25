<?php
/**
 * Páginas administrativas do plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderizar página principal do admin
 */
function qr_etiqueta_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para acessar esta página', 'qr-etiqueta' ) );
	}

	// Verificar se é para imprimir um lote de QR codes
	if ( isset( $_GET['qr_print_lote'] ) ) {
		$codigos = isset( $_POST['codigos'] ) ? array_map( 'sanitize_text_field', $_POST['codigos'] ) : [];
		
		if ( empty( $codigos ) ) {
			wp_die( esc_html__( 'Nenhum código para imprimir', 'qr-etiqueta' ) );
		}

		$generator = new QR_Etiqueta_Generator();
		echo $generator->gerar_html_impressao_lote( $codigos );
		exit;
	}

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Gerar QR Code Etiqueta', 'qr-etiqueta' ); ?></h1>

		<div class="qr-container">
			<div class="qr-form-section">
				<h2><?php echo esc_html__( 'Formulário de Geração', 'qr-etiqueta' ); ?></h2>

				<form id="qr-form" class="qr-form">
					<div class="form-group">
						<label for="codigos">
							<?php echo esc_html__( 'Códigos (até 50):', 'qr-etiqueta' ); ?>
						</label>
						<textarea 
							id="codigos" 
							name="codigos" 
							class="regular-text" 
							rows="8"
							placeholder="4090846
4090845
4090794
4090829
4090819"
							required
						></textarea>
						<p class="description">
							<?php echo esc_html__( 'Um código por linha. Máximo 50 códigos. Números de qualquer tamanho.', 'qr-etiqueta' ); ?>
						</p>
					</div>

<p><label><input type="checkbox" id="bipe-individual" name="bipe_individual" value="1"> Bipe individual</label><br>
<small>Marque para gerar até 10 QR codes na mesma etiqueta, cada um com seu número abaixo. Bipe um código por linha e clique em Gerar QR Codes.</small></p>
					<div class="form-actions">
						<button type="submit" class="button button-primary" id="gerar-btn">
							<?php echo esc_html__( 'Gerar QR Codes', 'qr-etiqueta' ); ?>
						</button>
						<span id="loading-spinner" class="spinner" style="display: none;"></span>
					</div>
				</form>

				<div id="result-message" class="notice" style="display: none;"></div>
			</div>

			<div class="qr-preview-section">
				<h2><?php echo esc_html__( 'Visualização', 'qr-etiqueta' ); ?></h2>
				<div id="qr-preview" class="qr-preview">
					<p class="placeholder"><?php echo esc_html__( 'Cole os códigos e clique em "Gerar QR Codes"', 'qr-etiqueta' ); ?></p>
				</div>

				<div id="qr-actions" class="qr-actions" style="display: none;">
					<button id="imprimir-btn" class="button button-primary">
						<?php echo esc_html__( 'Imprimir Etiquetas', 'qr-etiqueta' ); ?>
					</button>
					<button id="download-pdf-btn" class="button button-secondary">
						<?php echo esc_html__( 'Baixar PDF', 'qr-etiqueta' ); ?>
					</button>
					<button id="novo-btn" class="button">
						<?php echo esc_html__( 'Gerar Novo', 'qr-etiqueta' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<style>
		.qr-container {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 30px;
			margin-top: 20px;
		}

		@media (max-width: 1024px) {
			.qr-container {
				grid-template-columns: 1fr;
			}
		}

		.qr-form-section,
		.qr-preview-section {
			background: white;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}

		.form-group {
			margin-bottom: 20px;
		}

		.form-group label {
			display: block;
			margin-bottom: 5px;
			font-weight: bold;
		}

		.form-group input {
			width: 100%;
			padding: 10px;
			border: 1px solid #ddd;
			border-radius: 4px;
		}

		.form-actions {
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.spinner {
			display: inline-block;
			width: 20px !important;
			height: 20px !important;
			background: url('<?php echo esc_url( admin_url( 'images/spinner.gif' ) ); ?>') no-repeat;
			background-size: contain;
		}

		.qr-preview {
			min-height: 300px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #f5f5f5;
			border-radius: 4px;
			border: 2px dashed #ddd;
		}

		.qr-preview .placeholder {
			color: #999;
			text-align: center;
		}

		.qr-preview img {
			max-width: 100%;
			max-height: 100%;
		}

		.qr-actions {
			margin-top: 15px;
			display: flex;
			gap: 10px;
		}

		.notice {
			padding: 12px;
			margin: 20px 0 0 0;
			border-left: 4px solid #dc3545;
			background: #f8d7da;
			border-radius: 4px;
		}

		.notice.success {
			border-left-color: #28a745;
			background: #d4edda;
		}
	</style>
	<?php
}

/**
 * Renderizar página de histórico
 */
function qr_etiqueta_render_historico_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para acessar esta página', 'qr-etiqueta' ) );
	}

	// Processar ações
	if ( isset( $_POST['action'] ) && isset( $_POST['_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'qr_etiqueta_nonce' ) ) {
			wp_die( esc_html__( 'Erro de segurança', 'qr-etiqueta' ) );
		}

		if ( $_POST['action'] === 'limpar_historico' && current_user_can( 'manage_options' ) ) {
			qr_etiqueta_limpar_historico();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Histórico limpo com sucesso!', 'qr-etiqueta' ) . '</p></div>';
		}
	}

	$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$per_page = 20;
	$offset = ( $page - 1 ) * $per_page;
	$total = qr_etiqueta_contar_historico();
	$registros = qr_etiqueta_obter_historico( $per_page, $offset );
	$total_pages = ceil( $total / $per_page );

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Histórico de QR Codes', 'qr-etiqueta' ); ?></h1>

		<div class="tabnav-wrapper">
			<div class="tablenav top">
				<div class="alignleft actions">
					<form method="post" style="display: inline;">
						<?php wp_nonce_field( 'qr_etiqueta_nonce' ); ?>
						<input type="hidden" name="action" value="limpar_historico">
						<button type="submit" class="button" onclick="return confirm('<?php esc_attr_e( 'Tem certeza que deseja limpar todo o histórico?', 'qr-etiqueta' ); ?>')">
							<?php echo esc_html__( 'Limpar Histórico', 'qr-etiqueta' ); ?>
						</button>
					</form>
				</div>
				<div class="tablenav-pages">
					<span class="displaying-num">
						<?php printf(
							esc_html__( '%d items', 'qr-etiqueta' ),
							intval( $total )
						); ?>
					</span>
				</div>
			</div>
		</div>

		<?php if ( empty( $registros ) ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html__( 'Nenhum QR code foi gerado ainda', 'qr-etiqueta' ); ?></p></div>
		<?php else : ?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Código', 'qr-etiqueta' ); ?></th>
						<th><?php echo esc_html__( 'Data de Criação', 'qr-etiqueta' ); ?></th>
						<th><?php echo esc_html__( 'Usuário', 'qr-etiqueta' ); ?></th>
						<th><?php echo esc_html__( 'Ações', 'qr-etiqueta' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $registros as $registro ) : ?>
						<?php $user = get_user_by( 'id', $registro->usuario_id ); ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $registro->codigo ); ?></strong>
								<?php echo '(' . esc_html( qr_etiqueta_formatar_codigo( $registro->codigo ) ) . ')'; ?>
							</td>
							<td><?php echo esc_html( wp_strip_all_tags( date_i18n( 'd/m/Y H:i', strtotime( $registro->data_criacao ) ) ) ); ?></td>
							<td><?php echo $user ? esc_html( $user->user_login ) : esc_html__( 'Desconhecido', 'qr-etiqueta' ); ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'qr-etiqueta', 'qr_print' => $registro->codigo ] ) ); ?>" class="button button-small">
									<?php echo esc_html__( 'Imprimir', 'qr-etiqueta' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<?php
						echo wp_kses_post( paginate_links( [
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;' ),
							'next_text' => __( '&raquo;' ),
							'total'     => $total_pages,
							'current'   => $page,
						] ) );
						?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Renderizar página de configurações
 */
function qr_etiqueta_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para acessar esta página', 'qr-etiqueta' ) );
	}

	$settings_notice = '';
	$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [
		'paper_width_mm'  => 100,
		'paper_height_mm' => 50.3,
		'safe_margin_mm'  => 0.5,
		'qr_size_mm'      => 49.3,
		'print_mode'      => 'full',
	];

	if ( isset( $_POST['qr_etiqueta_save_settings'] ) ) {
		check_admin_referer( 'qr_etiqueta_save_settings', 'qr_etiqueta_save_settings_nonce' );

		$input = [
			'paper_width_mm'  => isset( $_POST['paper_width_mm'] ) ? wp_unslash( $_POST['paper_width_mm'] ) : '',
			'paper_height_mm' => isset( $_POST['paper_height_mm'] ) ? wp_unslash( $_POST['paper_height_mm'] ) : '',
			'safe_margin_mm'  => isset( $_POST['safe_margin_mm'] ) ? wp_unslash( $_POST['safe_margin_mm'] ) : '',
			'qr_size_mm'      => isset( $_POST['qr_size_mm'] ) ? wp_unslash( $_POST['qr_size_mm'] ) : '',
			'print_mode'      => isset( $_POST['print_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['print_mode'] ) ) : '',
		];

		if ( function_exists( 'qrEtiquetaUpdateSettings' ) ) {
			$settings = qrEtiquetaUpdateSettings( $input );
			$settings_notice = esc_html__( 'Configurações de impressão salvas com sucesso.', 'qr-etiqueta' );
		}
	}

	$paper_width_mm = floatval( $settings['paper_width_mm'] );
	$paper_height_mm = floatval( $settings['paper_height_mm'] );
	$safe_margin_mm = floatval( $settings['safe_margin_mm'] );
	$target_width_mm = ( 'half_left' === $settings['print_mode'] ) ? ( $paper_width_mm / 2 ) : $paper_width_mm;
	$max_qr_mm = max( 10, min( $target_width_mm, $paper_height_mm ) - ( $safe_margin_mm * 2 ) );

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Configurações - QR Etiqueta', 'qr-etiqueta' ); ?></h1>

		<?php if ( ! empty( $settings_notice ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html( $settings_notice ); ?></p>
			</div>
		<?php endif; ?>

		<div class="settings-box">
			<h2><?php echo esc_html__( 'Configuração de Impressão', 'qr-etiqueta' ); ?></h2>

			<form method="post" class="qr-settings-form">
				<?php wp_nonce_field( 'qr_etiqueta_save_settings', 'qr_etiqueta_save_settings_nonce' ); ?>
				<input type="hidden" name="qr_etiqueta_save_settings" value="1">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="paper_width_mm"><?php echo esc_html__( 'Largura do papel (mm)', 'qr-etiqueta' ); ?></label></th>
						<td>
							<input id="paper_width_mm" name="paper_width_mm" type="number" step="0.1" min="30" max="150" value="<?php echo esc_attr( $settings['paper_width_mm'] ); ?>" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="paper_height_mm"><?php echo esc_html__( 'Altura do papel (mm)', 'qr-etiqueta' ); ?></label></th>
						<td>
							<input id="paper_height_mm" name="paper_height_mm" type="number" step="0.1" min="20" max="150" value="<?php echo esc_attr( $settings['paper_height_mm'] ); ?>" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="print_mode"><?php echo esc_html__( 'Modo de posicionamento', 'qr-etiqueta' ); ?></label></th>
						<td>
							<select id="print_mode" name="print_mode">
								<option value="full" <?php selected( $settings['print_mode'], 'full' ); ?>><?php echo esc_html__( 'Preencher etiqueta completa', 'qr-etiqueta' ); ?></option>
								<option value="half_left" <?php selected( $settings['print_mode'], 'half_left' ); ?>><?php echo esc_html__( 'Usar metade esquerda da etiqueta', 'qr-etiqueta' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="qr_size_mm"><?php echo esc_html__( 'Tamanho do QR (mm)', 'qr-etiqueta' ); ?></label></th>
						<td>
							<input id="qr_size_mm" name="qr_size_mm" type="number" step="0.1" min="10" max="120" value="<?php echo esc_attr( $settings['qr_size_mm'] ); ?>" class="small-text">
							<p class="description"><?php echo esc_html( sprintf( 'Máximo útil atual: %.1f mm', $max_qr_mm ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="safe_margin_mm"><?php echo esc_html__( 'Margem de segurança (mm)', 'qr-etiqueta' ); ?></label></th>
						<td>
							<input id="safe_margin_mm" name="safe_margin_mm" type="number" step="0.1" min="0" max="10" value="<?php echo esc_attr( $settings['safe_margin_mm'] ); ?>" class="small-text">
							<p class="description"><?php echo esc_html__( 'Use 0.5 a 1.5mm para evitar corte de borda na impressão térmica.', 'qr-etiqueta' ); ?></p>
						</td>
					</tr>
				</table>

				<p>
					<button type="submit" class="button button-primary"><?php echo esc_html__( 'Salvar Configurações de Impressão', 'qr-etiqueta' ); ?></button>
				</p>
			</form>

			<h2><?php echo esc_html__( 'Informações do Plugin', 'qr-etiqueta' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php echo esc_html__( 'Nome', 'qr-etiqueta' ); ?></th>
					<td><?php echo esc_html__( 'QR Etiqueta Argox', 'qr-etiqueta' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Versão', 'qr-etiqueta' ); ?></th>
					<td><?php echo esc_html( QR_ETIQUETA_VERSION ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Dimensões da Etiqueta', 'qr-etiqueta' ); ?></th>
					<td><?php echo esc_html( sprintf( '%.1fmm x %.1fmm', $paper_width_mm, $paper_height_mm ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Compatibilidade', 'qr-etiqueta' ); ?></th>
					<td><?php echo esc_html__( 'Impressora Argox 2140', 'qr-etiqueta' ); ?></td>
				</tr>
			</table>

			<h2><?php echo esc_html__( 'Como Usar', 'qr-etiqueta' ); ?></h2>

			<ol class="instructions">
				<li><?php echo esc_html__( 'Acesse a aba "Gerar QR"', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( 'Digite um código com exatamente 10 dígitos', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( 'Clique em "Gerar QR Code"', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( 'Clique em "Imprimir Etiqueta" para abrir o diálogo de impressão', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( 'Configurar impressora: Argox 2140, tamanho 106x52mm', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( 'Imprimir', 'qr-etiqueta' ); ?></li>
			</ol>

			<h2><?php echo esc_html__( 'Shortcode', 'qr-etiqueta' ); ?></h2>

			<p><?php echo esc_html__( 'Use o shortcode abaixo para adicionar o gerador de QR em uma página:', 'qr-etiqueta' ); ?></p>

			<code style="background: #f5f5f5; padding: 10px; display: block; border-radius: 4px; margin: 10px 0;">
				[qr_etiqueta]
			</code>

			<h2><?php echo esc_html__( 'Dicas de Impressão', 'qr-etiqueta' ); ?></h2>

			<ul>
				<li><?php echo esc_html__( '✓ Use etiquetas pré-cortadas de 106x52mm', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( '✓ Configure a impressora para qualidade máxima', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( '✓ Preset recomendado: 100x50,3mm, margem 0,5mm e QR 49,3mm', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( '✓ Teste com algumas etiquetas antes de imprimir em lote', 'qr-etiqueta' ); ?></li>
				<li><?php echo esc_html__( '✓ Guarde os códigos no histórico para referência futura', 'qr-etiqueta' ); ?></li>
			</ul>
		</div>

		<style>
			.settings-box {
				background: white;
				padding: 20px;
				border-radius: 8px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
				max-width: 800px;
			}

			.form-table {
				width: 100%;
				border-collapse: collapse;
			}

			.form-table tr {
				border-bottom: 1px solid #ddd;
			}

			.form-table th {
				text-align: left;
				padding: 10px;
				font-weight: bold;
				background: #f5f5f5;
			}

			.form-table td {
				padding: 10px;
			}

			.instructions {
				background: #f5f5f5;
				padding: 20px 20px 20px 40px;
				border-radius: 4px;
			}

			.instructions li {
				margin-bottom: 8px;
			}

			.qr-settings-form {
				margin-bottom: 24px;
				padding-bottom: 16px;
				border-bottom: 1px solid #e5e5e5;
			}
		</style>
	</div>
	<?php
}
