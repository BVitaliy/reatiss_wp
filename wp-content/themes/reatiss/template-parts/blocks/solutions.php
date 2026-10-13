<?php
/**
 * Advanced Engineering Solutions — Figma 7967:8984.
 * Scroll scene (app-solutions.js, ≥1200): the screen holds still while
 * scrolling opens the chip into layers (video/chip1.mp4), then zooms layer by
 * layer (video/chip2.mp4) and shows the item for each layer with a lead line
 * from its number. Below 1200 — a plain list under a looping clip.
 * The clips and their zoom timings (ZOOM in app-solutions.js) belong to the
 * theme; the items (up to 4 — one per zoom stop) are editable.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = array_slice( is_array( $args['items'] ?? null ) ? $args['items'] : [], 0, 4 );

if ( empty( $rts_items ) ) {
	return;
}
?>
<section class="home-solutions js-solutions" data-page-theme="dark">
	<?php // cursor-reactive dot grid — invisible at rest, dots light up only around the cursor (app-stats.js, ≥1200) ?>
	<div class="home-solutions__dots js-sol-dots" aria-hidden="true"></div>

	<div class="home-solutions__bg" aria-hidden="true">
		<video class="home-solutions__video home-solutions__video--reveal js-solutions-reveal" width="1024" height="576" muted playsinline preload="metadata">
			<source src="<?php echo esc_url( rts_asset( 'video/chip1.mp4' ) ); ?>" type="video/mp4">
		</video>
		<video class="home-solutions__video home-solutions__video--layers js-solutions-layers" width="1024" height="576" muted playsinline preload="metadata">
			<source src="<?php echo esc_url( rts_asset( 'video/chip2.mp4' ) ); ?>" type="video/mp4">
		</video>
	</div>

	<div class="home-solutions__stage js-solutions-stage">
		<div class="container home-solutions__inner">
			<h2 class="home-solutions__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
			<svg class="home-solutions__leads js-solutions-leads" aria-hidden="true"></svg>

			<ol class="home-solutions__list">
				<?php
				foreach ( $rts_items as $rts_item ) {
					$rts_side = 'left' === ( $rts_item['side'] ?? '' ) ? 'left' : 'right';
					$rts_top  = isset( $rts_item['top'] ) && '' !== $rts_item['top'] ? (int) $rts_item['top'] : 20;
					$rts_aim  = preg_match( '/^\s*[\d.]+\s*,\s*[\d.]+\s*$/', (string) ( $rts_item['aim'] ?? '' ) ) ? preg_replace( '/\s+/', '', $rts_item['aim'] ) : '';
					?>
					<li class="home-solutions__item home-solutions__item--<?php echo esc_attr( $rts_side ); ?> js-solutions-item" style="--sol-top: <?php echo esc_attr( (string) $rts_top ); ?>%"<?php echo '' !== $rts_aim ? ' data-aim="' . esc_attr( $rts_aim ) . '"' : ''; ?>>
						<span class="home-solutions__num"><?php echo esc_html( (string) ( $rts_item['num'] ?? '' ) ); ?></span>
						<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
							<span class="home-solutions__art">
								<?php rts_picture( $rts_item['image'], null, [], 'large' ); ?>
							</span>
						<?php } ?>
						<span class="home-solutions__body">
							<h3 class="home-solutions__item-title h5"><?php echo esc_html( (string) ( $rts_item['title'] ?? '' ) ); ?></h3>
							<?php if ( ! empty( $rts_item['text'] ) ) { ?>
								<p class="home-solutions__item-text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
							<?php } ?>
						</span>
					</li>
				<?php } ?>
			</ol>
		</div>
	</div>

	<?php // gives the section its length: one scroll step per phase ?>
	<div class="home-solutions__track" aria-hidden="true"></div>
</section>
