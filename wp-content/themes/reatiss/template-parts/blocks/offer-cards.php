<?php
/**
 * Offer cards — Figma 8271:40201. Dark section: cursor dot field, two hexagon
 * outlines, centred title, up to three cards (drawing draws in — .js-draw).
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
?>
<section class="about-offer section-dark">
	<div class="dot-field js-dot-field" aria-hidden="true"></div>
	<svg class="about-offer__bg" viewBox="0 0 1795 2137" fill="none" aria-hidden="true">
		<path d="M1589.94 1400.43V736.569L897.5 244.557L205.057 736.569V1400.43L897.5 1892.44L1589.94 1400.43Z" />
		<path d="M1794.5 1498.71V638.295L897.5 0.613471L0.5 638.295V1498.71L897.5 2136.39L1794.5 1498.71Z" />
	</svg>

	<div class="container about-offer__inner">
		<?php rts_title( $args, 'h2', 'about-offer__title h2' ); ?>
		<?php if ( ! empty( $rts_items ) ) { ?>
		<ul class="about-offer__list">
			<?php foreach ( $rts_items as $rts_item ) { ?>
				<li class="offer-card">
					<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
						<div class="offer-card__icon js-draw">
							<?php rts_picture( $rts_item['image'], null, [ 'alt' => '' ], 'medium_large' ); ?>
						</div>
					<?php } ?>
					<div class="offer-card__body">
						<?php if ( ! empty( $rts_item['title'] ) ) { ?>
							<h3 class="offer-card__title h5"><?php echo esc_html( $rts_item['title'] ); ?></h3>
						<?php } ?>
						<?php if ( ! empty( $rts_item['text'] ) ) { ?>
							<p class="offer-card__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
						<?php } ?>
					</div>
				</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
