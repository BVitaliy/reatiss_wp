<?php
/**
 * Custom post types and taxonomies — all registered here, in one place.
 *
 * Services, labs and expertise have single pages built with the page builder
 * and card fields for the lists. Their list pages are ordinary pages (chosen in
 * Site Settings → Сторінки розділів), so the CPTs have no archives. Reports
 * (On Shelf) and team members have no pages of their own.
 *
 * Order in lists = «Порядок» (menu_order) in the item's «Атрибути».
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
				'labels'            => $this->taxonomy_labels( 'Категорії подій', 'Категорія' ),
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
			[ 'Послуги', 'Послуга', 'Додати послугу' ],
			'dashicons-hammer',
			21
		);

		$this->register_type(
			'rts_lab',
			'labs',
			[ 'Лабораторії', 'Лабораторія', 'Додати лабораторію' ],
			'dashicons-building',
			22
		);

		$this->register_type(
			'rts_expertise',
			'expertise',
			[ 'Експертизи', 'Експертиза', 'Додати експертизу' ],
			'dashicons-lightbulb',
			23
		);

		$this->register_type(
			'rts_event',
			'events',
			[ 'Події', 'Подія', 'Додати подію' ],
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
			[ 'Звіти (On Shelf)', 'Звіт', 'Додати звіт' ],
			'dashicons-media-document',
			25,
			[ 'supports' => [ 'title', 'page-attributes' ] ]
		);

		$this->register_type(
			'rts_team',
			'',
			[ 'Команда', 'Член команди', 'Додати члена команди' ],
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
						'edit_item'          => __( 'Редагувати', 'reatiss' ) . ': ' . mb_strtolower( $names[1] ),
						'new_item'           => $names[2],
						'view_item'          => __( 'Переглянути', 'reatiss' ),
						'search_items'       => __( 'Шукати', 'reatiss' ),
						'not_found'          => __( 'Нічого не знайдено', 'reatiss' ),
						'not_found_in_trash' => __( 'У кошику нічого немає', 'reatiss' ),
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
			'add_new_item'  => __( 'Додати', 'reatiss' ),
			'edit_item'     => __( 'Редагувати', 'reatiss' ),
			'search_items'  => __( 'Шукати', 'reatiss' ),
			'not_found'     => __( 'Нічого не знайдено', 'reatiss' ),
		];
	}

	/**
	 * @param string  $text Placeholder.
	 * @param WP_Post $post Post.
	 */
	public function title_placeholder( $text, $post ): string {
		$map = [
			'rts_team'   => __( 'Ім’я та прізвище', 'reatiss' ),
			'rts_report' => __( 'Назва звіту', 'reatiss' ),
		];

		return $map[ $post->post_type ] ?? (string) $text;
	}
}

new RTS_Post_Types();
