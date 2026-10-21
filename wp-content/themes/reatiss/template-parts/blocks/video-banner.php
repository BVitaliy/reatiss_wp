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

$rts_poster    = rts_image_id( $args['poster'] ?? 0 );
$rts_video     = (string) ( $args['video'] ?? '' );
$rts_video_mob = (string) ( $args['video_mobile'] ?? '' );
$rts_full      = (string) ( $args['full_video'] ?? '' );

if ( ! $rts_poster && '' === $rts_video && '' === $rts_full ) {
	return;
}
?>
<section class="home-video<?php echo '' !== $rts_video ? ' js-bg-video-section' : ''; ?>" data-parallax-media data-parallax-intensity="160">
	<div class="home-video__media" data-parallax-target>
		<?php
		if ( $rts_poster ) {
			ob_start();
			rts_picture( $rts_poster, $args['poster_mobile'] ?? 0, [ 'alt' => '', 'sizes' => '100vw' ] );
			echo str_replace( '<picture>', '<picture class="home-video__poster">', (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture().
		}
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
			<?php if ( $rts_poster ) { ?>data-video-poster="<?php echo esc_url( rts_image_url( $rts_poster ) ); ?>"<?php } ?>
			aria-label="<?php echo esc_attr( ! empty( $args['label'] ) ? (string) $args['label'] : __( 'Play video', 'reatiss' ) ); ?>">
			<span class="home-video__play"><?php rts_icon( 'icons/play.svg' ); ?></span>
			<?php if ( ! empty( $args['label'] ) ) { ?>
				<span class="home-video__label"><?php rts_icon( 'icons/chevron.svg' ); ?><?php echo esc_html( (string) $args['label'] ); ?></span>
			<?php } ?>
		</button>
	<?php } ?>
</section>
