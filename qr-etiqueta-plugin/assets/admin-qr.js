/**
 * JavaScript para painel administrativo
 */

jQuery(document).ready(function($) {
	'use strict';

	const $form = $('#qr-form');
	const $codigos = $('#codigos');
	const $gerarBtn = $('#gerar-btn');
	const $loadingSpinner = $('#loading-spinner');
	const $resultMessage = $('#result-message');
	const $qrPreview = $('#qr-preview');
	const $qrActions = $('#qr-actions');
	const $imprimirBtn = $('#imprimir-btn');
	const $downloadPdfBtn = $('#download-pdf-btn');
	const $novoBtn = $('#novo-btn');

	let currentQRData = null;

	// Enviar formulário
	$form.on('submit', function(e) {
		e.preventDefault();
		gerarQRCode();
	});

	// Botão imprimir
	$imprimirBtn.on('click', function() {
		if (currentQRData) {
			imprimirEtiqueta(currentQRData);
		}
	});

	// Botão baixar PDF
	$downloadPdfBtn.on('click', function() {
		if (currentQRData) {
			baixarPDF(currentQRData);
		}
	});

	/**
	 * Gerar QR code com sequência
	 */
	function gerarQRCode() {
		const codigos = $codigos.val().trim();

		// Validação
		if (!codigos) {
			showError('Insira pelo menos um código');
			return;
		}

		// Mostrar loading
		$loadingSpinner.show();
		$resultMessage.hide();
		$gerarBtn.prop('disabled', true);

		// AJAX request
		$.ajax({
			url: qrEtiquetaParams.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'qr_etiqueta_gerar_qr',
				nonce: qrEtiquetaParams.nonce,
				codigos: codigos,
			},
			success: function(response) {
				if (response.success) {
					currentQRData = response.data;
					exibirQRCode(response.data);
					showSuccess('✓ QR code gerado com ' + response.data.quantidade + ' códigos!');
				} else {
					showError(response.data || qrEtiquetaParams.i18n.erro);
				}
			},
			error: function() {
				showError(qrEtiquetaParams.i18n.erro);
			},
			complete: function() {
				$loadingSpinner.hide();
				$gerarBtn.prop('disabled', false);
			},
		});
	}

	/**
	 * Exibir QR code na preview
	 */
	function exibirQRCode(data) {
		let html = '<div class="qr-code-display" style="text-align: center;">';
		html += '<img src="' + escapeHtml(data.image_url) + '" alt="QR Code" style="max-width: 300px; margin: 20px auto;">';
		html += '<div style="margin-top: 15px; background: #f5f5f5; padding: 10px; border-radius: 4px;">';
		html += '<strong style="display: block; margin-bottom: 10px;">Códigos na sequência:</strong>';
		html += '<div style="text-align: left; font-family: monospace; line-height: 1.8;">';
		
		data.codigos.forEach(function(codigo) {
			html += escapeHtml(codigo) + '<br>';
		});
		
		html += '</div></div></div>';

		$qrPreview.html(html);
		$qrActions.show();
	}

	/**
	 * Imprimir etiqueta
	 */
	function imprimirEtiqueta(data) {
		const printUrl = qrEtiquetaParams.ajaxUrl +
			'?action=qr_etiqueta_print_qr' +
			'&print=1' +
			'&qr_data=' + encodeURIComponent(data.qr_data);
		window.open(printUrl, '_blank', 'width=420,height=240,scrollbars=no,resizable=yes');
	}

	/**
	 * Baixar PDF da etiqueta
	 */
	function baixarPDF(data) {
		// Criar URL para download
		const downloadUrl = qrEtiquetaParams.ajaxUrl + 
			'?action=qr_etiqueta_download_pdf' + 
			'&qr_data=' + encodeURIComponent(data.qr_data);
		
		// Abrir em nova aba para download
		window.open(downloadUrl, '_blank');
	}

	/**
	 * Resetar formulário
	 */
	function resetarFormulario() {
		$form[0].reset();
		$codigos.focus();
		$qrPreview.html('<p class="placeholder">Cole os códigos e clique em "Gerar QR Codes"</p>');
		$qrActions.hide();
		$resultMessage.hide();
		currentQRData = null;
	}

	/**
	 * Mostrar mensagem de erro
	 */
	function showError(message) {
		$resultMessage
			.removeClass('success')
			.addClass('notice-error')
			.html('<p>' + escapeHtml(message) + '</p>')
			.show();
	}

	/**
	 * Mostrar mensagem de sucesso
	 */
	function showSuccess(message) {
		$resultMessage
			.removeClass('notice-error')
			.addClass('success')
			.html('<p>' + escapeHtml(message) + '</p>')
			.show();
	}

	/**
	 * Escapar HTML
	 */
	function escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;',
		};
		return text.replace(/[&<>"']/g, (m) => map[m]);
	}
});
