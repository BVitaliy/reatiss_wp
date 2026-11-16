<?php
/**
 * Advanced Engineering Solutions — Figma 7967:8984.
 * ≥1200 with WebGL2: a 3D teardown scrubbed by the scroll (app-solutions-3d.js,
 * three.js, imported by app-solutions.js; drawn into __scene): laptop → top
 * case off → board slides out → package lifts and flips → lid off, die out →
 * package layers left, die layers right; each item stands by its layer
 * (data-layer: pkg-top | pkg-inner | pkg-bottom | pkg-lid | die-1 … die-4).
 * Without WebGL — the video scene (app-solutions.js): the screen holds still
 * while scrolling opens the chip into layers (video/chip1.mp4), then zooms
 * layer by layer (video/chip2.mp4) and shows the item for each layer with a
 * lead line from its number. Below 1200 — a plain list under a looping clip.
 * Both clips and the zoom timings are editable in the block: empty videos
 * fall back to the theme's chip clips, empty timings to the ones measured on
 * them (ZOOM in app-solutions.js). One item = one zoom of video 2.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = array_values( is_array( $args['items'] ?? null ) ? $args['items'] : [] );

if ( empty( $rts_items ) ) {
	return;
}

$rts_reveal = ! empty( $args['video_reveal'] ) ? (string) $args['video_reveal'] : rts_asset( 'video/chip1.mp4' );
$rts_layers = ! empty( $args['video_layers'] ) ? (string) $args['video_layers'] : rts_asset( 'video/chip2.mp4' );

// Zoom timings for app-solutions.js: [ zoom-in end, zoom-out end ] per item,
// only when every item has both (otherwise the script keeps its own).
$rts_zoom = [];

foreach ( $rts_items as $rts_item ) {
	$rts_in  = isset( $rts_item['zoom_in'] ) && '' !== $rts_item['zoom_in'] ? (float) $rts_item['zoom_in'] : null;
	$rts_out = isset( $rts_item['zoom_out'] ) && '' !== $rts_item['zoom_out'] ? (float) $rts_item['zoom_out'] : null;

	if ( null === $rts_in || null === $rts_out || $rts_out <= $rts_in || ( $rts_zoom && $rts_in <= end( $rts_zoom )[1] ) ) {
		$rts_zoom = [];
		break;
	}

	$rts_zoom[] = [ $rts_in, $rts_out ];
}

// The 3D module (+ its own version: an ES module import skips WP's ?ver).
$rts_scene     = rts_asset( 'js/app-solutions-3d.js' ) . '?v=' . rts_asset_ver( 'js/app-solutions-3d.js' );
$rts_layers_3d = [ 'pkg-top', 'pkg-inner', 'pkg-bottom', 'pkg-lid', 'die-1', 'die-2', 'die-3', 'die-4' ];
?>
<section class="home-solutions js-solutions" data-page-theme="dark" data-scene="<?php echo esc_url( $rts_scene ); ?>"<?php echo $rts_zoom ? ' data-zoom="' . esc_attr( (string) wp_json_encode( $rts_zoom ) ) . '"' : ''; ?>>
	<?php // cursor-reactive dot grid — invisible at rest, dots light up only around the cursor (app-stats.js, ≥1200) ?>
	<div class="home-solutions__dots js-sol-dots" aria-hidden="true"></div>

	<div class="home-solutions__bg" aria-hidden="true">
		<video class="home-solutions__video home-solutions__video--reveal js-solutions-reveal" width="1024" height="576" muted playsinline preload="metadata">
			<source src="<?php echo esc_url( $rts_reveal ); ?>" type="video/mp4">
		</video>
		<video class="home-solutions__video home-solutions__video--layers js-solutions-layers" width="1024" height="576" muted playsinline preload="metadata">
			<source src="<?php echo esc_url( $rts_layers ); ?>" type="video/mp4">
		</video>
	</div>

	<?php // the 3D scene's canvas (≥1200, app-solutions-3d.js) ?>
	<div class="home-solutions__scene js-solutions-scene" aria-hidden="true"></div>

	<div class="home-solutions__stage js-solutions-stage">
		<div class="container home-solutions__inner">
			<?php rts_title( $args, 'h2', 'home-solutions__title h2' ); ?>
			<svg class="home-solutions__leads js-solutions-leads" aria-hidden="true"></svg>

			<ol class="home-solutions__list">
				<?php
				foreach ( $rts_items as $rts_item ) {
					$rts_side = 'left' === ( $rts_item['side'] ?? '' ) ? 'left' : 'right';
					$rts_top  = isset( $rts_item['top'] ) && '' !== $rts_item['top'] ? (int) $rts_item['top'] : 20;
					$rts_aim  = preg_match( '/^\s*[\d.]+\s*,\s*[\d.]+\s*$/', (string) ( $rts_item['aim'] ?? '' ) ) ? preg_replace( '/\s+/', '', $rts_item['aim'] ) : '';
					$rts_layer = in_array( $rts_item['layer'] ?? '', $rts_layers_3d, true ) ? (string) $rts_item['layer'] : '';
					?>
					<li class="home-solutions__item home-solutions__item--<?php echo esc_attr( $rts_side ); ?> js-solutions-item" style="--sol-top: <?php echo esc_attr( (string) $rts_top ); ?>%"<?php echo '' !== $rts_aim ? ' data-aim="' . esc_attr( $rts_aim ) . '"' : ''; ?><?php echo '' !== $rts_layer ? ' data-layer="' . esc_attr( $rts_layer ) . '"' : ''; ?>>
						<?php if ( ! empty( $rts_item['num'] ) ) { ?>
							<span class="home-solutions__num"><?php echo esc_html( $rts_item['num'] ); ?></span>
						<?php } ?>
						<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
							<span class="home-solutions__art">
								<?php rts_picture( $rts_item['image'], null, [], 'large' ); ?>
							</span>
						<?php } ?>
						<span class="home-solutions__body">
							<?php if ( ! empty( $rts_item['title'] ) ) { ?>
								<h3 class="home-solutions__item-title h5"><?php echo esc_html( $rts_item['title'] ); ?></h3>
							<?php } ?>
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
