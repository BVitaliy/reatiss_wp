<?php
/**
 * All-in-One WP Migration: keep build-only files out of the .wpress export.
 *
 * node_modules (≈120 MB) is needed only to compile SCSS locally; the site runs
 * on the compiled css/ and js/ that ship with the theme.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Migration {

	public function __construct() {
		add_filter( 'ai1wm_exclude_themes_from_export', [ $this, 'exclude_build_files' ] );
	}

	/**
	 * @param array $filters Absolute paths excluded from the themes export.
	 */
	public function exclude_build_files( $filters ): array {
		$filters   = is_array( $filters ) ? $filters : [];
		$filters[] = RTS_DIR . DIRECTORY_SEPARATOR . 'node_modules';

		return $filters;
	}
}

new RTS_Migration();
