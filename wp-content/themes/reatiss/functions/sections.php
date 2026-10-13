<?php
/**
 * Section pages: the list page each post type belongs to (Site Settings →
 * Section pages). Used by the breadcrumbs, the menu's active item and the
 * blocks' default "View all" links.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post type => Site Settings field holding its list page.
 */
const RTS_SECTION_OPTIONS = [
	'rts_service'   => 'services_page',
	'rts_lab'       => 'labs_page',
	'rts_expertise' => 'expertise_page',
	'rts_event'     => 'events_page',
];

/**
 * List page of a post type (the blog page for posts).
 *
 * @param string $post_type Post type.
 */
function rts_section_page_id( string $post_type ): int {
	if ( 'post' === $post_type ) {
		return (int) get_option( 'page_for_posts' );
	}

	return isset( RTS_SECTION_OPTIONS[ $post_type ] ) ? (int) rts_option( RTS_SECTION_OPTIONS[ $post_type ] ) : 0;
}

add_filter(
	'rts_breadcrumb_section',
	static function ( $section, $post_id ) {
		return $section ? $section : rts_section_page_id( (string) get_post_type( $post_id ) );
	},
	10,
	2
);

// A single service / lab / expertise / event lights up its list page in the menu.
add_filter(
	'rts_nav_item_current',
	static function ( $current, $item ) {
		if ( $current || ! is_singular( array_keys( RTS_SECTION_OPTIONS ) ) || 'page' !== $item->object ) {
			return $current;
		}

		return (int) $item->object_id === rts_section_page_id( (string) get_post_type() );
	},
	10,
	2
);
