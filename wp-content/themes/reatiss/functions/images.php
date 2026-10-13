<?php
/**
 * Image output helpers.
 *
 * The markup's <picture> has a desktop and a mobile slot. In WordPress the
 * generated sizes are already WebP (functions/media.php), so the picture keeps
 * only the mobile <source> (optional crop) and an <img> with srcset.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attachment ID from an ACF image value (array or ID).
 *
 * @param mixed $image ACF image array or attachment ID.
 */
function rts_image_id( $image ): int {
	return is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
}

/**
 * <img> for an ACF image — srcset, intrinsic size, lazy by default.
 *
 * @param mixed  $image ACF image array or attachment ID.
 * @param array  $attrs Extra attributes: class, alt, loading, sizes…
 * @param string $size  Registered size.
 */
function rts_image( $image, array $attrs = [], string $size = 'full' ): void {
	$id = rts_image_id( $image );

	if ( ! $id ) {
		return;
	}

	echo wp_get_attachment_image(
		$id,
		$size,
		false,
		array_merge(
			[
				'loading'  => 'lazy',
				'decoding' => 'async',
			],
			$attrs
		)
	);
}

/**
 * URL of an ACF image in a given size.
 *
 * @param mixed  $image ACF image array or attachment ID.
 * @param string $size  Registered size.
 */
function rts_image_url( $image, string $size = 'full' ): string {
	$id = rts_image_id( $image );

	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/**
 * The markup's <picture>: optional mobile crop (≤767px) + the main image.
 *
 * @param mixed  $image  Main image (ACF array or ID).
 * @param mixed  $mobile Mobile crop; falls back to the main image.
 * @param array  $attrs  Attributes for the <img> (class, alt, loading, fetchpriority…).
 * @param string $size   Registered size for the main image.
 */
function rts_picture( $image, $mobile = null, array $attrs = [], string $size = 'full' ): void {
	if ( ! rts_image_id( $image ) ) {
		return;
	}

	$mobile_url = rts_image_url( $mobile, 'large' );
	?>
	<picture>
		<?php if ( '' !== $mobile_url ) { ?>
			<source srcset="<?php echo esc_url( $mobile_url ); ?>" media="(max-width:767px)">
		<?php } ?>
		<?php rts_image( $image, $attrs, $size ); ?>
	</picture>
	<?php
}
