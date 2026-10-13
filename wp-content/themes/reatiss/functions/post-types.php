<?php
/**
 * Custom post types and taxonomies — all registered here, in one place.
 *
 * Services, labs and expertise have single pages built with the page builder
 * and card fields for the lists. Their list pages are ordinary pages (chosen in
 * Site Settings → Section pages), so the CPTs have no archives. Reports
 * (On Shelf) and team members have no pages of their own.
 *
 * Order in lists = “Order” (menu_order) in the item's Page Attributes.
 *
 * Labels are English and translatable (text domain `reatiss`).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Post_Types {

	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
		add_filter( 'enter_title_here', [ $this, 'title_placeholder' ], 10, 2 );
	}

	public function register(): void {
		// Taxonomy first: its /events/category/… rules must come before the
		// event post type's /events/… ones, which would otherwise swallow them.
		register_taxonomy(
			'rts_event_cat',
			[ 'rts_event' ],
			[
				'labels'            => $this->taxonomy_labels( __( 'Event categories', 'reatiss' ), __( 'Event category', 'reatiss' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'rewrite'           => [
					'slug'       => 'events/category',
					'with_front' => false,
				],
			]
		);

		$this->register_type(
			'rts_service',
			'services',
			[ __( 'Services', 'reatiss' ), __( 'Service', 'reatiss' ), __( 'Add service', 'reatiss' ) ],
			'dashicons-hammer',
			21
		);

		$this->register_type(
			'rts_lab',
			'labs',
			[ __( 'Labs', 'reatiss' ), __( 'Lab', 'reatiss' ), __( 'Add lab', 'reatiss' ) ],
			'dashicons-building',
			22
		);

		$this->register_type(
			'rts_expertise',
			'expertise',
			[ __( 'Expertise', 'reatiss' ), __( 'Expertise item', 'reatiss' ), __( 'Add expertise', 'reatiss' ) ],
			'dashicons-lightbulb',
			23
		);

		$this->register_type(
			'rts_event',
			'events',
			[ __( 'Events', 'reatiss' ), __( 'Event', 'reatiss' ), __( 'Add event', 'reatiss' ) ],
			'dashicons-calendar-alt',
			24,
			[
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'hierarchical' => false,
			]
		);

		$this->register_type(
			'rts_report',
			'',
			[ __( 'Reports (On Shelf)', 'reatiss' ), __( 'Report', 'reatiss' ), __( 'Add report', 'reatiss' ) ],
			'dashicons-media-document',
			25,
			[ 'supports' => [ 'title', 'page-attributes' ] ]
		);

		$this->register_type(
			'rts_team',
			'',
			[ __( 'Team', 'reatiss' ), __( 'Team member', 'reatiss' ), __( 'Add team member', 'reatiss' ) ],
			'dashicons-groups',
			26,
			[ 'supports' => [ 'title', 'thumbnail', 'page-attributes' ] ]
		);

	}

	/**
	 * @param string   $type  Post type.
	 * @param string   $slug  URL base; empty = no public pages.
	 * @param string[] $names [ plural, singular, add new ].
	 * @param string   $icon  Dashicon.
	 * @param int      $position Admin menu position.
	 * @param array    $args  Overrides.
	 */
	private function register_type( string $type, string $slug, array $names, string $icon, int $position, array $args = [] ): void {
		$public = '' !== $slug;

		register_post_type(
			$type,
			array_merge(
				[
					'labels'              => [
						'name'               => $names[0],
						'singular_name'      => $names[1],
						'menu_name'          => $names[0],
						'add_new'            => $names[2],
						'add_new_item'       => $names[2],
						/* translators: %s: item type */
						'edit_item'          => sprintf( __( 'Edit %s', 'reatiss' ), mb_strtolower( $names[1] ) ),
						'new_item'           => $names[2],
						'view_item'          => __( 'View', 'reatiss' ),
						'search_items'       => __( 'Search', 'reatiss' ),
						'not_found'          => __( 'Nothing found', 'reatiss' ),
						'not_found_in_trash' => __( 'Nothing found in Trash', 'reatiss' ),
						'all_items'          => $names[0],
					],
					'public'              => $public,
					'publicly_queryable'  => $public,
					'exclude_from_search' => ! $public,
					'show_ui'             => true,
					'show_in_nav_menus'   => $public,
					'show_in_rest'        => false,
					'has_archive'         => false,
					'hierarchical'        => false,
					'menu_icon'           => $icon,
					'menu_position'       => $position,
					'supports'            => [ 'title', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ],
					'rewrite'             => $public ? [
						'slug'       => $slug,
						'with_front' => false,
					] : false,
				],
				$args
			)
		);
	}

	private function taxonomy_labels( string $plural, string $singular ): array {
		return [
			'name'          => $plural,
			'singular_name' => $singular,
			'menu_name'     => $plural,
			'all_items'     => $plural,
			'add_new_item'  => __( 'Add new', 'reatiss' ),
			'edit_item'     => __( 'Edit', 'reatiss' ),
			'search_items'  => __( 'Search', 'reatiss' ),
			'not_found'     => __( 'Nothing found', 'reatiss' ),
		];
	}

	/**
	 * @param string  $text Placeholder.
	 * @param WP_Post $post Post.
	 */
	public function title_placeholder( $text, $post ): string {
		$map = [
			'rts_team'   => __( 'Full name', 'reatiss' ),
			'rts_report' => __( 'Report title', 'reatiss' ),
		];

		return $map[ $post->post_type ] ?? (string) $text;
	}
}

new RTS_Post_Types();
