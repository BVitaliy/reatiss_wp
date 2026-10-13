<?php
/**
 * Info cards — Figma 8271:40659 (expertise workflow): centred title + photo
 * cards in a .grid-slider (grid on desktop, slider below 1200); optional line
 * coming from behind the block above.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

if ( empty( $rts_items ) ) {
	return;
}

$rts_line = ! empty( $args['line'] );
?>
<section class="expertise-workflow">
	<?php if ( $rts_line ) { ?>
		<div class="run-line expertise-workflow__line js-run-line" data-duration="22" data-start="0.83" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 624" preserveAspectRatio="xMidYMin slice" fill="none">
				<path class="run-line__path js-run-path" d="M2249 623.5H2209.11C2179.47 623.5 2154.6 601.157 2151.44 571.689L2129.56 367.811C2126.4 338.344 2101.53 316 2071.89 316H1680C1647.97 316 1622 290.033 1622 258V139.5V60C1622 27.968 1595.53 0.5 1563.5 0.5H524.5C492.467 0.5 466.5 26.468 466.5 58.5V292C466.5 324.033 440.533 350 408.5 350H-27" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 24.3%; --y: 45.2%;"></span>
		</div>
	<?php } ?>

	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2" style="--title-w: 18em"><?php rts_br( $args['title'] ?? '' ); ?></h2>
		</div>

		<div class="grid-slider swiper js-grid-slider" style="--cols: 4">
			<div class="swiper-wrapper">
				<?php foreach ( $rts_items as $rts_item ) { ?>
					<div class="swiper-slide">
						<article class="info-card">
							<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
								<div class="info-card__media">
									<?php rts_picture( $rts_item['image'], null, [], 'large' ); ?>
								</div>
							<?php } ?>
							<h3 class="info-card__title h5"><?php echo esc_html( (string) ( $rts_item['title'] ?? '' ) ); ?></h3>
							<?php if ( ! empty( $rts_item['text'] ) ) { ?>
								<p class="info-card__text text-md"><?php echo esc_html( $rts_item['text'] ); ?></p>
							<?php } ?>
						</article>
					</div>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination"></div>
		</div>
	</div>
</section>
