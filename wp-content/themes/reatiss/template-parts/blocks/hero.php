<?php
/**
 * Home hero — Figma 7967:8854. Image or video background (the image is the
 * poster and LCP), editable shade. Scroll motion: app-hero.js; video: app-video.js.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_video     = 'video' === ( $args['media_type'] ?? '' ) ? (string) ( $args['video'] ?? '' ) : '';
$rts_video_mob = '' !== $rts_video ? (string) ( $args['video_mobile'] ?? '' ) : '';
$rts_overlay   = isset( $args['overlay'] ) && '' !== $args['overlay'] ? max( 0, min( 90, (int) $args['overlay'] ) ) / 100 : 0.4; // client: 30 → 40
$rts_image     = rts_image_id( $args['image'] ?? 0 );

if ( empty( $args['title'] ) && empty( $args['text'] ) && ! rts_has_button( $args['btn'] ?? [] ) && ! $rts_image && '' === $rts_video ) {
	return;
}
?>
<section class="home-hero<?php echo '' !== $rts_video ? ' home-hero--video js-bg-video-section' : ''; ?> js-hero">
	<div class="home-hero__media js-hero-media">
		<?php
		if ( $rts_image ) {
			rts_picture(
				$rts_image,
				$args['image_mobile'] ?? 0,
				[
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				]
			);
		}
		?>
		<?php if ( '' !== $rts_video ) { ?>
			<video class="home-hero__video js-bg-video" muted loop playsinline preload="none" aria-hidden="true"
				data-src="<?php echo esc_url( $rts_video ); ?>"
				<?php if ( '' !== $rts_video_mob ) { ?>data-src-mob="<?php echo esc_url( $rts_video_mob ); ?>"<?php } ?>></video>
		<?php } ?>
	</div>
	<div class="home-hero__shade js-hero-shade" style="opacity: <?php echo esc_attr( (string) $rts_overlay ); ?>" aria-hidden="true"></div>

	<?php // Figma "bg": concentric hexagon outlines, white 10% ?>
	<svg class="home-hero__grid js-hero-grid" viewBox="0 -589 1795 2172" fill="none" aria-hidden="true">
		<path d="M1386.5 225.359V-242.359L898 -589L409.5 -242.359V225.359L898 572L1386.5 225.359Z" />
		<path d="M1227.5 149.219V-166.219L898 -400L568.5 -166.219V149.219L898 383L1227.5 149.219Z" />
		<path d="M1589.94 846.819V182.956L897.5 -309.057L205.057 182.956V846.819L897.5 1338.83L1589.94 846.819Z" />
		<path d="M1794.5 945.094V84.6814L897.5 -553L0.5 84.6814V945.094L897.5 1582.77L1794.5 945.094Z" />
	</svg>

	<div class="container home-hero__content js-hero-content">
		<?php rts_title( $args, 'h1', 'home-hero__title h1 clr-white' ); ?>
		<?php if ( ! empty( $args['text'] ) ) { ?>
			<p class="home-hero__text text-lg"><?php echo esc_html( $args['text'] ); ?></p>
		<?php } ?>
		<?php rts_button( $args['btn'] ?? [], 'btn home-hero__btn' ); ?>
	</div>
</section>
