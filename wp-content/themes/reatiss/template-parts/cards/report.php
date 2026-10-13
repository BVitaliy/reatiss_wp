<?php
/**
 * On Shelf report row (.result-row--desc): a button that opens the "reports"
 * popup; js/app-reports.js puts the report title into the form.
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

$rts_text = (string) get_post_meta( $rts_post->ID, 'description', true );
?>
<span class="step-line"></span>
<button class="result-row result-row--desc" type="button" data-modal-open="reports" data-report="<?php echo esc_attr( get_the_title( $rts_post ) ); ?>">
	<span class="result-row__body">
		<span class="result-row__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></span>
		<?php if ( '' !== $rts_text ) { ?>
			<span class="result-row__text text-lg"><?php echo esc_html( $rts_text ); ?></span>
		<?php } ?>
	</span>
	<?php rts_icon( 'icons/arrow.svg' ); ?>
</button>
