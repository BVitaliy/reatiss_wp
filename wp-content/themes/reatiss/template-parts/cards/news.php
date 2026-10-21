<?php
/**
 * News card — a blog post (home, blog, author, related articles).
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

$rts_thumb = (int) get_post_thumbnail_id( $rts_post );
?>
<a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>" class="news-card is-hover-arrow"<?php echo rts_aria_label( get_the_title( $rts_post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
	<span class="news-card__media cut cut--bl">
		<?php rts_picture( $rts_thumb, null, [], 'large' ); ?>
	</span>
	<span class="news-card__body">
		<span class="news-card__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></span>
		<span class="news-card__meta">
			<span class="news-card__date"><?php rts_icon( 'icons/calendar.svg' ); ?><?php echo esc_html( get_the_date( '', $rts_post ) ); ?></span>
			<span class="link-arrow"><?php esc_html_e( 'Read more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></span>
		</span>
	</span>
</a>
