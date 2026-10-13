<?php
/**
 * ACF bootstrap — JSON paths and the options page.
 *
 * Field groups are defined in acf-json/ (JSON is the source of truth — never
 * call acf_add_local_field_group()).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'acf/settings/save_json',
	function () {
		return RTS_DIR . '/acf-json';
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		unset( $paths[0] );
		$paths[] = RTS_DIR . '/acf-json';

		return $paths;
	}
);

// Field labels / instructions go through __( …, 'reatiss' ), so a translation
// file can localise the admin per user language.
add_filter(
	'acf/settings/l10n_textdomain',
	function () {
		return 'reatiss';
	}
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			[
				'page_title' => __( 'Site Settings', 'reatiss' ),
				'menu_title' => __( 'Site Settings', 'reatiss' ),
				'menu_slug'  => 'rts-site-settings',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'icon_url'   => 'dashicons-admin-generic',
				'position'   => 59,
			]
		);
	}
);
