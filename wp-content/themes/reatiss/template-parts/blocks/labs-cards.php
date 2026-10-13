<?php
/**
 * Lab cards — Figma 7967:9021 (+ line 7967:8876).
 * 3 cards; slider below 1200 (app-swiper.js).
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_posts = rts_card_posts( 'rts_lab', $args['items'] ?? [] );

if ( empty( $rts_posts ) ) {
	return;
}
?>
<section class="home-labs">

	<div class="run-line home-labs__line js-run-line" data-duration="18" data-start="0.19" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 649" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M-67 315.5H477.893C502.977 315.5 525.218 299.375 533.018 275.535L609.925 40.4651C617.725 16.6248 639.966 0.5 665.05 0.5H1341.62C1367.58 0.5 1390.37 17.7468 1397.43 42.7256L1556.57 605.774C1563.63 630.753 1586.42 648 1612.38 648H2016" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 21.6%; --y: 48.6%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2" style="--title-w: 11.8em"><?php rts_br( $args['title'] ?? '' ); ?></h2>
		</div>

		<div class="grid-slider swiper js-grid-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_posts as $rts_post ) { ?>
					<div class="swiper-slide">
						<?php get_template_part( 'template-parts/cards/lab', null, [ 'post' => $rts_post ] ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>
	</div>
</section>
