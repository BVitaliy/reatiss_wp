<?php
/**
 * Search result row (.result-row: title + ↘ that turns → on hover).
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
?>
<span class="step-line"></span>
<a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>" class="result-row"<?php echo rts_aria_label( get_the_title( $rts_post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
	<span class="result-row__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></span>
	<?php rts_icon( 'icons/arrow.svg' ); ?>
</a>
