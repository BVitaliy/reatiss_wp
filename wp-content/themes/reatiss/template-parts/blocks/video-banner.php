<?php
/**
 * Full-width video banner — Figma 7967:9091.
 * Background loop (muted, desktop / mobile cut) starts near the viewport and
 * pauses off-screen; the poster shows until it plays. The button opens the
 * full video in the fullscreen popup (app-video.js); parallax: app-parallax.js.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! rts_image_id( $args['poster'] ?? 0 ) ) {
	return;
}

$rts_video     = (string) ( $args['video'] ?? '' );
$rts_video_mob = (string) ( $args['video_mobile'] ?? '' );
$rts_full      = (string) ( $args['full_video'] ?? '' );
?>
<section class="home-video<?php echo '' !== $rts_video ? ' js-bg-video-section' : ''; ?>" data-parallax-media data-parallax-intensity="160">
	<div class="home-video__media" data-parallax-target>
		<?php
		ob_start();
		rts_picture( $args['poster'], $args['poster_mobile'] ?? 0, [ 'alt' => '', 'sizes' => '100vw' ] );
		echo str_replace( '<picture>', '<picture class="home-video__poster">', (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture().
		?>
		<?php if ( '' !== $rts_video ) { ?>
			<video class="home-video__video js-bg-video" muted loop playsinline preload="none" aria-hidden="true"
				data-src="<?php echo esc_url( $rts_video ); ?>"
				<?php if ( '' !== $rts_video_mob ) { ?>data-src-mob="<?php echo esc_url( $rts_video_mob ); ?>"<?php } ?>></video>
		<?php } ?>
	</div>

	<?php if ( '' !== $rts_full ) { ?>
		<button class="home-video__open" type="button"
			data-video-open="<?php echo esc_url( $rts_full ); ?>"
			data-video-poster="<?php echo esc_url( rts_image_url( $args['poster'] ) ); ?>">
			<span class="home-video__play"><?php rts_icon( 'icons/play.svg' ); ?></span>
			<span class="home-video__label"><?php rts_icon( 'icons/chevron.svg' ); ?><?php echo esc_html( (string) ( $args['label'] ?? '' ) ); ?></span>
		</button>
	<?php } ?>
</section>
