<?php
/**
 * Editor button “Insert CTA box”: a TinyMCE plugin (js/admin-mce-cta.js) on
 * the full toolbar of the classic editor and ACF WYSIWYG fields, so editors
 * insert [rts_cta] from a dialog instead of typing the shortcode.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Admin_Mce_Cta {

	public function __construct() {
		add_filter( 'mce_external_plugins', [ $this, 'plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'button' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'data' ], 1 );
	}

	/**
	 * @param array $plugins TinyMCE external plugins.
	 */
	public function plugin( $plugins ): array {
		$plugins            = is_array( $plugins ) ? $plugins : [];
		$plugins['rts_cta'] = rts_asset( 'js/admin-mce-cta.js' ) . '?ver=' . rts_asset_ver( 'js/admin-mce-cta.js' );

		return $plugins;
	}

	/**
	 * After “Insert link”.
	 *
	 * @param array $buttons First toolbar row.
	 */
	public function button( $buttons ): array {
		$buttons = is_array( $buttons ) ? $buttons : [];
		$at      = array_search( 'link', $buttons, true );

		array_splice( $buttons, false === $at ? count( $buttons ) : $at + 1, 0, [ 'rts_cta' ] );

		return $buttons;
	}

	/**
	 * Pages for the dialog's link list + its texts (before the editors init).
	 */
	public function data(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		$pages = [];
		$posts = get_posts(
			[
				'post_type'              => [ 'page', 'rts_service', 'rts_lab', 'rts_expertise' ],
				'post_status'            => 'publish',
				'numberposts'            => -1,
				'orderby'                => [
					'post_type'  => 'ASC',
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				],
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);

		foreach ( $posts as $post ) {
			$type    = get_post_type_object( $post->post_type );
			$pages[] = [
				'text'  => ( 'page' === $post->post_type ? '' : $type->labels->singular_name . ': ' ) . get_the_title( $post ),
				'value' => (string) get_permalink( $post ),
			];
		}

		$data = [
			'pages' => $pages,
			'i18n'  => [
				'tooltip'  => __( 'Insert a box with a button (CTA)', 'reatiss' ),
				'title'    => __( 'Box with a button', 'reatiss' ),
				'text'     => __( 'Text', 'reatiss' ),
				'button'   => __( 'Button text', 'reatiss' ),
				'page'     => __( 'Link to a page', 'reatiss' ),
				'custom'   => __( '— Custom URL (field below) —', 'reatiss' ),
				'url'      => __( 'Custom URL', 'reatiss' ),
				'urlHint'  => __( 'Any address, a PDF from the media library, or #consultation for the consultation popup.', 'reatiss' ),
				'big'      => __( 'Large text (as in articles)', 'reatiss' ),
				'needText' => __( 'Fill in the text.', 'reatiss' ),
			],
		];

		printf( '<script>window.rtsCta = %s;</script>', wp_json_encode( $data ) );
	}
}

new RTS_Admin_Mce_Cta();
