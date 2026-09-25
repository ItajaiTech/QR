<?php
/** PDF com números em fonte Courier e dimensões físicas em milímetros. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class QR_Etiqueta_Individual_PDF extends FPDF {
	public function __construct( $width, $height ) {
		parent::__construct( 'P', 'mm', [ $width, $height ] );
		$this->k = 72 / 25.4;
		$this->w = $width;
		$this->h = $height;
		$this->wPt = $width * $this->k;
		$this->hPt = $height * $this->k;
		$this->SetMargins( 0, 0, 0 );
		$this->SetAutoPageBreak( false, 0 );
	}

	public function numero( $x, $y, $number, $size ) {
		if ( ! preg_match( '/^\d+$/', $number ) ) {
			throw new InvalidArgumentException( 'Número inválido.' );
		}
		$this->_out( sprintf( 'BT /FQR %.4F Tf %.4F %.4F Td (%s) Tj ET', $size * $this->k, $x * $this->k, ( $this->h - $y ) * $this->k, $number ) );
	}

	protected function _putresourcedict() {
		parent::_putresourcedict();
		$this->_out( '/Font << /FQR << /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold >> >>' );
	}

	// A versão mínima incluída no plugin não monta corretamente a árvore de páginas.
	protected function _putpages() {
		$this->_newobj();
		$content = $this->n;
		$this->_out( '<< /Length ' . strlen( $this->pages[1] ) . ' >>' );
		$this->_out( 'stream' );
		$this->_out( $this->pages[1] );
		$this->_out( 'endstream' );
		$this->_out( 'endobj' );
		$this->_newobj();
		$page = $this->n;
		$this->PageInfo[1]['n'] = $page;
		$this->_out( '<< /Type /Page /Parent 1 0 R /Resources 2 0 R /Contents ' . $content . ' 0 R >>' );
		$this->_out( 'endobj' );
		$this->offsets[1] = strlen( $this->buffer );
		$this->_out( '1 0 obj' );
		$this->_out( sprintf( '<< /Type /Pages /Kids [%d 0 R] /Count 1 /MediaBox [0 0 %.2F %.2F] >>', $page, $this->wPt, $this->hPt ) );
		$this->_out( 'endobj' );
	}
}
