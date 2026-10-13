<?php
/**
 * «Duplicate» row action (next to Edit / Trash) for pages, posts and the
 * theme's post types: creates a draft copy with the same content, template,
 * fields (builder blocks, cards, SEO), featured image and terms, and opens it
 * for editing. A draft stays off the site until it is published.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin_Duplicate {

	private const ACTION = 'rts_duplicate_post';

	private const POST_TYPES = [ 'page', 'post', 'rts_service', 'rts_lab', 'rts_expertise', 'rts_event', 'rts_report', 'rts_team' ];

	/**
	 * Meta that belongs to the original only.
	 */
	private const SKIP_META = [ '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date', '_wp_trash_meta_status', '_wp_trash_meta_time' ];

	public function __construct() {
		add_filter( 'page_row_actions', [ $this, 'row_action' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'row_action' ], 10, 2 );
		add_action( 'admin_action_' . self::ACTION, [ $this, 'duplicate' ] );
	}

	/**
	 * @param string[] $actions Row actions.
	 * @param WP_Post  $post    Listed post.
	 * @return string[]
	 */
	public function row_action( array $actions, $post ): array {
		if ( ! in_array( $post->post_type, self::POST_TYPES, true ) || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'action' => self::ACTION,
					'post'   => $post->ID,
				],
				admin_url( 'admin.php' )
			),
			self::ACTION . '_' . $post->ID
		);

		$actions['rts_duplicate'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $url ),
			/* translators: %s: post title */
			esc_attr( sprintf( __( 'Duplicate “%s”', 'reatiss' ), get_the_title( $post ) ) ),
			esc_html__( 'Duplicate', 'reatiss' )
		);

		return $actions;
	}

	public function duplicate(): void {
		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;

		check_admin_referer( self::ACTION . '_' . $post_id );

		$post = get_post( $post_id );

		if ( ! $post || ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			wp_die( esc_html__( 'Item not found.', 'reatiss' ) );
		}

		$type = get_post_type_object( $post->post_type );

		if ( ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( $type->cap->create_posts ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'reatiss' ), 403 );
		}

		$copy_id = wp_insert_post(
			wp_slash(
				[
					'post_type'      => $post->post_type,
					'post_status'    => 'draft',
					/* translators: %s: original title */
					'post_title'     => sprintf( __( '%s (copy)', 'reatiss' ), $post->post_title ),
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_parent'    => $post->post_parent,
					'menu_order'     => $post->menu_order,
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_password'  => $post->post_password,
					'post_author'    => get_current_user_id(),
				]
			),
			true
		);

		if ( is_wp_error( $copy_id ) ) {
			wp_die( esc_html( $copy_id->get_error_message() ) );
		}

		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			if ( in_array( $key, self::SKIP_META, true ) ) {
				continue;
			}

			foreach ( $values as $value ) {
				add_post_meta( $copy_id, $key, wp_slash( maybe_unserialize( $value ) ) );
			}
		}

		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );

			if ( ! is_wp_error( $terms ) && $terms ) {
				wp_set_object_terms( $copy_id, $terms, $taxonomy );
			}
		}

		wp_safe_redirect( get_edit_post_link( $copy_id, 'raw' ) );
		exit;
	}
}

new RTS_Admin_Duplicate();
