<?php
/**
 * Nav menu walker.
 *
 * The design uses flat, one-level menus with no WordPress-generated classes,
 * so the default walker's markup would fight the stylesheet. This emits exactly
 * what the static markup did: <li class="…"><a class="…" href="…">Label</a></li>.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Class on every <li>; empty for a bare <li>.
	 */
	private string $item_class;

	/**
	 * Class on every <a>.
	 */
	private string $link_class;

	/**
	 * Extra attributes on every <a>, e.g. [ 'tabindex' => '-1' ] for the
	 * off-canvas menu that is unreachable by keyboard until opened.
	 */
	private array $link_attrs;

	public function __construct( string $item_class = '', string $link_class = '', array $link_attrs = [] ) {
		$this->item_class = $item_class;
		$this->link_class = $link_class;
		$this->link_attrs = $link_attrs;
	}

	/**
	 * No nested <ul> — the menus are flat by design.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( $depth > 0 ) {
			return;
		}

		$is_current = ! is_front_page() && ! empty( array_intersect( [ 'current-menu-item', 'current_page_item', 'current-menu-ancestor' ], (array) $item->classes ) );

		// WP tags the blog page `current_page_parent` on every non-page view
		// (404, search…) — count it only where the blog really is the section.
		if ( ! $is_current && in_array( 'current_page_parent', (array) $item->classes, true ) ) {
			$is_current = is_singular( 'post' ) || is_category() || is_tag() || is_author();
		}

		$is_current = (bool) apply_filters( 'rts_nav_item_current', $is_current, $item );

		$attrs = [
			'class' => trim( $this->link_class . ( $is_current ? ' is-active' : '' ) ),
			'href'  => rts_resolve_anchor( (string) $item->url ),
		];

		if ( $is_current ) {
			$attrs['aria-current'] = 'page';
		}

		if ( ! empty( $item->target ) ) {
			$attrs['target'] = $item->target;
			$attrs['rel']    = 'noopener noreferrer';
		}

		$attrs = array_merge( $attrs, $this->link_attrs );
		$html  = '';

		foreach ( $attrs as $name => $value ) {
			if ( '' === (string) $value ) {
				continue;
			}

			$html .= sprintf( ' %s="%s"', esc_attr( $name ), 'href' === $name ? esc_url( $value ) : esc_attr( $value ) );
		}

		$li_class = '' !== $this->item_class ? ' class="' . esc_attr( $this->item_class ) . '"' : '';

		$output .= '<li' . $li_class . '><a' . $html . '>' . esc_html( $item->title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $depth > 0 ) {
			return;
		}

		$output .= '</li>';
	}
}
