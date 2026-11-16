<?php
/**
 * Home "Advanced Engineering Solutions" — one-time content update for the
 * client's edits of Nov 2026 (3D teardown): five services in two columns
 * (Package Analysis + Teardown left, Circuitry / Process / Product Analysis
 * right), the client's short texts, a "Learn more" link each, the CTA under
 * the scene.
 *
 * The theme files reach a server by FTP, the block's content lives in its
 * database: this writes it there once. Only into the front page's block
 * while it is still the original seed (4 services, no CTA) or the first
 * version of this update (with Patent Mining) — an edited block is left
 * alone.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Solutions_Migration {

	private const DONE = 'rts_solutions_3d_content_v2';

	// field keys (acf-json/group_rts_page_builder.json, layout "solutions")
	private const K = [
		'items'          => 'field_920abab497a2985d',
		'title'          => 'field_03017f34eb057cfd',
		'image'          => 'field_b6feb6200699081d',
		'side'           => 'field_d3c375fcfe9d204b',
		'text'           => 'field_bd74c0dd3cc3cba8',
		'link'           => 'field_b10eaa6ab5eff762',
		'link_label'     => 'field_d06e948271fc969b',
		'link_type'      => 'field_eea2b1969d33c0d3',
		'link_page'      => 'field_ac11ffc7ba9257a0',
		'top'            => 'field_4b89cc4e27167167',
		'cta_text'       => 'field_1f1e8785cf473492',
		'cta_btn'        => 'field_af1565946e75e453',
		'cta_btn_label'  => 'field_816ebe956f231ab6',
		'cta_btn_type'   => 'field_ed81a7696e2101ab',
	];

	// title, link (service slug; Package Analysis has no page — the services
	// list), text (the client's), side
	private const ITEMS = [
		[ 'Package Analysis', 'page:services', 'Package Analysis helps to understand construction of package and die/dice connection.', 'left' ],
		[ 'Teardown', 'teardown', 'Teardown identifies device components, chips, and system functionality.', 'left' ],
		[ 'Circuitry Analysis', 'circuitry-analysis', 'Circuitry Analysis reveals layout and circuit design details.', 'right' ],
		[ 'Process Analysis', 'process-analysis', 'Process Analysis gives understanding of material and structure of integrated circuit.', 'right' ],
		[ 'Product Analysis', 'product-analysis', 'Product Analysis provides detailed information on blocks functionality, area utilization measurements, device technology analysis, device parameters, and other aspects.', 'right' ],
	];

	public function __construct() {
		add_action( 'acf/init', [ $this, 'run' ], 20 );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1 );

		$page   = (int) get_option( 'page_on_front' );
		$layout = $page ? get_post_meta( $page, 'page_builder', true ) : [];

		if ( ! is_array( $layout ) ) {
			return;
		}

		foreach ( $layout as $i => $name ) {
			if ( 'solutions' === $name ) {
				$this->update_row( $page, 'page_builder_' . $i . '_' );
			}
		}
	}

	private function update_row( int $page, string $row ): void {
		$count = (int) get_post_meta( $page, $row . 'items', true );
		$cta   = (string) get_post_meta( $page, $row . 'cta_text', true );
		$seed  = 4 === $count && '' === $cta && '' === (string) get_post_meta( $page, $row . 'items_0_link_type', true );
		$v1    = 5 === $count && 'Patent Mining' === get_post_meta( $page, $row . 'items_3_title', true ) && 'Device teardown down to its component parts, chips and system-level functionality.' === get_post_meta( $page, $row . 'items_0_text', true );

		// edited by hand (the client's own content) — leave it
		if ( ! $seed && ! $v1 ) {
			return;
		}

		// the illustrations already in the block, alternating as before
		$images = [ (int) get_post_meta( $page, $row . 'items_0_image', true ), (int) get_post_meta( $page, $row . 'items_1_image', true ) ];

		// the old rows go: numbers, video timings of 4 zoom stops
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND ( meta_key LIKE %s OR meta_key LIKE %s )", $page, $wpdb->esc_like( $row . 'items_' ) . '%', $wpdb->esc_like( '_' . $row . 'items_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		foreach ( self::ITEMS as $n => [ $title, $slug, $text, $side ] ) {
			$p       = $row . 'items_' . $n . '_';
			$service = 0 === strpos( $slug, 'page:' ) ? get_page_by_path( substr( $slug, 5 ) ) : get_page_by_path( $slug, OBJECT, 'rts_service' );

			$this->set( $page, $p . 'title', $title, 'title' );
			$this->set( $page, $p . 'image', $images[ $n % 2 ], 'image' );
			$this->set( $page, $p . 'side', $side, 'side' );
			$this->set( $page, $p . 'text', $text, 'text' );
			$this->set( $page, $p . 'top', 'left' === $side ? 52 : 20, 'top' );
			$this->set( $page, $p . 'link', '', 'link' );
			$this->set( $page, $p . 'link_label', $service ? 'Learn more' : '', 'link_label' );
			$this->set( $page, $p . 'link_type', $service ? 'page' : 'none', 'link_type' );

			if ( $service ) {
				$this->set( $page, $p . 'link_page', $service->ID, 'link_page' );
			}
		}

		$this->set( $page, $row . 'items', count( self::ITEMS ), 'items' );

		// the CTA only into the seed (after v1 it is there already)
		if ( $seed ) {
			$this->set( $page, $row . 'cta_text', 'Not sure which analysis you need?', 'cta_text' );
			$this->set( $page, $row . 'cta_btn', '', 'cta_btn' );
			$this->set( $page, $row . 'cta_btn_label', 'Get a consultation', 'cta_btn_label' );
			$this->set( $page, $row . 'cta_btn_type', 'modal', 'cta_btn_type' );
		}

		clean_post_cache( $page );
	}

	/**
	 * A sub-field value + its ACF reference meta.
	 *
	 * @param int    $page  Post ID.
	 * @param string $name  Meta key.
	 * @param mixed  $value Value.
	 * @param string $field Key in self::K.
	 */
	private function set( int $page, string $name, $value, string $field ): void {
		update_post_meta( $page, $name, $value );
		update_post_meta( $page, '_' . $name, self::K[ $field ] );
	}
}

new RTS_Solutions_Migration();
