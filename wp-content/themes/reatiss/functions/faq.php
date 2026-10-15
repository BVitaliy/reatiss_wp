<?php
/**
 * FAQ questions (CPT rts_faq): the FAQ block picks them, or shows all.
 *
 * Earlier the questions lived in Site Settings → FAQ (shared) and in the
 * blocks themselves (own); RTS_Faq_Migration moves both into rts_faq posts
 * once, so a site imported from an older export upgrades itself.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Questions for a FAQ block.
 *
 * @param int[] $picked Question IDs in the block's order; empty = all by Order.
 * @return array<int, array{question:string,answer:string}>
 */
function rts_faq_items( array $picked = [] ): array {
	$args = [
		'post_type'              => 'rts_faq',
		'post_status'            => 'publish',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	];

	if ( ! empty( $picked ) ) {
		$args['post__in']       = array_map( 'intval', $picked );
		$args['orderby']        = 'post__in';
		$args['posts_per_page'] = count( $picked );
	} else {
		$args['posts_per_page'] = -1;
		$args['orderby']        = [
			'menu_order' => 'ASC',
			'ID'         => 'ASC',
		];
	}

	$items = [];

	foreach ( rts_get_posts( $args ) as $post ) {
		$items[] = [
			'question' => get_the_title( $post ),
			'answer'   => (string) apply_filters( 'the_content', $post->post_content ),
		];
	}

	return $items;
}

/**
 * One-time move of the old FAQ data into rts_faq posts.
 */
class RTS_Faq_Migration {

	private const DONE = 'rts_faq_migrated_v1';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'run' ] );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1, false );

		// Shared questions from Site Settings → every block that used them.
		$shared = $this->create_posts( $this->rows( 'options_faq_items', 'option' ) );

		$this->update_blocks( $shared );

		foreach ( [ 'options_faq_items', '_options_faq_items' ] as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * Old repeater rows { question, answer, open } stored as flat meta.
	 *
	 * @param string     $prefix  Meta / option name of the repeater.
	 * @param string|int $context 'option' or a post ID.
	 */
	private function rows( string $prefix, $context ): array {
		$get   = 'option' === $context
			? static fn( $name ) => get_option( $name )
			: static fn( $name ) => get_post_meta( (int) $context, $name, true );
		$count = (int) $get( $prefix );
		$rows  = [];

		for ( $i = 0; $i < $count; $i++ ) {
			$question = trim( (string) $get( "{$prefix}_{$i}_question" ) );

			if ( '' !== $question ) {
				$rows[] = [
					'question' => $question,
					'answer'   => (string) $get( "{$prefix}_{$i}_answer" ),
					'open'     => ! empty( $get( "{$prefix}_{$i}_open" ) ),
				];
			}
		}

		return $rows;
	}

	/**
	 * Creates (or finds by title) the question posts.
	 *
	 * @param array $rows Old rows.
	 * @return array{ids:int[],open:int} IDs in order + index of the open one (-1 none).
	 */
	private function create_posts( array $rows ): array {
		$ids  = [];
		$open = -1;

		foreach ( $rows as $n => $row ) {
			$existing = get_posts(
				[
					'post_type'   => 'rts_faq',
					'post_status' => 'any',
					'title'       => $row['question'],
					'numberposts' => 1,
					'fields'      => 'ids',
				]
			);

			$ids[] = $existing ? (int) $existing[0] : (int) wp_insert_post(
				[
					'post_type'    => 'rts_faq',
					'post_status'  => 'publish',
					'post_title'   => $row['question'],
					'post_content' => $row['answer'],
					'menu_order'   => count( $ids ),
				]
			);

			if ( $row['open'] ) {
				$open = $n;
			}
		}

		return [
			'ids'  => array_values( array_filter( $ids ) ),
			'open' => $open,
		];
	}

	/**
	 * Every FAQ block: “shared” → no pick (all questions), “own” → its rows
	 * as posts, picked in order; the open row becomes “Open on load”.
	 *
	 * @param array $shared Result of create_posts() for the shared rows.
	 */
	private function update_blocks( array $shared ): void {
		$posts = get_posts(
			[
				'post_type'   => [ 'page', 'rts_service', 'rts_lab', 'rts_expertise' ],
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => 'page_builder', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			]
		);

		foreach ( $posts as $post_id ) {
			$layouts = get_post_meta( $post_id, 'page_builder', true );

			if ( ! is_array( $layouts ) ) {
				continue;
			}

			foreach ( $layouts as $i => $layout ) {
				if ( 'faq' !== $layout ) {
					continue;
				}

				$base = "page_builder_{$i}";
				$own  = 'own' === get_post_meta( $post_id, "{$base}_source", true );
				$set  = $own ? $this->create_posts( $this->rows( "{$base}_items", $post_id ) ) : $shared;
				$open = $set['open'];
				$last = count( $set['ids'] ) - 1;

				update_post_meta( $post_id, "{$base}_questions", $own ? array_map( 'strval', $set['ids'] ) : '' );
				update_post_meta( $post_id, "_{$base}_questions", rts_faq_field_key( 'questions' ) );
				update_post_meta( $post_id, "{$base}_expanded", 0 === $open ? 'first' : ( $last === $open && $open > 0 ? 'last' : 'none' ) );
				update_post_meta( $post_id, "_{$base}_expanded", rts_faq_field_key( 'expanded' ) );

				foreach ( array_keys( (array) get_post_meta( $post_id ) ) as $meta_key ) {
					if ( preg_match( '/^_?' . preg_quote( $base, '/' ) . '_(source|items)(_|$)/', $meta_key ) ) {
						delete_post_meta( $post_id, $meta_key );
					}
				}
			}
		}
	}
}

/**
 * Deterministic key of a FAQ block sub-field (same scheme as acf-json).
 *
 * @param string $name Sub-field name.
 */
function rts_faq_field_key( string $name ): string {
	return 'field_' . substr( sha1( 'group_rts_page_builder/page_builder/faq/' . $name ), 0, 16 );
}

if ( is_admin() ) {
	new RTS_Faq_Migration();
}
