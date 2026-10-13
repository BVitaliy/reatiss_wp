<?php
/**
 * Related row under an article (Figma 8054:19086 "Discover More Articles") or
 * an event (8054:19345 "Explore More Upcoming Events"): title + link to the
 * list, 3 cards, slider below 1200.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_post     = get_post();
$rts_is_event = 'rts_event' === $rts_post->post_type;
$rts_posts    = rts_related_posts( $rts_post );

if ( empty( $rts_posts ) ) {
	return;
}

$rts_section = rts_section_page_id( $rts_post->post_type );
?>
<section class="related">
	<div class="container">
		<div class="related__head">
			<h2 class="h2"><?php echo esc_html( (string) rts_option( $rts_is_event ? 'related_events_title' : 'related_posts_title' ) ); ?></h2>
			<?php if ( $rts_section ) { ?>
				<a href="<?php echo esc_url( (string) get_permalink( $rts_section ) ); ?>" class="link-line"><?php echo esc_html( $rts_is_event ? __( 'All events', 'reatiss' ) : __( 'All news', 'reatiss' ) ); ?> <span class="link-line__icon" aria-hidden="true"><?php rts_icon( 'icons/arrow.svg' ); ?></span></a>
			<?php } ?>
		</div>

		<div class="grid-slider swiper js-grid-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_posts as $rts_item ) { ?>
					<div class="swiper-slide">
						<?php get_template_part( 'template-parts/cards/' . ( $rts_is_event ? 'event' : 'news' ), null, [ 'post' => $rts_item ] ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>
	</div>
</section>
