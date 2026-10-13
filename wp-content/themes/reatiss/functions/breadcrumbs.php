<?php
/**
 * Breadcrumbs: Home → section page → current item.
 *
 * Markup: nav.breadcrumbs > ol.breadcrumbs__list > li.breadcrumbs__item
 * (link, the current one a <span aria-current="page">). Also printed inside
 * dark page banners with `breadcrumbs--light`.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trail for the current request.
 *
 * @return array<int, array{title:string,url:string}> The last item has no URL.
 */
function rts_breadcrumbs(): array {
	$items = [
		[
			'title' => __( 'Home', 'reatiss' ),
			'url'   => RTS_HOME_URL,
		],
	];

	if ( is_front_page() ) {
		return [];
	}

	if ( is_search() ) {
		$items[] = [
			'title' => __( 'Search results', 'reatiss' ),
			'url'   => '',
		];

		return $items;
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();

		// Section page the item belongs to (blog page for posts, list pages for CPTs).
		$section = (int) apply_filters( 'rts_breadcrumb_section', 'post' === get_post_type( $post_id ) ? (int) get_option( 'page_for_posts' ) : 0, $post_id );

		foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor ) {
			$items[] = [
				'title' => get_the_title( $ancestor ),
				'url'   => (string) get_permalink( $ancestor ),
			];
		}

		if ( $section ) {
			$items[] = [
				'title' => get_the_title( $section ),
				'url'   => (string) get_permalink( $section ),
			];
		}

		$items[] = [
			'title' => get_the_title( $post_id ),
			'url'   => '',
		];

		return $items;
	}

	$items[] = [
		'title' => wp_strip_all_tags( (string) apply_filters( 'rts_breadcrumb_title', get_the_archive_title() ) ),
		'url'   => '',
	];

	return $items;
}

/**
 * Prints the breadcrumbs nav.
 *
 * @param array  $items    From rts_breadcrumbs().
 * @param string $modifier Extra class: 'breadcrumbs--left' | 'breadcrumbs--light'.
 */
function rts_breadcrumbs_nav( array $items, string $modifier = '' ): void {
	if ( count( $items ) < 2 ) {
		return;
	}
	?>
	<nav class="<?php echo esc_attr( trim( 'breadcrumbs ' . $modifier ) ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'reatiss' ); ?>">
		<ol class="breadcrumbs__list">
			<?php foreach ( $items as $item ) { ?>
				<li class="breadcrumbs__item">
					<?php if ( '' !== $item['url'] ) { ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					<?php } else { ?>
						<span aria-current="page"><?php echo esc_html( $item['title'] ); ?></span>
					<?php } ?>
				</li>
			<?php } ?>
		</ol>
	</nav>
	<?php
}
