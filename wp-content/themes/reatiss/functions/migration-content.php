<?php
/**
 * One-time content update (client, Nov 2026) for a server that got the theme
 * by FTP but not the local database:
 * - home stats lead: the new sentence (only while the old one is there);
 * - On Shelf: the mockup's title and the text under it (only the old title /
 *   an empty text);
 * - reports: the six still carrying the old seed names get the mockup's part
 *   numbers + functionality, the 13th (SanDisk) is added if missing.
 * Anything already edited is left alone.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Content_Migration {

	private const DONE = 'rts_content_nov16_v1';

	private const STATS_OLD = 'Our expertise combines advanced analysis, scientific precision, and proven engineering to deliver reliable insights for benchmarking, IP protection, and innovation.';
	private const STATS_NEW = 'Our experience, engineering expertise, and proven track record make us a trusted partner to semiconductor patent owners worldwide.';

	private const SHELF_TITLE_OLD = 'Available Analysis Reports';
	private const SHELF_TITLE_NEW = 'Available RE materials';
	private const SHELF_TEXT      = '<p>We continuously perform custom IC analyses and review new product releases to stay up to date with the latest developments in the IC industry. A selection of our recent analyses is listed below. If you do not find the IC you are looking for, or if you have any inquiries, please contact us at: <a href="mailto:info@reatiss.com">info@reatiss.com</a></p>';

	// old seed name => [ part number and die marking, functionality, order ]
	private const REPORTS = [
		'Power Management IC Analysis' => [ 'Broadcom BCM43684KRFBG BCM43684', '802.11ax Wi-Fi Chipset', 7 ],
		'Display Driver IC Analysis'   => [ 'Broadcom BCM4389 BCM4389', 'WiFi 6E/BT 5.0 chip', 8 ],
		'Audio Codec IC Analysis'      => [ 'Broadcom BCM56370 BCM56370', 'Ethernet Switch', 9 ],
		'RF Transceiver Analysis'      => [ 'Intel i5-11400 SRKP0 RKL', 'Core i5-11400 Rocket Lake Processor', 10 ],
		'FPGA Architecture Analysis'   => [ 'KIOXIA K5A3 FXH8 512G', '3D NAND Flash Memory', 11 ],
		'MEMS Sensor Analysis'         => [ 'Renesas R7F702300B FABA-C R7F7', 'RH850/U2A16 32-bit Microcontroller', 12 ],
	];
	private const REPORT_NEW = [ 'SanDisk 9043DG327462 256G LAMBEAU UP4', 'SD Card Controller', 13 ];

	// field keys: acf-json/group_rts_page_builder.json, group_rts_report.json
	private const K = [
		'stats_lead'    => 'field_92baa249f33d825f',
		'shelf_title'   => 'field_d8a19a695f1bd8d0',
		'shelf_text'    => 'field_2e34eb3cc004705d',
		'functionality' => 'field_34bb237f4c1bac0b',
		'description'   => 'field_57bdabf2834146e4',
	];

	public function __construct() {
		add_action( 'acf/init', [ $this, 'run' ], 20 );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1 );

		$this->builder_rows(
			(int) get_option( 'page_on_front' ),
			'stats',
			function ( int $page, string $row ) {
				if ( self::STATS_OLD === trim( (string) get_post_meta( $page, $row . 'lead', true ) ) ) {
					$this->set( $page, $row . 'lead', self::STATS_NEW, 'stats_lead' );
				}
			}
		);

		foreach ( $this->pages_with( 'reports_list' ) as $page ) {
			$this->builder_rows(
				$page,
				'reports_list',
				function ( int $page, string $row ) {
					if ( self::SHELF_TITLE_OLD === trim( (string) get_post_meta( $page, $row . 'title', true ) ) ) {
						$this->set( $page, $row . 'title', self::SHELF_TITLE_NEW, 'shelf_title' );
					}
					if ( '' === trim( (string) get_post_meta( $page, $row . 'text', true ) ) ) {
						$this->set( $page, $row . 'text', self::SHELF_TEXT, 'shelf_text' );
					}
				}
			);
		}

		foreach ( self::REPORTS as $old => $new ) {
			$id = $this->report( $old );
			if ( $id ) {
				$this->update_report( $id, $new );
			}
		}

		if ( ! $this->report( self::REPORT_NEW[0] ) ) {
			$id = wp_insert_post(
				[
					'post_type'   => 'rts_report',
					'post_status' => 'publish',
					'post_title'  => self::REPORT_NEW[0],
				]
			);
			if ( $id && ! is_wp_error( $id ) ) {
				$this->update_report( (int) $id, self::REPORT_NEW );
			}
		}
	}

	/**
	 * @param int   $id  Report ID.
	 * @param array $new [ title, functionality, order ].
	 */
	private function update_report( int $id, array $new ): void {
		[ $title, $func, $order ] = $new;

		wp_update_post(
			[
				'ID'         => $id,
				'post_title' => $title,
				'post_name'  => sanitize_title( $title ),
				'menu_order' => $order,
			]
		);
		$this->set( $id, 'functionality', $func, 'functionality' );
		// the old description was about the old name
		$this->set( $id, 'description', sprintf( 'Die-level analysis of the %s: package and die photos, layout overview and process details.', $func ), 'description' );
	}

	private function report( string $title ): int {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'rts_report' AND post_status <> 'trash' AND post_title = %s LIMIT 1", $title ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Pages whose builder holds a layout.
	 *
	 * @param string $layout Layout name.
	 * @return int[]
	 */
	private function pages_with( string $layout ): array {
		global $wpdb;

		return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'page_builder' AND meta_value LIKE %s", '%' . $wpdb->esc_like( '"' . $layout . '"' ) . '%' ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Calls $fn( $page, 'page_builder_N_' ) for each row of a layout.
	 *
	 * @param int      $page   Post ID.
	 * @param string   $layout Layout name.
	 * @param callable $fn     Callback.
	 */
	private function builder_rows( int $page, string $layout, callable $fn ): void {
		$rows = $page ? get_post_meta( $page, 'page_builder', true ) : [];

		foreach ( is_array( $rows ) ? $rows : [] as $i => $name ) {
			if ( $layout === $name ) {
				$fn( $page, 'page_builder_' . $i . '_' );
			}
		}

		clean_post_cache( $page );
	}

	/**
	 * A field value + its ACF reference meta.
	 *
	 * @param int    $id    Post ID.
	 * @param string $name  Meta key.
	 * @param mixed  $value Value.
	 * @param string $field Key in self::K.
	 */
	private function set( int $id, string $name, $value, string $field ): void {
		update_post_meta( $id, $name, $value );
		update_post_meta( $id, '_' . $name, self::K[ $field ] );
	}
}

new RTS_Content_Migration();
