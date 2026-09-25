<?php
// Execute: php tests/individual.php
define( 'ABSPATH', __DIR__ );
function get_option( $key, $default ) { return $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function sanitize_key( $s ) { return $s; }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
require __DIR__ . '/../qr-etiqueta-plugin/includes/functions.php';
require __DIR__ . '/../qr-etiqueta-plugin/includes/qr-generator.php';
require __DIR__ . '/../qr-etiqueta-plugin/includes/individual-pdf.php';
function check( $condition ) { if ( ! $condition ) { throw new Exception( 'Test failed' ); } }
$generator = new QR_Etiqueta_Generator();
$codes = array_map( function( $n ) { return '00040908' . $n; }, range( 10, 19 ) );
$data = implode( "\n", $codes );
$layout = $generator->layout_individual( $data );
check( $layout['cols'] === 5 && $layout['rows'] == 2 );
check( $layout['cw'] * $layout['cols'] + 2 * $layout['m'] <= $layout['w'] );
check( $layout['ch'] * $layout['rows'] + 2 * $layout['m'] <= $layout['h'] );
$html = $generator->gerar_html_individual( $data );
check( substr_count( $html, '<img ' ) === 10 );
foreach ( $codes as $code ) {
 check( strpos( $html, 'text=' . $code . '&amp;' ) !== false );
 check( strpos( $html, '>' . $code . '</div>' ) !== false );
}
foreach ( [ '', $data . "\n123", "123\n123", "123\nabc", str_repeat( '1', 500 ) ] as $invalid ) {
 try { $generator->layout_individual( $invalid ); throw new Exception( 'Invalid input accepted' ); }
 catch ( InvalidArgumentException $expected ) {}
}
check( $generator->layout_individual( '0' )['codes'] === [ '0' ] );
$pdf = new QR_Etiqueta_Individual_PDF( 100, 50.3 );
$pdf->AddPage();
foreach ( $codes as $i => $code ) { $pdf->numero( 1, 3 + $i * 3, $code, 2 ); }
$bytes = $pdf->Output( 'S' );
if ( isset( $argv[1] ) ) { file_put_contents( $argv[1], $bytes ); }
check( strpos( $bytes, '/MediaBox [0 0 283.46 142.58]' ) !== false );
check( substr_count( $bytes, ') Tj ET' ) === 10 );
check( strpos( $bytes, '/BaseFont /Courier' ) !== false );
echo "PASS: ten individual QR codes, bounds, numbers, invalid inputs, PDF size and text.\n";
