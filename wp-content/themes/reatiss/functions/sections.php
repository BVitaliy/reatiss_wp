<?php
/**
 * Section pages: the list page each post type belongs to — found by itself:
 * services / labs / expertise = the page whose “List page” block shows them,
 * events = the page on the “Events” template, posts = the posts page.
 * Used by the breadcrumbs, the menu's active item and the "All events" link.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that have a section list page.
 */
const RTS_SECTION_TYPES = [ 'rts_service', 'rts_lab', 'rts_expertise', 'rts_event' ];

/**
 * List page of a post type (the blog page for posts).
 *
 * @param string $post_type Post type.
 */
function rts_section_page_id( string $post_type ): int {
	if ( 'post' === $post_type ) {
		return (int) get_option( 'page_for_posts' );
	}

	if ( 'rts_event' === $post_type ) {
		return rts_template_page_id( 'templates/events.php' );
	}

	return in_array( $post_type, RTS_SECTION_TYPES, true ) ? rts_template_page_id( 'list:' . $post_type ) : 0;
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
		if ( $current || ! is_singular( RTS_SECTION_TYPES ) || 'page' !== $item->object ) {
			return $current;
		}

		return (int) $item->object_id === rts_section_page_id( (string) get_post_type() );
	},
	10,
	2
);
