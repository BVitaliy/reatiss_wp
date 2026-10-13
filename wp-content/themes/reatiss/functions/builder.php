<?php
/**
 * Page builder (ACF flexible content `page_builder`) rendering.
 *
 * Every layout renders template-parts/blocks/{layout}.php with the row as
 * $args. The first sub-field of every layout is `{layout}_hide`; hidden rows
 * render nothing — not even their spacer, so removing a block leaves no gap.
 *
 * Spacing follows the markup: every section is followed by `.spacer-xl`,
 * the SEO block (last on a page) by `.spacer-sm`.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Layouts that open the page with a dark full-screen banner: the header stays
 * transparent over them and the breadcrumbs move into the banner.
 */
const RTS_DARK_TOP_LAYOUTS = [ 'hero', 'page_banner' ];

/**
 * Spacer after a layout, where it differs from `spacer-xl`.
 */
const RTS_LAYOUT_SPACERS = [
	'seo' => 'spacer-sm',
];

/**
 * Visible builder rows of a post.
 *
 * @param int $post_id Post ID; defaults to the queried object.
 * @return array[] Rows with `acf_fc_layout`.
 */
function rts_builder_rows( int $post_id = 0 ): array {
	static $cache = [];

	$post_id = $post_id ? $post_id : (int) get_queried_object_id();

	if ( ! $post_id ) {
		return [];
	}

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	// Every image / file / post of the page in two queries instead of two per item.
	rts_prime_posts( rts_meta_ids( $post_id ) );

	$rows = function_exists( 'get_field' ) ? get_field( 'page_builder', $post_id ) : null;

	$cache[ $post_id ] = array_values(
		array_filter(
			is_array( $rows ) ? $rows : [],
			static function ( $row ) {
				$layout = $row['acf_fc_layout'] ?? '';

				return '' !== $layout && empty( $row[ $layout . '_hide' ] );
			}
		)
	);

	return $cache[ $post_id ];
}

/**
 * Renders the page builder.
 *
 * @param int $post_id Post ID; defaults to the queried object.
 */
function rts_render_builder( int $post_id = 0 ): void {
	foreach ( rts_builder_rows( $post_id ) as $index => $row ) {
		$layout = $row['acf_fc_layout'];

		$row['index'] = $index;

		get_template_part( 'template-parts/blocks/' . str_replace( '_', '-', $layout ), null, $row );
		printf( '<div class="%s"></div>', esc_attr( RTS_LAYOUT_SPACERS[ $layout ] ?? 'spacer-xl' ) );
	}
}

/**
 * Does the current page's builder contain any of these layouts?
 *
 * @param string[] $layouts Layout names.
 */
function rts_has_layout( array $layouts ): bool {
	if ( ! is_singular() ) {
		return false;
	}

	$used = array_column( rts_builder_rows(), 'acf_fc_layout' );

	return (bool) array_intersect( $layouts, $used );
}

/**
 * Does the current page open with a dark banner (see RTS_DARK_TOP_LAYOUTS)?
 */
function rts_has_dark_top(): bool {
	if ( ! is_singular() ) {
		return false;
	}

	$rows = rts_builder_rows();

	return ! empty( $rows ) && in_array( $rows[0]['acf_fc_layout'], RTS_DARK_TOP_LAYOUTS, true );
}

add_filter(
	'rts_header_dark',
	static function ( $dark ) {
		return $dark || rts_has_dark_top();
	}
);

/**
 * Builder page body: breadcrumbs (unless a banner carries them) + the blocks.
 */
function rts_builder_page(): void {
	if ( ! rts_has_dark_top() ) {
		get_template_part( 'template-parts/global/page-top' );
	}

	rts_render_builder();
}

/**
 * Post types whose singles are built with the page builder (single-builder.php).
 */
const RTS_BUILDER_TYPES = [ 'rts_service', 'rts_lab', 'rts_expertise' ];

add_filter(
	'single_template_hierarchy',
	static function ( $templates ) {
		if ( is_singular( RTS_BUILDER_TYPES ) ) {
			array_unshift( $templates, 'single-builder.php' );
		}

		return $templates;
	}
);
