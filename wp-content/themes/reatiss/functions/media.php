<?php
/**
 * Media pipeline: WebP for every generated image size.
 *
 * The theme renders photos exclusively through named sizes
 * (wp_get_attachment_image() / get_the_post_thumbnail() with a `rts-*` crop),
 * never the raw upload — so switching the *generated* sizes to WebP delivers
 * WebP site-wide without a single <picture> element or a rewrite rule.
 *
 * The upload itself is kept: WordPress files the JPEG/PNG the editor dropped in
 * under `original_image` in the attachment metadata and serves WebP for every
 * size, so re-crops still work from the untouched source and nothing is lost if
 * the site ever moves to a host without WebP support.
 *
 * Verified locally: a 1200×900 JPEG upload produces .webp for all 18 registered
 * sizes plus the "full" size, alongside the preserved .jpg original.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Media {

	/**
	 * Source mime types that are re-encoded as WebP.
	 *
	 * GIF is deliberately absent — the editor would flatten an animation to a
	 * single frame. SVG never reaches the image editor at all.
	 */
	private const CONVERT = [ 'image/jpeg', 'image/png' ];

	/**
	 * Encoder quality. 82 keeps photos visually identical at a fraction of the
	 * size of 100. RS Resizer's own quality setting (Settings → RS Resizer)
	 * filters `wp_editor_set_quality` at priority 100 — leave it empty.
	 */
	private const QUALITY = 82;

	public function __construct() {
		add_filter( 'image_editor_output_format', [ $this, 'output_webp' ], 10, 3 );
		add_filter( 'wp_editor_set_quality', [ $this, 'webp_quality' ], 10, 2 );
	}

	/**
	 * Re-encodes generated sub-sizes as WebP.
	 *
	 * @param array  $formats   Map of source mime type => output mime type.
	 * @param string $filename  Path to the file being resized.
	 * @param string $mime_type Source mime type.
	 */
	public function output_webp( $formats, $filename, $mime_type ): array {
		if ( ! is_array( $formats ) ) {
			$formats = [];
		}

		if ( ! in_array( $mime_type, self::CONVERT, true ) ) {
			return $formats;
		}

		// A host without WebP support would fail every resize and leave the
		// attachment with no sizes at all, so fall back to the source format.
		if ( ! wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ) {
			return $formats;
		}

		$formats[ $mime_type ] = 'image/webp';

		return $formats;
	}

	/**
	 * @param int    $quality   Compression quality, 1-100.
	 * @param string $mime_type Output mime type.
	 */
	public function webp_quality( $quality, $mime_type ): int {
		return 'image/webp' === $mime_type ? self::QUALITY : (int) $quality;
	}
}

new RTS_Media();
