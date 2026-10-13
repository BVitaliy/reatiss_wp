<?php
/**
 * Article / event pages: editor output tweaks, related items, share links.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Articles {

	public function __construct() {
		// After shortcodes (11) — tables printed by them are wrapped as well.
		add_filter( 'the_content', [ $this, 'wrap_tables' ], 20 );
	}

	/**
	 * Editor tables scroll sideways on phones inside .article-table.
	 *
	 * @param string $content Post content.
	 */
	public function wrap_tables( $content ): string {
		$content = (string) $content;

		if ( ! str_contains( $content, '<table' ) || str_contains( $content, 'article-table' ) ) {
			return $content;
		}

		return (string) preg_replace(
			'#<table\b.*?</table>#s',
			'<div class="article-table" data-lenis-prevent-horizontal>$0</div>',
			$content
		);
	}
}

new RTS_Articles();

/**
 * Related items for the bottom of an article / event: the same category
 * first, topped up with the latest ones.
 *
 * @param WP_Post $post  Current post.
 * @param int     $count How many.
 * @return WP_Post[]
 */
function rts_related_posts( WP_Post $post, int $count = 3 ): array {
	$taxonomy = 'rts_event' === $post->post_type ? 'rts_event_cat' : 'category';
	$terms    = wp_get_post_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
	$base     = [
		'post_type'              => $post->post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $count,
		'post__not_in'           => [ $post->ID ],
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	];

	if ( 'rts_event' === $post->post_type ) {
		$base['meta_key'] = 'date_start'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$base['orderby']  = 'meta_value';
		$base['order']    = 'DESC';
	} else {
		$base['orderby'] = 'date'; // explicit: rspanel would sort posts by menu_order
		$base['order']   = 'DESC';
	}

	$posts = [];

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$posts = rts_get_posts(
			$base + [
				'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => $taxonomy,
						'terms'    => $terms,
					],
				],
			]
		);
	}

	if ( count( $posts ) < $count ) {
		$base['posts_per_page'] = $count - count( $posts );
		$base['post__not_in']   = array_merge( [ $post->ID ], wp_list_pluck( $posts, 'ID' ) );
		$posts                  = array_merge( $posts, rts_get_posts( $base ) );
	}

	rts_prime_thumbnails( $posts );

	return $posts;
}

/**
 * Share buttons of the article footer (Figma 7999:18510): Facebook, the
 * company Instagram (Instagram has no share URL), X, copy / native share.
 * app-article.js builds the share URLs from the page; Instagram links to the
 * profile from Site Settings and is left out without one.
 */
function rts_share_buttons(): void {
	$instagram = '';

	foreach ( rts_socials() as $social ) {
		if ( 'instagram' === $social['icon'] ) {
			$instagram = $social['url'];
		}
	}
	?>
	<ul class="social-list">
		<li><a href="#" class="social-btn js-share" data-share="facebook" aria-label="<?php esc_attr_e( 'Share on Facebook', 'reatiss' ); ?>" target="_blank" rel="noopener"><?php rts_icon( 'icons/share-facebook.svg' ); ?></a></li>
		<?php if ( '' !== $instagram ) { ?>
			<li><a href="<?php echo esc_url( $instagram ); ?>" class="social-btn" aria-label="<?php esc_attr_e( 'Reatiss on Instagram', 'reatiss' ); ?>" target="_blank" rel="noopener"><?php rts_icon( 'icons/share-instagram.svg' ); ?></a></li>
		<?php } ?>
		<li><a href="#" class="social-btn js-share" data-share="x" aria-label="<?php esc_attr_e( 'Share on X', 'reatiss' ); ?>" target="_blank" rel="noopener"><?php rts_icon( 'icons/share-x.svg' ); ?></a></li>
		<li><button class="social-btn js-share" data-share="copy" type="button" aria-label="<?php esc_attr_e( 'Share or copy link', 'reatiss' ); ?>"><?php rts_icon( 'icons/share-link.svg' ); ?></button></li>
	</ul>
	<?php
}
