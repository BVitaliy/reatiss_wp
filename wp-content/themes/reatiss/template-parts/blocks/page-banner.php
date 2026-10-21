<?php
/**
 * Page banner — Figma 8271:41293. Universal dark top block for inner pages:
 * breadcrumbs on top, title at the bottom, photo (or video loop over it) +
 * editable shade; drifts on scroll (app-parallax.js), video: app-video.js.
 * The header stays transparent over it (RTS_DARK_TOP_LAYOUTS).
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
$rts_overlay   = isset( $args['overlay'] ) && '' !== $args['overlay'] ? max( 0, min( 90, (int) $args['overlay'] ) ) / 100 : 0.3;
$rts_title     = ! empty( $args['title'] ) ? (string) $args['title'] : esc_html( get_the_title() );
$rts_title_w   = ! empty( $args['title_width'] ) ? (float) $args['title_width'] : 0;
$rts_is_first  = 0 === (int) ( $args['index'] ?? 0 );

ob_start();
rts_picture(
	$args['image'] ?? 0,
	$args['image_mobile'] ?? 0,
	$rts_is_first ? [
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'sizes'         => '100vw',
	] : [ 'sizes' => '100vw' ]
);
$rts_picture = (string) ob_get_clean();
?>
<section class="page-banner<?php echo '' !== $rts_video ? ' page-banner--video js-bg-video-section' : ''; ?>">
	<div class="page-banner__media" data-parallax-media data-parallax-intensity="160">
		<?php if ( '' !== $rts_video ) { ?>
			<div class="page-banner__layer" data-parallax-target>
				<?php echo $rts_picture; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture(). ?>
				<video class="page-banner__video js-bg-video" muted loop playsinline preload="none" aria-hidden="true"
					data-src="<?php echo esc_url( $rts_video ); ?>"
					<?php if ( '' !== $rts_video_mob ) { ?>data-src-mob="<?php echo esc_url( $rts_video_mob ); ?>"<?php } ?>></video>
			</div>
		<?php } else { ?>
			<?php echo str_replace( '<picture>', '<picture data-parallax-target>', $rts_picture ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture(). ?>
		<?php } ?>
	</div>
	<div class="page-banner__shade" style="opacity: <?php echo esc_attr( (string) $rts_overlay ); ?>" aria-hidden="true"></div>

	<div class="container page-banner__inner">
		<?php
		if ( $rts_is_first ) {
			rts_breadcrumbs_nav( rts_breadcrumbs(), 'breadcrumbs--light' );
		}
		?>
		<?php rts_title( $args, 'h1', 'page-banner__title h1 h1--sm', $rts_title_w ? '--title-w: ' . $rts_title_w . 'em' : '', $rts_title ); ?>
	</div>
</section>
