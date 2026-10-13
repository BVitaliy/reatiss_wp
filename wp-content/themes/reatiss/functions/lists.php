<?php
/**
 * Blog, events and author lists: one query builder for the page and the
 * "Show more" AJAX call, the pager markup (.pager: Show more + .pagination),
 * breadcrumbs / menu state for the archives.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cards per page: 3 columns of posts, 2 columns of events.
 */
const RTS_PER_PAGE = [
	'post'       => 9,
	'rts_event'  => 6,
	'rts_report' => 10,
];

/**
 * Card partial per list type.
 */
const RTS_LIST_CARDS = [
	'post'       => 'news',
	'rts_event'  => 'event',
	'rts_report' => 'report',
];

class RTS_Lists {

	public function __construct() {
		add_action( 'pre_get_posts', [ $this, 'main_query' ] );
		add_action( 'init', [ $this, 'events_paging_rule' ], 20 );
		add_action( 'acf/save_post', [ $this, 'flush_on_settings' ], 20 );
		add_action( 'wp_ajax_rts_load_more', [ $this, 'load_more' ] );
		add_action( 'wp_ajax_nopriv_rts_load_more', [ $this, 'load_more' ] );

		add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		add_filter( 'rts_breadcrumbs_before_current', [ $this, 'archive_parents' ] );
		add_filter( 'rts_nav_item_current', [ $this, 'nav_current' ], 10, 2 );
	}

	/**
	 * Posts per page on the blog, its archives and the author page.
	 *
	 * @param WP_Query $query Query.
	 */
	public function main_query( $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_author() ) {
			$query->set( 'posts_per_page', RTS_PER_PAGE['post'] );
			$query->set( 'ignore_sticky_posts', true );
			// Explicit: rspanel's post-order module would otherwise sort posts by menu_order.
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}

		if ( $query->is_tax( 'rts_event_cat' ) ) {
			$query->set( 'posts_per_page', RTS_PER_PAGE['rts_event'] );
			$query->set( 'meta_key', 'date_start' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'DESC' );
		}
	}

	/**
	 * /events/page/2/ is read by the event post type's rules as an event called
	 * "page" — the events list page gets its own paging rule ahead of them.
	 */
	public function events_paging_rule(): void {
		$page_id = rts_section_page_id( 'rts_event' );
		$path    = $page_id ? get_page_uri( $page_id ) : '';

		if ( '' !== $path ) {
			add_rewrite_rule( '^' . preg_quote( $path, '#' ) . '/page/([0-9]+)/?$', 'index.php?pagename=' . $path . '&paged=$matches[1]', 'top' );
		}
	}

	/**
	 * Site Settings saved (the events page may have changed) → rebuild rules.
	 *
	 * @param mixed $post_id ACF post ID.
	 */
	public function flush_on_settings( $post_id ): void {
		if ( 'options' === $post_id ) {
			flush_rewrite_rules( false );
		}
	}

	/**
	 * "Show more": the next page of the same list, as card markup.
	 */
	public function load_more(): void {
		check_ajax_referer( 'rts_nonce', 'nonce' );

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
		$type     = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'post';
		$type     = isset( RTS_LIST_CARDS[ $type ] ) ? $type : 'post';
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$term     = isset( $_POST['term'] ) ? absint( $_POST['term'] ) : 0;
		$author   = isset( $_POST['author'] ) ? absint( $_POST['author'] ) : 0;
		$page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
		// phpcs:enable

		$query = rts_list_query( $type, $page, $taxonomy, $term, $author );

		ob_start();
		foreach ( $query->posts as $post ) {
			echo '<li>';
			get_template_part( 'template-parts/cards/' . RTS_LIST_CARDS[ $type ], null, [ 'post' => $post ] );
			echo '</li>';
		}

		wp_send_json_success(
			[
				'html' => (string) ob_get_clean(),
				'page' => $page,
				'more' => $page < (int) $query->max_num_pages,
			]
		);
	}

	/**
	 * Category / tag → Blog, event category → Events in the breadcrumbs.
	 *
	 * @param array $items Trail so far (Home …).
	 */
	public function archive_parents( array $items ): array {
		$parent = 0;

		if ( is_category() || is_tag() ) {
			$parent = rts_section_page_id( 'post' );
		} elseif ( is_tax( 'rts_event_cat' ) ) {
			$parent = rts_section_page_id( 'rts_event' );
		}

		if ( $parent ) {
			$items[] = [
				'title' => get_the_title( $parent ),
				'url'   => (string) get_permalink( $parent ),
			];
		}

		return $items;
	}

	/**
	 * An event category lights up the Events menu item.
	 *
	 * @param bool    $current Current state.
	 * @param WP_Post $item    Menu item.
	 */
	public function nav_current( $current, $item ) {
		if ( $current || ! is_tax( 'rts_event_cat' ) || 'page' !== $item->object ) {
			return $current;
		}

		return (int) $item->object_id === rts_section_page_id( 'rts_event' );
	}
}

new RTS_Lists();

/**
 * One page of a list — the same query for the page and for "Show more".
 *
 * @param string $type     'post' | 'rts_event'.
 * @param int    $page     Page number.
 * @param string $taxonomy Filter taxonomy (category, post_tag, rts_event_cat).
 * @param int    $term     Filter term ID.
 * @param int    $author   Author ID.
 */
function rts_list_query( string $type, int $page = 1, string $taxonomy = '', int $term = 0, int $author = 0 ): WP_Query {
	$args = [
		'post_type'              => $type,
		'post_status'            => 'publish',
		'posts_per_page'         => RTS_PER_PAGE[ $type ] ?? 9,
		'paged'                  => $page,
		'ignore_sticky_posts'    => true,
		'update_post_term_cache' => false,
	];

	if ( 'rts_event' === $type ) {
		$args['meta_key'] = 'date_start'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['orderby']  = 'meta_value';
		$args['order']    = 'DESC';
	} elseif ( 'rts_report' === $type ) {
		$args['orderby'] = [
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		];
	} else {
		$args['orderby'] = 'date'; // explicit — see RTS_Lists::main_query()
		$args['order']   = 'DESC';
	}

	if ( $term && in_array( $taxonomy, [ 'category', 'post_tag', 'rts_event_cat' ], true ) ) {
		$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			[
				'taxonomy' => $taxonomy,
				'terms'    => $term,
			],
		];
	}

	if ( $author ) {
		$args['author'] = $author;
	}

	$query = new WP_Query( $args );
	rts_prime_thumbnails( $query->posts );

	return $query;
}

/**
 * Category chips: "All …" + every non-empty term; the current one active.
 *
 * @param string $taxonomy  Taxonomy.
 * @param string $all_label Label of the "all" chip.
 * @param string $all_url   URL of the "all" chip.
 */
function rts_filter_chips( string $taxonomy, string $all_label, string $all_url ): void {
	$terms = get_terms(
		[
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'term_id', // creation order, as the editor added them
			'exclude'    => 'category' === $taxonomy ? [ (int) get_option( 'default_category' ) ] : [],
		]
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$current = is_tax( $taxonomy ) || is_category() || is_tag() ? (int) get_queried_object_id() : 0;
	?>
	<nav class="list-page__filter" aria-label="<?php esc_attr_e( 'Categories', 'reatiss' ); ?>">
		<ul class="chips chips--scroll">
			<li><a href="<?php echo esc_url( $all_url ); ?>" class="chip<?php echo $current ? '' : ' is-active'; ?>"<?php echo $current ? '' : ' aria-current="page"'; ?>><?php echo esc_html( $all_label ); ?></a></li>
			<?php foreach ( $terms as $term ) { ?>
				<li><a href="<?php echo esc_url( (string) get_term_link( $term ) ); ?>" class="chip<?php echo $current === $term->term_id ? ' is-active' : ''; ?>"<?php echo $current === $term->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $term->name ); ?></a></li>
			<?php } ?>
		</ul>
	</nav>
	<?php
}

/**
 * Page numbers with gaps: 1 2 … 5 [6] 7 … 18 19.
 *
 * @param int $current Current page.
 * @param int $total   Total pages.
 * @return array<int|string> Numbers and '…'.
 */
function rts_page_numbers( int $current, int $total ): array {
	$keep = [];

	foreach ( range( 1, $total ) as $n ) {
		if ( $n <= 2 || $n > $total - 2 || abs( $n - $current ) <= 1 ) {
			$keep[] = $n;
		}
	}

	$out  = [];
	$prev = 0;

	foreach ( $keep as $n ) {
		if ( $n - $prev > 1 ) {
			$out[] = '…';
		}

		$out[] = $n;
		$prev  = $n;
	}

	return $out;
}

/**
 * The pager: "Show more" (next page by AJAX, a plain link without JS) +
 * the page numbers. Prints nothing for a single page.
 *
 * @param WP_Query $query List query.
 * @param array    $data  { type, taxonomy, term, author } for the AJAX call.
 * @param string   $class Extra class on .pager.
 */
function rts_pager( WP_Query $query, array $data, string $class = 'list-page__pager' ): void {
	$total   = (int) $query->max_num_pages;
	$current = max( 1, (int) $query->get( 'paged' ) );

	if ( $total < 2 ) {
		return;
	}

	$arrow = static function ( string $dir, int $page ) use ( $total ): void {
		$label = 'prev' === $dir ? __( 'Previous page', 'reatiss' ) : __( 'Next page', 'reatiss' );
		$class = 'pagination__arrow pagination__arrow--' . $dir;

		if ( $page < 1 || $page > $total ) {
			printf( '<span class="%s is-disabled" aria-hidden="true">', esc_attr( $class ) );
			rts_icon( 'icons/arrow.svg' );
			echo '</span>';

			return;
		}

		printf( '<a href="%s" class="%s" aria-label="%s">', esc_url( get_pagenum_link( $page ) ), esc_attr( $class ), esc_attr( $label ) );
		rts_icon( 'icons/arrow.svg' );
		echo '</a>';
	};
	?>
	<div class="pager <?php echo esc_attr( $class ); ?>">
		<?php if ( $current < $total ) { ?>
			<a href="<?php echo esc_url( get_pagenum_link( $current + 1 ) ); ?>" class="btn btn--primary js-load-more"
				data-type="<?php echo esc_attr( (string) ( $data['type'] ?? 'post' ) ); ?>"
				data-taxonomy="<?php echo esc_attr( (string) ( $data['taxonomy'] ?? '' ) ); ?>"
				data-term="<?php echo esc_attr( (string) (int) ( $data['term'] ?? 0 ) ); ?>"
				data-author="<?php echo esc_attr( (string) (int) ( $data['author'] ?? 0 ) ); ?>"
				data-page="<?php echo esc_attr( (string) $current ); ?>">
				<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
				<?php esc_html_e( 'Show more', 'reatiss' ); ?>
			</a>
		<?php } ?>
		<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'reatiss' ); ?>">
			<?php $arrow( 'prev', $current - 1 ); ?>
			<ul class="pagination__list">
				<?php foreach ( rts_page_numbers( $current, $total ) as $n ) { ?>
					<li>
						<?php if ( '…' === $n ) { ?>
							<span class="pagination__item pagination__dots">…</span>
						<?php } elseif ( $n === $current ) { ?>
							<span class="pagination__item is-active" aria-current="page"><?php echo esc_html( (string) $n ); ?></span>
						<?php } else { ?>
							<a href="<?php echo esc_url( get_pagenum_link( $n ) ); ?>" class="pagination__item" data-page="<?php echo esc_attr( (string) $n ); ?>"><?php echo esc_html( (string) $n ); ?></a>
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
			<?php $arrow( 'next', $current + 1 ); ?>
		</nav>
	</div>
	<?php
}
