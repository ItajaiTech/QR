<?php
/**
 * Template para shortcode no frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="qr-etiqueta-frontend">
	<div class="qr-form-wrapper">
		<h3><?php echo esc_html__( 'Gerar QR Codes Etiqueta', 'qr-etiqueta' ); ?></h3>

		<form id="qr-form-frontend" class="qr-form-frontend">
			<div class="form-group">
				<label for="codigos-frontend">
					<?php echo esc_html__( 'Códigos:', 'qr-etiqueta' ); ?>
				</label>
				<textarea 
					id="codigos-frontend" 
					name="codigos" 
					class="qr-textarea"
					rows="8"
					placeholder="4090846&#10;4090845&#10;4090794"
					required
				></textarea>
				<small><?php echo esc_html__( 'Um código por linha. Máximo 50 códigos. Números de qualquer tamanho.', 'qr-etiqueta' ); ?></small>
			</div>

<p><label><input type="checkbox" id="bipe-individual-frontend" name="bipe_individual" value="1"> Bipe individual</label><br>
<small>Marque para gerar até 10 QR codes na mesma etiqueta, cada um com seu número abaixo. Bipe um código por linha e clique em Gerar QR Codes.</small></p>
			<button type="submit" class="btn btn-primary">
				<?php echo esc_html__( 'Gerar QR Codes', 'qr-etiqueta' ); ?>
			</button>
		</form>

		<div id="qr-result-frontend" class="qr-result-frontend" style="display: none;">
			<div id="qr-preview-frontend" class="qr-preview-frontend"></div>
			<div style="display: flex; gap: 10px;">
				<button id="print-btn-frontend" class="btn btn-secondary" style="flex: 1;">
					<?php echo esc_html__( 'Imprimir', 'qr-etiqueta' ); ?>
				</button>
				<button id="download-pdf-btn-frontend" class="btn btn-primary" style="flex: 1;">
					<?php echo esc_html__( 'Baixar PDF', 'qr-etiqueta' ); ?>
				</button>
				<button id="new-btn-frontend" class="btn btn-secondary" style="flex: 1;">
					<?php echo esc_html__( 'Novo', 'qr-etiqueta' ); ?>
				</button>
			</div>
		</div>

		<div id="loading-frontend" class="loading" style="display: none;">
			<?php echo esc_html__( 'Gerando QR codes...', 'qr-etiqueta' ); ?>
		</div>

		<div id="error-frontend" class="error" style="display: none;"></div>
	</div>
</div>

<style>
	.qr-etiqueta-frontend {
		background: white;
		padding: 20px;
		border-radius: 8px;
		max-width: 500px;
		margin: 20px auto;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.qr-form-wrapper h3 {
		margin-top: 0;
		margin-bottom: 20px;
		text-align: center;
		color: #333;
	}

	.form-group {
		margin-bottom: 15px;
	}

	.form-group label {
		display: block;
		margin-bottom: 5px;
		font-weight: bold;
		font-size: 14px;
	}

	.qr-textarea {
		width: 100%;
		padding: 10px;
		border: 1px solid #ddd;
		border-radius: 4px;
		font-size: 14px;
		font-family: monospace;
		box-sizing: border-box;
		resize: vertical;
	}

	.qr-textarea:focus {
		outline: none;
		border-color: #007cba;
		box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
	}

	.form-group small {
		display: block;
		margin-top: 5px;
		color: #666;
		font-size: 12px;
	}

	.btn {
		padding: 10px 20px;
		border: none;
		border-radius: 4px;
		font-size: 14px;
		font-weight: bold;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.btn-primary {
		background: #007cba;
		color: white;
		width: 100%;
		margin-bottom: 15px;
	}

	.btn-primary:hover {
		background: #005a87;
	}

	.btn-secondary {
		background: #6c757d;
		color: white;
	}

	.btn-secondary:hover {
		background: #5a6268;
	}

	.qr-preview-frontend {
		text-align: center;
		margin: 15px 0;
		padding: 15px;
		background: #f5f5f5;
		border-radius: 4px;
		min-height: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.qr-result-frontend {
		margin-top: 20px;
	}

	.loading {
		text-align: center;
		color: #666;
		padding: 20px;
	}

	.error {
		background: #f8d7da;
		color: #721c24;
		padding: 12px;
		border-radius: 4px;
		border-left: 4px solid #f5c6cb;
		margin-top: 15px;
	}
</style>
