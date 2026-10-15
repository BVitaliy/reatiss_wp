<?php
/**
 * Builder “Add block” menu: hovering a block name shows a screenshot of how
 * that block looks on the site. Screenshots: img/admin/blocks/{layout}.webp.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin_Block_Previews {

	private const DIR = 'img/admin/blocks';

	public function __construct() {
		add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, array_merge( [ 'page' ], RTS_BUILDER_TYPES ), true ) ) {
			return;
		}

		$previews = [];

		foreach ( glob( RTS_DIR . '/' . self::DIR . '/*.webp' ) ?: [] as $file ) {
			$layout              = str_replace( '-', '_', basename( $file, '.webp' ) );
			$previews[ $layout ] = rts_asset( self::DIR . '/' . basename( $file ) ) . '?ver=' . filemtime( $file );
		}

		if ( empty( $previews ) ) {
			return;
		}

		wp_enqueue_script( 'rts-admin-block-previews', rts_asset( 'js/admin-block-previews.js' ), [ 'acf-input' ], rts_asset_ver( 'js/admin-block-previews.js' ), true );
		wp_localize_script( 'rts-admin-block-previews', 'rtsBlockPreviews', $previews );
	}
}

new RTS_Admin_Block_Previews();
