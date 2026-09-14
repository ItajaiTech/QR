/**
 * JavaScript para frontend (shortcode)
 */

jQuery(document).ready(function($) {
	'use strict';

	const $form = $('#qr-form-frontend');
	const $codigos = $('#codigos-frontend');
	const $loading = $('#loading-frontend');
	const $result = $('#qr-result-frontend');
	const $preview = $('#qr-preview-frontend');
	const $error = $('#error-frontend');
	const $printBtn = $('#print-btn-frontend');
	const $downloadPdfBtn = $('#download-pdf-btn-frontend');
	const $newBtn = $('#new-btn-frontend');

	let currentQRData = null;

	// Enviar formulário
	$form.on('submit', function(e) {
		e.preventDefault();
		gerarQRCode();
	});

	// Botão imprimir
	$printBtn.on('click', function() {
		if (currentQRData) {
			imprimirEtiqueta();
		}
	});

	// Botão baixar PDF
	$downloadPdfBtn.on('click', function() {
		if (currentQRData) {
			baixarPDF();
		}
	});

	// Botão novo
	$newBtn.on('click', function() {
		resetarFormulario();
	});

	/**
	 * Gerar QR code com sequência
	 */
	function gerarQRCode() {
		const codigos = $codigos.val().trim();

		if (!codigos) {
			mostrarErro('Insira pelo menos um código');
			return;
		}

		const codigosArray = codigos.split(/\r?\n/).map(function(codigo) {
			return codigo.trim();
		}).filter(Boolean);
		const codigosRepetidos = codigosArray.filter(function(codigo, indice) {
			return codigosArray.indexOf(codigo) !== indice;
		}).filter(function(codigo, indice, lista) {
			return lista.indexOf(codigo) === indice;
		});

		if (codigosArray.length > 50) {
			mostrarErro('Máximo de 50 códigos por vez');
			return;
		}

		if (codigosRepetidos.length) {
			mostrarErro('Número(s) repetido(s): ' + codigosRepetidos.join(', ') + '. Remova a repetição para continuar.');
			return;
		}

		$loading.show();
		$result.hide();
		$error.hide();

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
					$result.show();
				} else {
					mostrarErro(response.data || 'Erro ao gerar QR code');
				}
			},
			error: function() {
				mostrarErro('Erro na requisição');
			},
			complete: function() {
				$loading.hide();
			},
		});
	}

	/**
	 * Exibir QR code
	 */
	function exibirQRCode(data) {
		let html = '<div style="text-align: center;">';
		html += '<img src="' + escapeHtml(data.image_url) + '" alt="QR Code" style="max-width: 300px; margin: 20px auto;">';
		html += '<div style="margin-top: 15px; background: #f5f5f5; padding: 10px; border-radius: 4px;">';
		html += '<strong style="display: block; margin-bottom: 10px;">Códigos na sequência:</strong>';
		html += '<div style="text-align: left; font-family: monospace; line-height: 1.8;">';
		
		data.codigos.forEach(function(codigo) {
			html += escapeHtml(codigo) + '<br>';
		});
		
		html += '</div></div></div>';

		$preview.html(html);
	}

	/**
	 * Imprimir etiqueta
	 */
	function imprimirEtiqueta() {
		const printUrl = qrEtiquetaParams.ajaxUrl +
			'?action=qr_etiqueta_print_qr' +
			'&print=1' +
			'&qr_data=' + encodeURIComponent(currentQRData.qr_data);
		window.open(printUrl, '_blank', 'width=420,height=240,scrollbars=no,resizable=yes');
	}

	/**
	 * Baixar PDF
	 */
	function baixarPDF() {
		const downloadUrl = qrEtiquetaParams.ajaxUrl + 
			'?action=qr_etiqueta_download_pdf' + 
			'&qr_data=' + encodeURIComponent(currentQRData.qr_data);
		
		window.open(downloadUrl, '_blank');
	}

	/**
	 * Resetar formulário
	 */
	function resetarFormulario() {
		$form[0].reset();
		$codigos.focus();
		$preview.html('<p style="color: #999;">Cole os códigos (um por linha) e clique em "Gerar"</p>');
		$result.hide();
		$error.hide();
		currentQRData = null;
	}

	/**
	 * Mostrar erro
	 */
	function mostrarErro(message) {
		$error.html(escapeHtml(message)).show();
		$result.hide();
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
		return text.replaceAll(/[&<>"']/g, (m) => map[m]);
	}
});
