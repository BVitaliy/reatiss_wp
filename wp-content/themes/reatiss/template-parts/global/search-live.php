<?php
/**
 * Header search dropdown (Figma 8017:19131): up to six titles, the total and
 * "View All" to the results page. Rendered by the rts_live_search AJAX call.
 *
 * @package reatiss
 *
 * @var array $args { query: WP_Query, term: string }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_query = $args['query'] ?? null;

if ( ! $rts_query instanceof WP_Query ) {
	return;
}

$rts_total = (int) $rts_query->found_posts;
?>
<?php if ( $rts_total ) { ?>
	<ul class="header__results-list">
		<?php foreach ( $rts_query->posts as $rts_post ) { ?>
			<li><a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>"<?php echo rts_aria_label( get_the_title( $rts_post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>><?php echo esc_html( get_the_title( $rts_post ) ); ?></a></li>
		<?php } ?>
	</ul>
	<span class="step-line"></span>
<?php } ?>
<div class="header__results-foot">
	<p><?php esc_html_e( 'Found:', 'reatiss' ); ?><b><?php echo esc_html( number_format_i18n( $rts_total ) ); ?></b></p>
	<?php if ( $rts_total ) { ?>
		<a href="<?php echo esc_url( get_search_link( (string) ( $args['term'] ?? '' ) ) ); ?>" class="link-arrow"<?php echo rts_aria_label( __( 'View all results', 'reatiss' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>><?php esc_html_e( 'View All', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></a>
	<?php } ?>
</div>
