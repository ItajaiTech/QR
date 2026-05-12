<?php
/**
 * Script de validação do plugin QR Etiqueta
 * Coloque este arquivo na pasta raiz do plugin para testar
 */

// Detectar erros
$errors = [];
$warnings = [];
$success = [];

// 1. Validar estrutura de pastas
$required_dirs = [
	'assets',
	'includes',
	'templates',
];

foreach ( $required_dirs as $dir ) {
	if ( ! is_dir( dirname( __FILE__ ) . '/' . $dir ) ) {
		$errors[] = "❌ Pasta ausente: /$dir";
	} else {
		$success[] = "✅ Pasta existe: /$dir";
	}
}

// 2. Validar arquivos principais
$required_files = [
	'qr-etiqueta.php',
	'README.md',
	'includes/qr-generator.php',
	'includes/functions.php',
	'assets/admin-qr.css',
	'assets/admin-qr.js',
	'assets/frontend-qr.css',
	'assets/frontend-qr.js',
	'templates/admin-pages.php',
	'templates/shortcode-qr.php',
];

foreach ( $required_files as $file ) {
	if ( ! file_exists( dirname( __FILE__ ) . '/' . $file ) ) {
		$errors[] = "❌ Arquivo faltando: /$file";
	} else {
		$success[] = "✅ Arquivo OK: /$file";
	}
}

// 3. Validar sintaxe PHP
$php_files = glob( dirname( __FILE__ ) . '/**/*.php', GLOB_RECURSIVE );
foreach ( $php_files as $php_file ) {
	$output = [];
	exec( "php -l " . escapeshellarg( $php_file ), $output, $return_code );
	if ( $return_code !== 0 ) {
		$errors[] = "❌ Erro de sintaxe em: " . str_replace( dirname( __FILE__ ), '', $php_file );
	}
}

// 4. Avisos
if ( ! function_exists( 'wp_create_nonce' ) ) {
	$warnings[] = "⚠️ Script deve rodar dentro do WordPress";
}

// Exibir resultados
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Validação - QR Etiqueta Plugin</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			max-width: 800px;
			margin: 40px auto;
			padding: 20px;
			background: #f1f1f1;
		}

		.container {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		h1 {
			color: #333;
			margin-bottom: 30px;
			text-align: center;
		}

		.section {
			margin-bottom: 30px;
		}

		.section h2 {
			font-size: 16px;
			color: #555;
			margin-bottom: 15px;
			border-bottom: 2px solid #007cba;
			padding-bottom: 8px;
		}

		.message {
			padding: 10px;
			margin-bottom: 8px;
			border-left: 4px solid;
			border-radius: 2px;
		}

		.success {
			background: #d4edda;
			color: #155724;
			border-left-color: #28a745;
		}

		.error {
			background: #f8d7da;
			color: #721c24;
			border-left-color: #dc3545;
		}

		.warning {
			background: #fff3cd;
			color: #856404;
			border-left-color: #ffc107;
		}

		.summary {
			text-align: center;
			margin-top: 30px;
			padding-top: 20px;
			border-top: 2px solid #ddd;
		}

		.status-icon {
			font-size: 48px;
			margin-bottom: 10px;
		}

		.status-text {
			font-size: 20px;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>🧪 Validação - QR Etiqueta Plugin</h1>

		<?php if ( ! empty( $success ) ) : ?>
			<div class="section">
				<h2>✅ Verificações OK</h2>
				<?php foreach ( $success as $msg ) : ?>
					<div class="message success"><?php echo esc_html( $msg ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="section">
				<h2>❌ Erros Encontrados</h2>
				<?php foreach ( $errors as $msg ) : ?>
					<div class="message error"><?php echo esc_html( $msg ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $warnings ) ) : ?>
			<div class="section">
				<h2>⚠️ Avisos</h2>
				<?php foreach ( $warnings as $msg ) : ?>
					<div class="message warning"><?php echo esc_html( $msg ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="summary">
			<?php if ( empty( $errors ) ) : ?>
				<div class="status-icon">✅</div>
				<div class="status-text">Plugin está pronto para usar!</div>
				<p style="margin-top: 15px; color: #666;">
					Próximo passo: Ativar o plugin no WordPress
				</p>
			<?php else : ?>
				<div class="status-icon">⚠️</div>
				<div class="status-text">Corrija os erros acima</div>
				<p style="margin-top: 15px; color: #666;">
					Total de erros: <strong><?php echo count( $errors ); ?></strong>
				</p>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
