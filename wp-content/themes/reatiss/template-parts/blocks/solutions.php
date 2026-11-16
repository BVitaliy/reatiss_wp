<?php
/**
 * Advanced Engineering Solutions — Figma 7967:8984.
 * ≥1200 with WebGL2: a 3D teardown scrubbed by the scroll (app-solutions-3d.js,
 * three.js, imported by app-solutions.js; drawn into __scene): laptop → top
 * case off → board slides out → package lifts and flips → lid off, die out →
 * package layers left, die layers right; the items stand in two columns by
 * the model (left = under the title, no lines to the layers).
 * Item: blue dot · title · 1–2 lines · text link;
 * an optional CTA (text + button) under the scene once the services are out.
 * Without WebGL — the video scene (app-solutions.js): the screen holds still
 * while scrolling opens the chip into layers (video/chip1.mp4), then zooms
 * layer by layer (video/chip2.mp4) and shows the item for each layer with a
 * lead line from its number. Below 1200 — a plain list under a looping clip:
 * the mobile video (rendered from the 3D scene, `?solrender`), loaded only there.
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
$rts_mobile = ! empty( $args['video_mobile'] ) ? (string) $args['video_mobile'] : rts_asset( 'video/solutions-mobile.mp4' );
// poster: the block's own, else the theme's — only for the theme video
$rts_poster = rts_image_id( $args['video_mobile_poster'] ?? 0 ) ? (string) wp_get_attachment_image_url( rts_image_id( $args['video_mobile_poster'] ), 'large' ) : '';
if ( '' === $rts_poster && empty( $args['video_mobile'] ) ) {
	$rts_poster = rts_asset( 'img/home/solutions-3d/mobile-poster.webp' );
}

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
$rts_scene    = rts_asset( 'js/app-solutions-3d.js' ) . '?v=' . rts_asset_ver( 'js/app-solutions-3d.js' );
$rts_cta_text = trim( (string) ( $args['cta_text'] ?? '' ) );
$rts_cta_btn  = rts_has_button( $args['cta_btn'] ?? [] );
?>
<section class="home-solutions js-solutions" data-page-theme="dark" data-scene="<?php echo esc_url( $rts_scene ); ?>"<?php echo $rts_zoom ? ' data-zoom="' . esc_attr( (string) wp_json_encode( $rts_zoom ) ) . '"' : ''; ?>>
	<?php // cursor-reactive dot grid — invisible at rest, dots light up only around the cursor (app-stats.js, ≥1200) ?>
	<div class="home-solutions__dots js-sol-dots" aria-hidden="true"></div>

	<div class="home-solutions__bg" aria-hidden="true">
		<?php // the video scene's clips (≥1200 without WebGL): app-solutions.js loads them when it runs ?>
		<video class="home-solutions__video home-solutions__video--reveal js-solutions-reveal" width="1024" height="576" muted playsinline preload="none">
			<source src="<?php echo esc_url( $rts_reveal ); ?>" type="video/mp4">
		</video>
		<video class="home-solutions__video home-solutions__video--layers js-solutions-layers" width="1024" height="576" muted playsinline preload="none">
			<source src="<?php echo esc_url( $rts_layers ); ?>" type="video/mp4">
		</video>
		<?php // phones / tablets: loads only when played there (app-solutions.js) ?>
		<video class="home-solutions__video home-solutions__video--mobile js-solutions-mobile" width="1080" height="1080" muted playsinline preload="none"<?php echo '' !== $rts_poster ? ' poster="' . esc_url( $rts_poster ) . '"' : ''; ?>>
			<source src="<?php echo esc_url( $rts_mobile ); ?>" type="video/mp4">
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
					?>
					<li class="home-solutions__item home-solutions__item--<?php echo esc_attr( $rts_side ); ?> js-solutions-item" style="--sol-top: <?php echo esc_attr( (string) $rts_top ); ?>%">
						<span class="home-solutions__num" aria-hidden="true"></span>
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
							<?php
							$rts_link = rts_has_button( $rts_item['link'] ?? [] ) ? rts_link( $rts_item['link'] ) : null;
							if ( $rts_link ) {
								$rts_label = trim( (string) $rts_item['link']['label'] );
								?>
								<a class="home-solutions__link link-arrow"<?php echo rts_link_attrs( $rts_link, $rts_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $rts_label ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></a>
							<?php } ?>
						</span>
					</li>
				<?php } ?>
			</ol>

			<?php if ( '' !== $rts_cta_text || $rts_cta_btn ) { ?>
				<div class="home-solutions__cta js-solutions-cta">
					<?php if ( '' !== $rts_cta_text ) { ?>
						<p class="home-solutions__cta-text text-lg"><?php echo esc_html( $rts_cta_text ); ?></p>
					<?php } ?>
					<?php rts_button( $args['cta_btn'] ?? [], 'btn btn--primary home-solutions__cta-btn' ); ?>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php // gives the section its length: one scroll step per phase ?>
	<div class="home-solutions__track" aria-hidden="true"></div>
</section>
