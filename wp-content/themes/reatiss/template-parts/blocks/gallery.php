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

if ( empty( $rts_images ) ) {
	return;
}

$rts_title = (string) ( $args['title'] ?? '' );
?>
<section class="gallery-strip">
	<?php if ( '' !== $rts_title ) { ?>
		<div class="container">
			<h2 class="gallery-strip__title h3"><?php echo esc_html( $rts_title ); ?></h2>
		</div>
	<?php } ?>

	<div class="gallery-strip__slider swiper js-gallery-strip" aria-label="<?php echo esc_attr( $rts_title ); ?>">
		<div class="swiper-wrapper">
			<?php foreach ( $rts_images as $rts_image ) { ?>
				<a class="swiper-slide gallery-strip__slide" href="<?php echo esc_url( rts_image_url( $rts_image ) ); ?>">
					<?php rts_picture( $rts_image, null, [ 'sizes' => '(max-width: 767px) 78vw, 38vw' ], 'large' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
</section>
