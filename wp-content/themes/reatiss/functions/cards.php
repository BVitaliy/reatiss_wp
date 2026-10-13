<?php
/**
 * Card lists for the blocks: picked posts in the editor's order, or all /
 * latest ones — always in one query with only the caches the cards use.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts for a card block.
 *
 * @param string $post_type Post type.
 * @param mixed  $picked    Relationship value (IDs); empty = automatic.
 * @param int    $limit     Max items when automatic (-1 = all).
 * @return WP_Post[]
 */
function rts_card_posts( string $post_type, $picked, int $limit = -1 ): array {
	$args = [
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	];

	if ( ! empty( $picked ) && is_array( $picked ) ) {
		$args['post__in']       = array_map( 'intval', $picked );
		$args['orderby']        = 'post__in';
		$args['posts_per_page'] = count( $picked );
	} elseif ( 'post' === $post_type ) {
		$args['posts_per_page'] = $limit;
	} elseif ( 'rts_event' === $post_type ) {
		// Upcoming first (by start date); past events only fill the gap.
		$args['posts_per_page'] = $limit;
		$args['meta_key']       = 'date_start'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['orderby']        = 'meta_value';
		$args['order']          = 'ASC';
		$args['meta_query']     = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			[
				'key'     => 'date_start',
				'value'   => wp_date( 'Ymd' ),
				'compare' => '>=',
			],
		];

		$posts = get_posts( $args );

		if ( $limit > 0 && count( $posts ) < $limit ) {
			$args['posts_per_page'] = $limit - count( $posts );
			$args['order']          = 'DESC';
			$args['meta_query'][0]['compare'] = '<';
			$posts = array_merge( $posts, get_posts( $args ) );
		}

		rts_prime_thumbnails( $posts );

		return $posts;
	} else {
		$args['posts_per_page'] = $limit;
		$args['orderby']        = [
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		];
	}

	$posts = get_posts( $args );
	rts_prime_thumbnails( $posts );

	return $posts;
}

/**
 * Loads the featured images of a card list in one query.
 *
 * @param WP_Post[] $posts Posts.
 */
function rts_prime_thumbnails( array $posts ): void {
	$ids = [];

	foreach ( $posts as $post ) {
		$thumb = (int) get_post_thumbnail_id( $post );

		if ( $thumb ) {
			$ids[] = $thumb;
		}
	}

	rts_prime_posts( $ids );
}

/**
 * Event dates as the design writes them: 22-24.09.26, 30.09-02.10.26, 22.09.26.
 *
 * @param int $post_id Event ID.
 */
function rts_event_dates( int $post_id ): string {
	$start = DateTime::createFromFormat( 'Ymd', (string) get_post_meta( $post_id, 'date_start', true ) );
	$end   = DateTime::createFromFormat( 'Ymd', (string) get_post_meta( $post_id, 'date_end', true ) );

	if ( ! $start ) {
		return '';
	}

	if ( ! $end || $end <= $start ) {
		return $start->format( 'd.m.y' );
	}

	if ( $start->format( 'Y' ) !== $end->format( 'Y' ) ) {
		return $start->format( 'd.m.y' ) . '-' . $end->format( 'd.m.y' );
	}

	if ( $start->format( 'm' ) !== $end->format( 'm' ) ) {
		return $start->format( 'd.m' ) . '-' . $end->format( 'd.m.y' );
	}

	return $start->format( 'd' ) . '-' . $end->format( 'd.m.y' );
}
