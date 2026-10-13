<?php
/**
 * Hardening: close the write endpoints this site has no use for.
 *
 * Content is authored in wp-admin only — nothing outside it ever needs to
 * create a user or a post. The REST API stays
 * readable (ACF, the block editor's leftovers and Query Monitor all rely on
 * GET routes), only the insert paths are shut.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Security {

	/**
	 * Post types whose REST insert route is closed.
	 *
	 * `rest_pre_insert_<post_type>` fires per type, so each one this theme
	 * registers needs its own filter — closing `post` alone would leave the
	 * custom types open.
	 */
	private const CLOSED_POST_TYPES = [ 'post', 'page', 'rts_service', 'rts_lab', 'rts_expertise', 'rts_report', 'rts_event', 'rts_team' ];

	public function __construct() {
		add_filter( 'rest_pre_insert_user', [ $this, 'block_user_insert' ], 10, 2 );
		// Core does not check the filter above for a WP_Error and fails later with a
		// misleading 500 — refuse the route up front so the answer is a clean 403.
		add_filter( 'rest_pre_dispatch', [ $this, 'block_user_route' ], 10, 3 );

		foreach ( self::CLOSED_POST_TYPES as $post_type ) {
			add_filter( "rest_pre_insert_{$post_type}", [ $this, 'block_post_insert' ], 10, 2 );
		}

		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', [ $this, 'strip_xmlrpc_methods' ] );

		// Pingback advertising is pointless once XML-RPC is off, and the header
		// is what scanners look for.
		add_filter( 'wp_headers', [ $this, 'strip_pingback_header' ] );
		remove_action( 'wp_head', 'rsd_link' );

		// Author enumeration: /?author=1 redirects to the author archive and
		// leaks the login slug. The archive itself stays reachable by its slug.
		// Priority 0 — redirect_canonical() sits on the same hook at 10 and
		// would perform exactly the redirect this is meant to prevent.
		add_action( 'template_redirect', [ $this, 'block_author_enumeration' ], 0 );
	}

	/**
	 * Registration through the REST API is disabled.
	 *
	 * @param stdClass|WP_Error $prepared_user User object prepared for insertion.
	 * @param WP_REST_Request   $request       Request object.
	 * @return WP_Error
	 */
	public function block_user_insert( $prepared_user, $request ) {
		return new WP_Error(
			'registration_disabled',
			__( 'User registration through the API is disabled.', 'reatiss' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * POST /wp/v2/users → 403 before the controller runs.
	 *
	 * @param mixed           $result  Response to short-circuit with, or null.
	 * @param WP_REST_Server  $server  Server.
	 * @param WP_REST_Request $request Request.
	 */
	public function block_user_route( $result, $server, $request ) {
		if ( null === $result && 'POST' === $request->get_method() && preg_match( '#^/wp/v2/users/?$#', $request->get_route() ) ) {
			return $this->block_user_insert( null, $request );
		}

		return $result;
	}

	/**
	 * Creating and updating content through the REST API is disabled.
	 *
	 * @param stdClass|WP_Error $prepared_post Post object prepared for insertion.
	 * @param WP_REST_Request   $request       Request object.
	 * @return WP_Error
	 */
	public function block_post_insert( $prepared_post, $request ) {
		return new WP_Error(
			'post_creation_disabled',
			__( 'Creating posts through the REST API is disabled.', 'reatiss' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * `xmlrpc_enabled` only covers the authenticated methods — pingback.ping
	 * stays callable without it, which is the half that gets abused.
	 *
	 * @param array $methods Registered XML-RPC methods.
	 */
	public function strip_xmlrpc_methods( array $methods ): array {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

		return $methods;
	}

	/**
	 * @param array $headers Response headers.
	 */
	public function strip_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * Turns /?author=N into a 404 instead of a redirect that reveals the login.
	 */
	public function block_author_enumeration(): void {
		if ( is_admin() || ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only guard on a public URL.
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

new RTS_Security();
