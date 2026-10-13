<?php
/**
 * Expertise cards — Figma 7967:8886 (+ line 7967:8873).
 * Grid 2×2 on desktop, slider below 768 (app-swiper.js); line: app-lines.js.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_posts = rts_card_posts( 'rts_expertise', $args['items'] ?? [] );

if ( empty( $rts_posts ) ) {
	return;
}

$rts_more = rts_link( $args['more_link'] ?? [] );
?>
<section class="home-expertise">

	<div class="run-line home-expertise__line js-run-line" data-duration="18" data-start="0.19" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 404" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M-343 0.433L328.915 386.716C337.71 391.773 347.677 394.433 357.822 394.433H478.054C491.743 394.433 504.991 389.592 515.454 380.764L749.593 183.231C760.056 174.404 773.304 169.562 786.993 169.562H1176.96C1192.3 169.562 1206.97 175.62 1217.94 186.345C1281.39 248.391 1382.53 342.464 1422.05 379.087C1432.74 388.997 1446.73 394.434 1461.31 394.434L1568.25 394.433C1578.48 394.433 1588.53 391.725 1597.38 386.583L2262 0.433" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 19.5%; --y: 97.6%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
		</div>

		<div class="grid-slider swiper js-grid-slider" data-slider-below="768" style="--cols: 2">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_posts as $rts_post ) { ?>
					<div class="swiper-slide">
						<?php get_template_part( 'template-parts/cards/expertise', null, [ 'post' => $rts_post ] ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>

		<?php if ( $rts_more && ! empty( $args['more_link']['label'] ) ) { ?>
			<p class="home-expertise__more h4">
				<?php echo esc_html( (string) ( $args['more_before'] ?? '' ) ); ?>
				<a class="link-inline"<?php echo rts_link_attrs( $rts_more ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $args['more_link']['label'] ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></a>
				<?php echo esc_html( (string) ( $args['more_after'] ?? '' ) ); ?>
			</p>
		<?php } ?>
	</div>
</section>
