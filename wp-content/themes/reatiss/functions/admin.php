<?php
/**
 * Admin-side tweaks.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin {

	/**
	 * @var RTS_Svg_Sanitizer
	 */
	private RTS_Svg_Sanitizer $sanitizer;

	public function __construct() {
		$this->sanitizer = new RTS_Svg_Sanitizer();

		add_filter( 'upload_mimes', [ $this, 'allow_svg_upload' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_svg_filetype' ], 10, 4 );
		// Both paths matter: the media modal goes through upload_prefilter, while
		// imports, media_handle_sideload() and some plugins use sideload_prefilter.
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'sanitize_uploaded_svg' ] );
		add_filter( 'wp_handle_sideload_prefilter', [ $this, 'sanitize_uploaded_svg' ] );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'svg_metadata' ], 10, 2 );
		add_action( 'admin_head', [ $this, 'svg_thumbnail_css' ] );

		// 3D models for the “3D model” block.
		add_filter( 'upload_mimes', [ $this, 'allow_glb_upload' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_glb_filetype' ], 10, 4 );
	}

	/**
	 * .glb (glTF binary) uploads for anyone who can upload files.
	 *
	 * @param array $mimes Allowed mime types.
	 */
	public function allow_glb_upload( array $mimes ): array {
		if ( current_user_can( 'upload_files' ) ) {
			$mimes['glb'] = 'model/gltf-binary';
		}

		return $mimes;
	}

	/**
	 * finfo reports .glb as application/octet-stream; trust the extension only
	 * when the file really starts with the glTF magic bytes.
	 *
	 * @param array  $data     Filetype data.
	 * @param string $file     Full path to the file.
	 * @param string $filename File name.
	 * @param array  $mimes    Allowed mime types.
	 */
	public function fix_glb_filetype( $data, $file, $filename, $mimes ): array {
		$data = is_array( $data ) ? $data : [];

		if ( ! empty( $data['ext'] ) || 'glb' !== strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) ) ) {
			return $data;
		}

		$handle = is_readable( $file ) ? fopen( $file, 'rb' ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- read 4 bytes of the upload.
		$magic  = $handle ? fread( $handle, 4 ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread

		if ( $handle ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}

		if ( 'glTF' === $magic && ! empty( $mimes['glb'] ) ) {
			$data['ext']  = 'glb';
			$data['type'] = 'model/gltf-binary';
		}

		return $data;
	}

	/**
	 * SVG uploads for anyone who can upload files.
	 *
	 * Editors need this to manage icons, so the capability gate is `upload_files`
	 * rather than `unfiltered_html`. What makes that safe is
	 * sanitize_uploaded_svg() below: every file is stripped to an allow-list
	 * before it ever lands in uploads/.
	 *
	 * @param array $mimes Allowed mime types.
	 */
	public function allow_svg_upload( array $mimes ): array {
		if ( ! current_user_can( 'upload_files' ) ) {
			return $mimes;
		}

		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';

		return $mimes;
	}

	/**
	 * WordPress' real-mime sniffing rejects SVG without this correction.
	 *
	 * @param array  $data     File data.
	 * @param string $file     Full path to the file.
	 * @param string $filename The name of the file.
	 * @param array  $mimes    Allowed mimes.
	 */
	public function fix_svg_filetype( array $data, $file, $filename, $mimes ): array {
		if ( ! current_user_can( 'upload_files' ) ) {
			return $data;
		}

		if ( str_ends_with( strtolower( (string) $filename ), '.svg' ) ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	}

	/**
	 * Cleans an SVG before it is stored, and rejects the upload when the file
	 * cannot be made safe.
	 *
	 * Runs on the temp file, so nothing hostile is ever written to uploads/.
	 *
	 * @param array $file $_FILES entry.
	 */
	public function sanitize_uploaded_svg( array $file ): array {
		if ( empty( $file['tmp_name'] ) || empty( $file['name'] ) ) {
			return $file;
		}

		if ( ! str_ends_with( strtolower( $file['name'] ), '.svg' ) ) {
			return $file;
		}

		// An unreadable temp file is not our problem to report — WordPress raises
		// its own upload error for that, and claiming "invalid SVG" here would
		// point the editor at the wrong thing.
		if ( ! is_readable( $file['tmp_name'] ) ) {
			return $file;
		}

		if ( ! $this->sanitizer->sanitize_file( $file['tmp_name'] ) ) {
			$file['error'] = __( 'This SVG could not be processed: it contains disallowed elements or is damaged. Export it without scripts and try again.', 'reatiss' );
		}

		return $file;
	}

	/**
	 * Gives SVG attachments width/height metadata.
	 *
	 * Without it WordPress reports the attachment as 0×0 and ACF's preview plus
	 * any wp_get_attachment_image() call renders a collapsed box.
	 *
	 * @param array $metadata      Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 */
	public function svg_metadata( $metadata, $attachment_id ) {
		if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );

		if ( ! $file || ! is_readable( $file ) ) {
			return $metadata;
		}

		$size = $this->svg_dimensions( $file );

		if ( ! $size ) {
			return $metadata;
		}

		if ( ! is_array( $metadata ) ) {
			$metadata = [];
		}

		$metadata['width']  = $size['width'];
		$metadata['height'] = $size['height'];
		$metadata['file']   = _wp_relative_upload_path( $file );
		$metadata['sizes']  = [];

		return $metadata;
	}

	/**
	 * Reads intrinsic size from width/height, falling back to viewBox.
	 *
	 * @param string $path Absolute path to the SVG.
	 * @return array{width:int,height:int}|null
	 */
	private function svg_dimensions( string $path ): ?array {
		$previous = libxml_use_internal_errors( true );

		$svg = simplexml_load_file( $path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOENT );

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( false === $svg ) {
			return null;
		}

		$attributes = $svg->attributes();

		if ( ! empty( $attributes->width ) && ! empty( $attributes->height ) ) {
			$width  = (int) round( (float) $attributes->width );
			$height = (int) round( (float) $attributes->height );

			if ( $width > 0 && $height > 0 ) {
				return [ 'width' => $width, 'height' => $height ];
			}
		}

		if ( ! empty( $attributes->viewBox ) ) {
			$box = preg_split( '/[\s,]+/', trim( (string) $attributes->viewBox ) );

			if ( is_array( $box ) && 4 === count( $box ) ) {
				$width  = (int) round( (float) $box[2] );
				$height = (int) round( (float) $box[3] );

				if ( $width > 0 && $height > 0 ) {
					return [ 'width' => $width, 'height' => $height ];
				}
			}
		}

		return null;
	}

	/**
	 * Without a width, SVGs render as a sliver in the media grid.
	 */
	public function svg_thumbnail_css(): void {
		echo '<style>.attachment-266x266, .thumbnail img[src$=".svg"] { width: 100% !important; height: auto !important; }</style>';
	}
}

new RTS_Admin();
