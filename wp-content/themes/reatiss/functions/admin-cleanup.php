<?php
/**
 * Comments are not part of the design: they are switched off everywhere and
 * removed from the admin menu, admin bar and dashboard.
 *
 * Loaded on the front end too — the admin bar is shown there.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin_Cleanup {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'remove_menus' ], 999 );
		add_action( 'admin_bar_menu', [ $this, 'remove_admin_bar_nodes' ], 999 );
		add_action( 'admin_init', [ $this, 'redirect_hidden_screens' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'remove_dashboard_widgets' ] );
		add_action( 'init', [ $this, 'disable_comments_support' ], 100 );

		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );
		add_filter( 'comments_array', '__return_empty_array', 10 );
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
	}

	public function remove_menus(): void {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * @param WP_Admin_Bar $bar Admin bar.
	 */
	public function remove_admin_bar_nodes( $bar ): void {
		$bar->remove_node( 'comments' );
	}

	/**
	 * Comment screens → dashboard.
	 */
	public function redirect_hidden_screens(): void {
		global $pagenow;

		if ( in_array( $pagenow, [ 'edit-comments.php', 'comment.php' ], true ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	public function remove_dashboard_widgets(): void {
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * No post type on this site takes comments or trackbacks.
	 */
	public function disable_comments_support(): void {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}
}

new RTS_Admin_Cleanup();
