<?php
/**
 * Gallery strip — Figma 8271:41552. Full-width Swiper (autoplay, loop, drag;
 * app-gallery.js); a click opens lightGallery over the original images.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_images = is_array( $args['images'] ?? null ) ? $args['images'] : [];
$rts_title  = (string) ( $args['title'] ?? '' );

if ( '' === $rts_title && empty( $rts_images ) ) {
	return;
}
?>
<section class="gallery-strip">
	<?php if ( '' !== $rts_title ) { ?>
		<div class="container">
			<?php rts_title( $args, 'h2', 'gallery-strip__title h3' ); ?>
		</div>
	<?php } ?>

	<?php if ( ! empty( $rts_images ) ) { ?>
	<div class="gallery-strip__slider swiper js-gallery-strip" aria-label="<?php echo esc_attr( $rts_title ); ?>">
		<div class="swiper-wrapper">
			<?php foreach ( $rts_images as $rts_n => $rts_image ) { ?>
				<a class="swiper-slide gallery-strip__slide" href="<?php echo esc_url( rts_image_url( $rts_image ) ); ?>"<?php echo rts_aria_label( sprintf( /* translators: 1: photo number, 2: count */ __( 'Open photo %1$d of %2$d', 'reatiss' ), $rts_n + 1, count( $rts_images ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
					<?php rts_picture( $rts_image, null, [ 'sizes' => '(max-width: 767px) 78vw, 38vw' ], 'large' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</section>
