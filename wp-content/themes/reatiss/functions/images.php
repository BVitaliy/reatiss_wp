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

/**
 * Inline an uploaded SVG (sanitized) so CSS can recolour it via currentColor;
 * raster icons fall back to <img>.
 *
 * @param mixed $image ACF image array or attachment ID.
 * @param array $attrs Attributes for the <img> fallback.
 */
function rts_inline_icon( $image, array $attrs = [] ): void {
	$id = rts_image_id( $image );

	if ( ! $id ) {
		return;
	}

	if ( 'image/svg+xml' === get_post_mime_type( $id ) ) {
		$path = get_attached_file( $id );
		$raw  = $path && is_readable( $path ) ? file_get_contents( $path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local upload.

		if ( false !== $raw ) {
			$clean = ( new RTS_Svg_Sanitizer() )->sanitize( $raw );

			if ( '' !== $clean ) {
				echo preg_replace( '/<svg\b/', '<svg focusable="false" aria-hidden="true"', $clean, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized against an allow-list.

				return;
			}
		}
	}

	rts_image( $id, $attrs, 'thumbnail' );
}
