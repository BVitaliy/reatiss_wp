<?php
/**
 * On Shelf table row (.shelf-table__row): part number and die marking (the
 * title) | functionality. A button that opens the "reports" popup;
 * js/app-reports.js puts the title into the form and the description into
 * the popup's plate above it.
 *
 * @package reatiss
 *
 * @var array $args { post: WP_Post }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_post = $args['post'] ?? null;

if ( ! $rts_post instanceof WP_Post ) {
	return;
}

$rts_title = get_the_title( $rts_post );
$rts_func  = (string) get_post_meta( $rts_post->ID, 'functionality', true );
$rts_text  = (string) get_post_meta( $rts_post->ID, 'description', true );
?>
<button class="shelf-table__row" type="button" data-modal-open="reports" data-report="<?php echo esc_attr( $rts_title ); ?>" data-report-text="<?php echo esc_attr( $rts_text ); ?>">
	<span class="shelf-table__cell"><?php echo esc_html( $rts_title ); ?></span>
	<span class="shelf-table__cell"><?php echo esc_html( $rts_func ); ?></span>
</button>
