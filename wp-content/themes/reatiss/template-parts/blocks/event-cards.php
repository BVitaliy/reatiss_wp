<?php
/**
 * Upcoming events — Figma 7967:9118 (+ line 7967:8879).
 * 3 event cards; slider below 1200.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_posts = rts_card_posts( 'rts_event', $args['items'] ?? [], 3 );

if ( empty( $rts_posts ) ) {
	return;
}
?>
<section class="home-events">
	<div class="run-line home-events__line js-run-line" data-duration="20" data-start="0.84" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1803 765" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M0.5 764.5V422.5C0.5 390.467 26.4675 364.5 58.5 364.5H457.5C489.533 364.5 515.5 338.533 515.5 306.5V58.5C515.5 26.4675 541.467 0.5 573.5 0.5H1317.35C1337.35 0.5 1355.94 10.8019 1366.54 27.7599L1504.81 249C1515.17 266.945 1534.32 278 1555.04 278H2016.5" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 87.2%; --y: 36.3%;"></span>
	</div>
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
			<?php
			$rts_link = rts_link( $args['link'] ?? [] );

			if ( $rts_link && ! empty( $args['link']['label'] ) ) {
				?>
				<a class="link-line"<?php echo rts_link_attrs( $rts_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $args['link']['label'] ); ?> <span class="link-line__icon" aria-hidden="true"><?php rts_icon( 'icons/arrow.svg' ); ?></span></a>
			<?php } ?>
		</div>

		<div class="grid-slider swiper js-grid-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_posts as $rts_post ) { ?>
					<div class="swiper-slide">
						<?php get_template_part( 'template-parts/cards/event', null, [ 'post' => $rts_post ] ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>
	</div>
</section>
