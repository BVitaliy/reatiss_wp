<?php
/**
 * Site search: the results page (search.php) and the live results under the
 * header field (js/app-search.js → wp_ajax_rts_live_search).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the search looks in. Reports and team members have no pages.
 */
const RTS_SEARCH_TYPES = [ 'post', 'page', 'rts_service', 'rts_lab', 'rts_expertise', 'rts_event' ];

/**
 * Results per page / in the header dropdown.
 */
const RTS_SEARCH_PER_PAGE = 10;
const RTS_SEARCH_LIVE     = 6;

class RTS_Search {

	public function __construct() {
		add_action( 'pre_get_posts', [ $this, 'main_query' ] );
		add_action( 'wp_ajax_rts_live_search', [ $this, 'live' ] );
		add_action( 'wp_ajax_nopriv_rts_live_search', [ $this, 'live' ] );
	}

	/**
	 * @param WP_Query $query Query.
	 */
	public function main_query( $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}

		$query->set( 'post_type', RTS_SEARCH_TYPES );
		$query->set( 'posts_per_page', RTS_SEARCH_PER_PAGE );
		// Explicit: rspanel would otherwise sort the results by menu_order.
		$query->set( 'orderby', 'relevance' );
		$query->set( 'order', 'DESC' );
	}

	/**
	 * Header dropdown: up to six titles, the total and "View All".
	 */
	public function live(): void {
		check_ajax_referer( 'rts_nonce', 'nonce' );

		$term = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.

		if ( mb_strlen( $term ) < 2 ) {
			wp_send_json_success( [ 'html' => '' ] );
		}

		$query = rts_search_query( $term, 1, RTS_SEARCH_LIVE );

		ob_start();
		get_template_part(
			'template-parts/global/search-live',
			null,
			[
				'query' => $query,
				'term'  => $term,
			]
		);

		wp_send_json_success( [ 'html' => (string) ob_get_clean() ] );
	}
}

new RTS_Search();

/**
 * Search query — the same for the page, "Show more" and the header dropdown.
 *
 * @param string $term     Search term.
 * @param int    $page     Page.
 * @param int    $per_page Results per page.
 */
function rts_search_query( string $term, int $page = 1, int $per_page = RTS_SEARCH_PER_PAGE ): WP_Query {
	return new WP_Query(
		[
			's'                      => $term,
			'post_type'              => RTS_SEARCH_TYPES,
			'post_status'            => 'publish',
			'posts_per_page'         => $per_page,
			'paged'                  => $page,
			'orderby'                => 'relevance',
			'order'                  => 'DESC',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);
}
