<?php
/**
 * Reveals — Figma 8271:41456 (service), 8271:40254 (About "What Drives Our
 * Expertise", with its own line from the block above), lab "Why choose".
 * Centred title + cards (drawing draws in — .js-draw) between vertical step
 * lines; grid on desktop, slider below 1200.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

if ( empty( $args['title'] ) && empty( $rts_items ) ) {
	return;
}

$rts_line    = ! empty( $args['line'] );
$rts_title_w = ! empty( $args['title_width'] ) ? (float) $args['title_width'] : 18;
?>
<section class="service-reveals<?php echo $rts_line ? ' about-drives' : ''; ?>">
	<?php if ( $rts_line ) { ?>
		<div class="run-line about-drives__line js-run-line" data-duration="22" data-start="0.83" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 624" preserveAspectRatio="xMidYMin slice" fill="none">
				<path class="run-line__path js-run-path" d="M2291 623.5H2251.11C2221.47 623.5 2196.6 601.157 2193.44 571.689L2171.56 367.811C2168.4 338.344 2143.53 316 2113.89 316H1722C1689.97 316 1664 290.033 1664 258V139.5V60C1664 27.968 1637.53 0.5 1605.5 0.5L517.5 0.5C485.467 0.5 459.5 26.468 459.5 58.5V292C459.5 324.033 433.533 350 401.5 350H-34" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 23.9%; --y: 45.2%;"></span>
		</div>
	<?php } ?>

	<div class="container">
		<?php if ( ! empty( $args['title'] ) ) { ?>
			<div class="section-head">
				<?php rts_title( $args, 'h2', 'section-head__title h2', '--title-w: ' . $rts_title_w . 'em' ); ?>
			</div>
		<?php } ?>

		<?php if ( ! empty( $rts_items ) ) { ?>
		<div class="service-reveals__list">
			<div class="grid-slider swiper js-grid-slider" style="--cols: 4">
				<div class="swiper-wrapper">
					<?php foreach ( $rts_items as $rts_item ) { ?>
						<div class="swiper-slide">
							<div class="reveal-card">
								<span class="step-line step-line--v reveal-card__line" aria-hidden="true"></span>
								<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
									<div class="reveal-card__media js-draw">
										<?php rts_picture( $rts_item['image'], null, [ 'alt' => '' ], 'medium_large' ); ?>
									</div>
								<?php } ?>
								<?php if ( ! empty( $rts_item['title'] ) ) { ?>
									<h3 class="reveal-card__title h5"><?php echo esc_html( $rts_item['title'] ); ?></h3>
								<?php } ?>
								<?php if ( ! empty( $rts_item['text'] ) ) { ?>
									<p class="reveal-card__text text-md"><?php echo esc_html( $rts_item['text'] ); ?></p>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="grid-slider__pagination"></div>
			</div>
			<span class="step-line step-line--v service-reveals__end" aria-hidden="true"></span>
		</div>
		<?php } ?>
	</div>
</section>
