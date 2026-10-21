<?php
/**
 * Latest blog posts — Figma 7967:9040 (+ line 7967:8882).
 * 3 news cards; slider below 1200.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_posts = rts_card_posts( 'post', $args['items'] ?? [], 3 );

$rts_link = rts_link( $args['link'] ?? [] );
$rts_link = $rts_link && ! empty( $args['link']['label'] ) ? $rts_link : null;

if ( empty( $args['title'] ) && ! $rts_link && empty( $rts_posts ) ) {
	return;
}
?>
<section class="home-news">
	<div class="run-line home-news__line js-run-line" data-duration="20" data-start="0.81" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 697" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M2083.5 696.5H2043.61C2013.97 696.5 1989.1 674.157 1985.94 644.689L1964.06 440.811C1960.9 411.343 1936.03 389 1906.39 389H1514.5C1482.47 389 1456.5 363.033 1456.5 331V212.5V60C1456.5 27.9675 1430.03 0.5 1398 0.5H480.5C448.467 0.5 422.5 26.4675 422.5 58.5V406C422.5 438.033 396.533 464 364.5 464H-71" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 22%; --y: 51%;"></span>
	</div>
	<div class="container">
		<?php if ( ! empty( $args['title'] ) || $rts_link ) { ?>
		<div class="section-head">
			<?php rts_title( $args, 'h2', 'section-head__title h2', '--title-w: 12.3em' ); ?>
			<?php if ( $rts_link ) { ?>
				<a class="link-line"<?php echo rts_link_attrs( $rts_link, $args['link']['label'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $args['link']['label'] ); ?> <span class="link-line__icon" aria-hidden="true"><?php rts_icon( 'icons/arrow.svg' ); ?></span></a>
			<?php } ?>
		</div>

		<?php } ?>

		<?php if ( ! empty( $rts_posts ) ) { ?>
		<div class="grid-slider swiper js-grid-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_posts as $rts_post ) { ?>
					<div class="swiper-slide">
						<?php get_template_part( 'template-parts/cards/news', null, [ 'post' => $rts_post ] ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>
		<?php } ?>
	</div>
</section>
