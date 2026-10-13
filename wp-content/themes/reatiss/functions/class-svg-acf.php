<?php
/**
 * Lets specific ACF image fields accept SVG.
 *
 * ACF 6.8.7 hardened image and gallery fields: an upload must pass
 * wp_get_image_mime() and an existing attachment must pass
 * wp_attachment_is_image(). Both read raster headers, so an SVG — being XML —
 * can never satisfy them, and the editor just sees "File must be a valid image."
 *
 * Rather than switching that protection off site-wide, the exemption is scoped
 * to the field names that are meant to hold icons, and only for files this
 * theme's sanitizer can actually parse. Banner and photo fields keep the strict
 * raster check.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Svg_Acf {

	/**
	 * ACF field names allowed to hold an SVG.
	 */
	private const SVG_FIELDS = [ 'icon', 'logo', 'custom_icon', 'overlay_svg', 'zone_svg' ];

	/**
	 * @var RTS_Svg_Sanitizer
	 */
	private RTS_Svg_Sanitizer $sanitizer;

	public function __construct() {
		$this->sanitizer = new RTS_Svg_Sanitizer();

		foreach ( self::SVG_FIELDS as $name ) {
			add_filter( "acf/validate_is_image_attachment/name={$name}", [ $this, 'allow_svg_attachment' ], 20, 5 );
			add_filter( "acf/validate_value/name={$name}", [ $this, 'allow_svg_value' ], 20, 4 );
		}
	}

	/**
	 * Drops ACF's "not an image" error when the file is an SVG we can sanitize.
	 *
	 * @param array  $errors     Collected errors.
	 * @param array  $file       Normalized file data.
	 * @param array  $attachment Attachment data; shape depends on context.
	 * @param array  $field      Field array.
	 * @param string $context    'upload', 'prepare' or 'basic_upload'.
	 */
	public function allow_svg_attachment( $errors, $file, $attachment, $field, $context ) {
		if ( ! is_array( $errors ) || empty( $errors['invalid_image'] ) ) {
			return $errors;
		}

		if ( ! $this->is_svg( $attachment, $file ) ) {
			return $errors;
		}

		// An SVG we cannot parse stays rejected — but say why, instead of
		// claiming it is not an image.
		$path = $this->local_path( $attachment );

		if ( $path && '' === $this->sanitizer->sanitize( (string) file_get_contents( $path ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
			$errors['invalid_image'] = __( 'This SVG could not be processed: it contains disallowed elements or is damaged.', 'reatiss' );

			return $errors;
		}

		unset( $errors['invalid_image'] );

		return $errors;
	}

	/**
	 * Same exemption for the save-time check, which runs wp_attachment_is_image()
	 * on the chosen attachment id.
	 *
	 * @param mixed  $valid Current validity — true, or an error string.
	 * @param mixed  $value Field value (attachment id).
	 * @param array  $field Field array.
	 * @param string $input Input name.
	 */
	public function allow_svg_value( $valid, $value, $field, $input ) {
		if ( true === $valid || empty( $value ) || ! is_numeric( $value ) ) {
			return $valid;
		}

		if ( 'image/svg+xml' === get_post_mime_type( (int) $value ) ) {
			return true;
		}

		return $valid;
	}

	/**
	 * Is the file under validation an SVG?
	 *
	 * @param array $attachment Attachment data.
	 * @param array $file       Normalized file data.
	 */
	private function is_svg( $attachment, $file ): bool {
		$attachment = is_array( $attachment ) ? $attachment : [];
		$file       = is_array( $file ) ? $file : [];

		$mimes = [
			$attachment['mime'] ?? '',
			$attachment['type'] ?? '',
			$file['type'] ?? '',
		];

		if ( ! empty( $attachment['id'] ) ) {
			$mimes[] = (string) get_post_mime_type( (int) $attachment['id'] );
		}

		foreach ( $mimes as $mime ) {
			if ( 'image/svg+xml' === $mime ) {
				return true;
			}
		}

		$names = [ $attachment['name'] ?? '', $file['name'] ?? '' ];

		foreach ( $names as $name ) {
			if ( '' !== $name && str_ends_with( strtolower( (string) $name ), '.svg' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Readable path for the file being validated, if there is one.
	 *
	 * @param array $attachment Attachment data.
	 */
	private function local_path( $attachment ): string {
		$attachment = is_array( $attachment ) ? $attachment : [];

		if ( ! empty( $attachment['tmp_name'] ) && is_readable( $attachment['tmp_name'] ) ) {
			return (string) $attachment['tmp_name'];
		}

		if ( ! empty( $attachment['id'] ) ) {
			$path = get_attached_file( (int) $attachment['id'] );

			if ( $path && is_readable( $path ) ) {
				return (string) $path;
			}
		}

		return '';
	}
}

new RTS_Svg_Acf();
