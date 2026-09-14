<?php
/**
 * Plugin Name: QR Etiqueta Argox
 * Plugin URI: https://example.com/qr-etiqueta
 * Description: Gera QR codes otimizados para impressão em etiquetas Argox 2140 (106x52mm) com 10 dígitos
 * Version: 1.0.2
 * Author: Admin
 * License: GPL v2 or later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Proteção contra acesso direto
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Definir constantes do plugin
define( 'QR_ETIQUETA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QR_ETIQUETA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'QR_ETIQUETA_VERSION', '1.0.2' );

// Incluir arquivos do plugin
require_once QR_ETIQUETA_PLUGIN_DIR . 'includes/qr-generator.php';
require_once QR_ETIQUETA_PLUGIN_DIR . 'includes/functions.php';

/**
 * Ativação do plugin
 */
function qr_etiqueta_activate() {
	// Criar tabela de histórico se necessário
	global $wpdb;
	$table_name = $wpdb->prefix . 'qr_etiqueta_historico';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		codigo VARCHAR(20) NOT NULL,
		data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
		usuario_id BIGINT(20) UNSIGNED,
		observacoes TEXT,
		PRIMARY KEY (id),
		UNIQUE KEY codigo (codigo),
		KEY usuario_id (usuario_id),
		KEY data_criacao (data_criacao)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'qr_etiqueta_activate' );

/**
 * Desativação do plugin
 */
function qr_etiqueta_deactivate() {
	// Limpar opções
	delete_option( 'qr_etiqueta_settings' );
}
register_deactivation_hook( __FILE__, 'qr_etiqueta_deactivate' );

/**
 * Carregar arquivos de idioma
 */
function qr_etiqueta_load_text_domain() {
	load_plugin_textdomain(
		'qr-etiqueta',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'qr_etiqueta_load_text_domain' );

/**
 * Menu de admin
 */
function qr_etiqueta_add_admin_menu() {
	add_menu_page(
		__( 'QR Etiqueta', 'qr-etiqueta' ),
		__( 'QR Etiqueta', 'qr-etiqueta' ),
		'manage_options',
		'qr-etiqueta',
		'qr_etiqueta_render_admin_page',
		'dashicons-qrcode',
		76
	);

	add_submenu_page(
		'qr-etiqueta',
		__( 'Gerar QR', 'qr-etiqueta' ),
		__( 'Gerar QR', 'qr-etiqueta' ),
		'manage_options',
		'qr-etiqueta',
		'qr_etiqueta_render_admin_page'
	);

	add_submenu_page(
		'qr-etiqueta',
		__( 'Histórico', 'qr-etiqueta' ),
		__( 'Histórico', 'qr-etiqueta' ),
		'manage_options',
		'qr-etiqueta-historico',
		'qr_etiqueta_render_historico_page'
	);

	add_submenu_page(
		'qr-etiqueta',
		__( 'Configurações', 'qr-etiqueta' ),
		__( 'Configurações', 'qr-etiqueta' ),
		'manage_options',
		'qr-etiqueta-settings',
		'qr_etiqueta_render_settings_page'
	);
}
add_action( 'admin_menu', 'qr_etiqueta_add_admin_menu' );

/**
 * Enfileirar scripts e estilos do admin
 */
function qr_etiqueta_enqueue_admin_assets( $hook ) {
	if ( ! in_array( $hook, [ 'toplevel_page_qr-etiqueta', 'qr-etiqueta_page_qr-etiqueta-historico', 'qr-etiqueta_page_qr-etiqueta-settings' ] ) ) {
		return;
	}

	wp_enqueue_style(
		'qr-etiqueta-admin',
		QR_ETIQUETA_PLUGIN_URL . 'assets/admin-qr.css',
		[],
		QR_ETIQUETA_VERSION
	);

	wp_enqueue_script(
		'qr-etiqueta-admin',
		QR_ETIQUETA_PLUGIN_URL . 'assets/admin-qr.js',
		[ 'jquery' ],
		QR_ETIQUETA_VERSION,
		true
	);

	wp_localize_script( 'qr-etiqueta-admin', 'qrEtiquetaParams', [
		'nonce'       => wp_create_nonce( 'qr_etiqueta_nonce' ),
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'i18n'        => [
			'confirmarEntrada' => __( 'Confirme se o valor é um número de 10 dígitos', 'qr-etiqueta' ),
			'gerandoQR'        => __( 'Gerando QR code...', 'qr-etiqueta' ),
			'sucesso'          => __( 'QR code gerado com sucesso!', 'qr-etiqueta' ),
			'erro'             => __( 'Erro ao gerar QR code', 'qr-etiqueta' ),
			'abrindoImpressao' => __( 'Abrindo diálogo de impressão...', 'qr-etiqueta' ),
		],
	] );
}
add_action( 'admin_enqueue_scripts', 'qr_etiqueta_enqueue_admin_assets' );

/**
 * AJAX para gerar QR code
 */
function qr_etiqueta_ajax_gerar_qr() {
	// Verificar nonce sem morrer se falhar
	if ( isset( $_POST['nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'qr_etiqueta_nonce' ) ) {
			wp_send_json_error( __( 'Falha na verificação de segurança', 'qr-etiqueta' ) );
		}
	}

	$codigos_raw = isset( $_POST['codigos'] ) ? sanitize_textarea_field( $_POST['codigos'] ) : '';

	if ( empty( $codigos_raw ) ) {
		wp_send_json_error( __( 'Insira pelo menos um código', 'qr-etiqueta' ) );
	}

	// Processar múltiplos códigos (um por linha)
	$codigos_array = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $codigos_raw ) ) );
	$max_codigos = 50;

	if ( empty( $codigos_array ) ) {
		wp_send_json_error( __( 'Nenhum código válido encontrado', 'qr-etiqueta' ) );
	}

	if ( count( $codigos_array ) > $max_codigos ) {
		wp_send_json_error( sprintf( __( 'Máximo de %d códigos por vez', 'qr-etiqueta' ), $max_codigos ) );
	}

	// Validar e sanitizar cada código
	$codigos_validos = [];
	foreach ( $codigos_array as $codigo ) {
		if ( preg_match( '/^\d+$/', $codigo ) && strlen( $codigo ) > 0 ) {
			$codigos_validos[] = $codigo;
		}
	}

	if ( empty( $codigos_validos ) ) {
		wp_send_json_error( __( 'Nenhum código numérico válido', 'qr-etiqueta' ) );
	}

	// Não gerar QR code quando houver números repetidos no formulário.
	$contagem_codigos = array_count_values( $codigos_validos );
	$codigos_repetidos = array_keys( array_filter( $contagem_codigos, function( $quantidade ) {
		return $quantidade > 1;
	} ) );

	if ( ! empty( $codigos_repetidos ) ) {
		wp_send_json_error( sprintf(
			__( 'Número(s) repetido(s): %s. Remova a repetição para continuar.', 'qr-etiqueta' ),
			implode( ', ', $codigos_repetidos )
		) );
	}

	// Gerar QR code com sequência (quebra de linha entre os números)
	$generator = new QR_Etiqueta_Generator();
	$qr_data = implode( "\n", $codigos_validos );
	$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [];
	$qr_size_mm = isset( $settings['qr_size_mm'] ) ? floatval( $settings['qr_size_mm'] ) : 49.3;
	$qr_size = function_exists( 'qrEtiquetaMmToPixels203dpi' ) ? qrEtiquetaMmToPixels203dpi( $qr_size_mm ) : 400;
	$image_url = $generator->gerar_url_google_charts( $qr_data, $qr_size );

	wp_send_json_success( [
		'qr_data'       => $qr_data,
		'codigos'       => $codigos_validos,
		'quantidade'    => count( $codigos_validos ),
		'image_url'     => $image_url,
		'timestamp'     => current_time( 'Y-m-d H:i:s' ),
	] );
}
add_action( 'wp_ajax_qr_etiqueta_gerar_qr', 'qr_etiqueta_ajax_gerar_qr' );
add_action( 'wp_ajax_nopriv_qr_etiqueta_gerar_qr', 'qr_etiqueta_ajax_gerar_qr' );

/**
 * GET handler para imprimir QR code (via AJAX GET)
 */
function qr_etiqueta_print_qr() {
	if ( ! isset( $_GET['print'] ) || $_GET['print'] !== '1' ) {
		return;
	}

	if ( ! isset( $_GET['qr_data'] ) ) {
		wp_die( __( 'Dados inválidos', 'qr-etiqueta' ) );
	}

	$qr_data = sanitize_textarea_field( wp_unslash( $_GET['qr_data'] ) );
	if ( '' === trim( $qr_data ) ) {
		wp_die( __( 'Dados inválidos', 'qr-etiqueta' ) );
	}

	$generator = new QR_Etiqueta_Generator();
	$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [];
	$qr_size_mm = isset( $settings['qr_size_mm'] ) ? floatval( $settings['qr_size_mm'] ) : 49.3;
	$qr_size = function_exists( 'qrEtiquetaMmToPixels203dpi' ) ? qrEtiquetaMmToPixels203dpi( $qr_size_mm ) : 400;
	$image_url = $generator->gerar_url_google_charts( $qr_data, $qr_size );
	echo $generator->gerar_html_impressao( $qr_data, $image_url );
	exit;
}
add_action( 'wp_ajax_nopriv_qr_etiqueta_print_qr', 'qr_etiqueta_print_qr' );
add_action( 'wp_ajax_qr_etiqueta_print_qr', 'qr_etiqueta_print_qr' );

/**
 * AJAX para baixar PDF do QR code
 */
function qr_etiqueta_download_pdf() {
	if ( ! isset( $_GET['qr_data'] ) ) {
		wp_die( __( 'Dados inválidos', 'qr-etiqueta' ) );
	}

	$qr_data = sanitize_textarea_field( wp_unslash( $_GET['qr_data'] ) );
	if ( '' === trim( $qr_data ) ) {
		wp_die( __( 'Dados inválidos', 'qr-etiqueta' ) );
	}

	try {
		$generator = new QR_Etiqueta_Generator();
		$settings = function_exists( 'qrEtiquetaGetSettings' ) ? qrEtiquetaGetSettings() : [];
		$qr_size_mm = isset( $settings['qr_size_mm'] ) ? floatval( $settings['qr_size_mm'] ) : 49.3;
		$qr_size = function_exists( 'qrEtiquetaMmToPixels203dpi' ) ? qrEtiquetaMmToPixels203dpi( $qr_size_mm ) : 400;
		$image_url = $generator->gerar_url_google_charts( $qr_data, $qr_size );
		$generator->gerar_pdf_download( $qr_data, $image_url );
		exit;
	} catch ( Throwable $e ) {
		if ( function_exists( 'error_log' ) ) {
			error_log( '[qr-etiqueta] erro no download de PDF: ' . $e->getMessage() );
		}
		wp_die( __( 'Nao foi possivel gerar o PDF no momento. Verifique as configuracoes e tente novamente.', 'qr-etiqueta' ) );
	}
}
add_action( 'wp_ajax_nopriv_qr_etiqueta_download_pdf', 'qr_etiqueta_download_pdf' );
add_action( 'wp_ajax_qr_etiqueta_download_pdf', 'qr_etiqueta_download_pdf' );

/** * Shortcode para exibir gerador de QR no frontend
 */
function qr_etiqueta_shortcode( $atts ) {
	ob_start();
	include QR_ETIQUETA_PLUGIN_DIR . 'templates/shortcode-qr.php';
	return ob_get_clean();
}
add_shortcode( 'qr_etiqueta', 'qr_etiqueta_shortcode' );

/**
 * Enfileirar scripts frontend quando shortcode é usado
 */
function qr_etiqueta_enqueue_frontend_assets() {
	if ( is_singular() && has_shortcode( get_post()->post_content, 'qr_etiqueta' ) ) {
		wp_enqueue_style(
			'qr-etiqueta-frontend',
			QR_ETIQUETA_PLUGIN_URL . 'assets/frontend-qr.css',
			[],
			QR_ETIQUETA_VERSION
		);

		wp_enqueue_script(
			'qr-etiqueta-frontend',
			QR_ETIQUETA_PLUGIN_URL . 'assets/frontend-qr.js',
			[ 'jquery' ],
			QR_ETIQUETA_VERSION,
			true
		);

		wp_localize_script( 'qr-etiqueta-frontend', 'qrEtiquetaParams', [
			'nonce'   => wp_create_nonce( 'qr_etiqueta_nonce' ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		] );
	}
}
add_action( 'wp_enqueue_scripts', 'qr_etiqueta_enqueue_frontend_assets' );

// Incluir página de renderização
include QR_ETIQUETA_PLUGIN_DIR . 'templates/admin-pages.php';
