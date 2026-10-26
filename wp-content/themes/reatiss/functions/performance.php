<?php
/**
 * Database query reduction (team checklist items 21 and 48). No page caching:
 * everything here removes queries rather than storing rendered output.
 *
 * - Site Settings (ACF options) are autoloaded, so the whole options page
 *   arrives with WordPress' single `alloptions` query instead of one query per
 *   sub-field.
 * - Attachments used on a page are primed in bulk (posts + meta in two
 *   queries) instead of one query pair per image.
 * - Page-template URLs (and the section list pages — the page whose “List
 *   page” block shows services / labs / expertise) are kept in one autoloaded
 *   option, rebuilt when a page is saved, instead of a meta query per lookup.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Performance {

	/**
	 * Autoloaded map: page template => page ID, plus 'list:{post type}' =>
	 * the page listing that type (v2 added the list pages).
	 */
	public const TEMPLATE_PAGES = 'rts_template_pages_v2';

	/**
	 * Marks the one-time switch of existing ACF options to autoload.
	 */
	private const AUTOLOAD_DONE = 'rts_acf_autoload_v1';

	public function __construct() {
		// New and re-saved options are written with autoload on…
		add_filter( 'acf/settings/autoload', '__return_true' );
		// …and the ones saved before this existed are switched once.
		add_action( 'admin_init', [ $this, 'autoload_existing_options' ] );

		add_action( 'save_post_page', [ $this, 'rebuild_template_pages' ] );
		// ACF writes the builder rows after save_post_page — rebuild once more.
		add_action( 'acf/save_post', [ $this, 'rebuild_after_fields' ], 20 );
		add_action( 'deleted_post', [ $this, 'rebuild_template_pages' ] );
		add_action( 'trashed_post', [ $this, 'rebuild_template_pages' ] );
	}

	public function autoload_existing_options(): void {
		if ( get_option( self::AUTOLOAD_DONE ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"UPDATE {$wpdb->options} SET autoload = 'on'
			WHERE option_name LIKE 'options\\_%' OR option_name LIKE '\\_options\\_%'"
		);

		wp_cache_delete( 'alloptions', 'options' );
		update_option( self::AUTOLOAD_DONE, 1, true );
	}

	/**
	 * @param int|string $post_id Saved post (or 'options').
	 */
	public function rebuild_after_fields( $post_id ): void {
		if ( is_numeric( $post_id ) && 'page' === get_post_type( (int) $post_id ) ) {
			$this->rebuild_template_pages();
		}
	}

	/**
	 * Map of every page template in use to its first published page, and of
	 * each section post type to the first published page whose “List page”
	 * block (page_builder_N_type) lists it.
	 */
	public function rebuild_template_pages(): array {
		global $wpdb;

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT pm.meta_value AS template, MIN(p.ID) AS id
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '_wp_page_template' AND p.post_type = 'page' AND p.post_status = 'publish'
			GROUP BY pm.meta_value",
			ARRAY_A
		);

		$map = [];
		foreach ( $rows as $row ) {
			$map[ $row['template'] ] = (int) $row['id'];
		}

		$lists = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT pm.meta_value AS type, MIN(p.ID) AS id
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key REGEXP '^page_builder_[0-9]+_type$'
				AND pm.meta_value IN ('rts_service', 'rts_lab', 'rts_expertise')
				AND p.post_type = 'page' AND p.post_status = 'publish'
			GROUP BY pm.meta_value",
			ARRAY_A
		);

		foreach ( $lists as $row ) {
			$map[ 'list:' . $row['type'] ] = (int) $row['id'];
		}

		update_option( self::TEMPLATE_PAGES, $map, true );

		return $map;
	}
}

$GLOBALS['rts_performance'] = new RTS_Performance();

/**
 * Page ID that uses a template, from the autoloaded map.
 *
 * @param string $template Template file, e.g. 'templates/visual.php'.
 */
function rts_template_page_id( string $template ): int {
	$map = get_option( RTS_Performance::TEMPLATE_PAGES );

	if ( ! is_array( $map ) ) {
		$map = $GLOBALS['rts_performance']->rebuild_template_pages();
	}

	return (int) ( $map[ $template ] ?? 0 );
}

/**
 * Loads posts and their meta for many IDs at once (2 queries), so later
 * get_post() / get_post_meta() / wp_get_attachment_image() calls hit memory.
 *
 * @param int[] $ids Post / attachment IDs.
 */
function rts_prime_posts( array $ids ): void {
	$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );

	if ( $ids ) {
		_prime_post_caches( $ids, false, true );
	}
}

/**
 * Every ID-looking value stored in a post's meta — image, gallery, file and
 * post-object fields — so a builder page's attachments can be primed in one go.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function rts_meta_ids( int $post_id ): array {
	$ids = [];

	foreach ( (array) get_post_meta( $post_id ) as $key => $values ) {
		if ( str_starts_with( (string) $key, '_' ) ) {
			continue; // ACF field-key references.
		}

		foreach ( $values as $value ) {
			if ( ctype_digit( (string) $value ) ) {
				$ids[] = (int) $value;
			} elseif ( is_serialized( $value ) ) {
				$list = maybe_unserialize( $value );

				if ( is_array( $list ) ) {
					foreach ( $list as $item ) {
						if ( is_scalar( $item ) && ctype_digit( (string) $item ) ) {
							$ids[] = (int) $item;
						}
					}
				}
			}
		}
	}

	return $ids;
}
