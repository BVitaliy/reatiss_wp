<?php
/**
 * Edit screen order (team checklist: tidy admin).
 *
 * - A page that is built with blocks (or the front page) has no use for the
 *   classic editor — it is removed, so the page builder sits right under the
 *   title. Pages without blocks (text pages) keep the editor.
 * - Yoast's box goes below the theme's fields.
 * - Collapsed builder rows show the block's title and a “hidden” mark.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin_Editor {

	public function __construct() {
		add_action( 'load-post.php', [ $this, 'maybe_hide_editor' ] );
		add_filter( 'wpseo_metabox_prio', [ $this, 'yoast_priority' ] );
		add_filter( 'acf/fields/flexible_content/layout_title/name=page_builder', [ $this, 'layout_title' ], 10, 4 );
	}

	/**
	 * “Expertise cards — Deep Technical Expertise (hidden)”.
	 *
	 * @param string $title  Layout label.
	 * @param array  $field  Flexible content field.
	 * @param array  $layout Layout.
	 * @param int    $i      Row index.
	 */
	public function layout_title( $title, $field, $layout, $i ): string {
		$heading = get_sub_field( 'title' );

		if ( empty( $heading ) ) {
			$heading = get_sub_field( 'lead' );
		}

		$title = esc_html( (string) $title );

		if ( ! empty( $heading ) && is_string( $heading ) ) {
			$title .= ' — <span style="font-weight:400">' . esc_html( wp_trim_words( wp_strip_all_tags( $heading ), 8 ) ) . '</span>';
		}

		if ( get_sub_field( $layout['name'] . '_hide' ) ) {
			$title .= ' <em style="color:#b32d2e">(' . esc_html__( 'hidden', 'reatiss' ) . ')</em>';
		}

		return $title;
	}

	public function maybe_hide_editor(): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen setup.

		if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
			return;
		}

		$is_front = (int) get_option( 'page_on_front' ) === $post_id;

		if ( $is_front || ! empty( get_post_meta( $post_id, 'page_builder', true ) ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	public function yoast_priority(): string {
		return 'low';
	}
}

new RTS_Admin_Editor();
