<?php
/**
 * Theme setup and asset loading.
 *
 * Scripts and styles follow the markup's inc/_top.php and inc/_bottom.php in
 * the same order: every app module bails out by itself when its block is not
 * on the page, exactly as in the static build.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Theme {

	/**
	 * Vendor scripts (inc/_bottom.php order) => where they are needed
	 * ('all' or a condition key of conditions()). The theme's own jQuery build
	 * replaces the core one — the markup was written and tested against it.
	 * lightGallery and model-viewer are fetched on demand by their modules.
	 */
	private const VENDORS = [
		'jquery'             => [ 'js/vendors/jquery.min.js', 'all' ],
		'rts-swiper'         => [ 'js/vendors/swiper-bundle.min.js', 'swiper' ],
		'rts-gsap'           => [ 'js/vendors/gsap.min.js', 'all' ],
		'rts-scroll-trigger' => [ 'js/vendors/ScrollTrigger.min.js', 'all' ],
		'rts-split-text'     => [ 'js/vendors/SplitText.min.js', 'all' ],
		'rts-motion-path'    => [ 'js/vendors/MotionPathPlugin.min.js', 'all' ],
		'rts-draw-svg'       => [ 'js/vendors/DrawSVGPlugin.min.js', 'draw_svg' ],
		'rts-lenis'          => [ 'js/vendors/lenis.min.js', 'all' ],
	];

	/**
	 * App scripts (inc/_bottom.php order) => where they are needed. Each module
	 * also bails out by itself when its block is missing; loading it only where
	 * the block is keeps every other page lighter (team checklist #53).
	 */
	private const SCRIPTS = [
		'rts-global'      => [ 'js/app-global.js', 'all' ],
		'rts-modals'      => [ 'js/app-modals.js', 'all' ],
		'rts-header'      => [ 'js/app-header.js', 'all' ],
		'rts-swiper-init' => [ 'js/app-swiper.js', 'swiper' ],
		'rts-faq'         => [ 'js/app-faq.js', 'faq' ],
		'rts-form'        => [ 'js/app-form.js', 'all' ],
		'rts-cf7'         => [ 'js/app-cf7.js', 'all' ],
		'rts-wp'          => [ 'js/app-wp.js', 'all' ],
		'rts-load-more'   => [ 'js/app-load-more.js', 'lists' ],
		'rts-reports'     => [ 'js/app-reports.js', 'reports' ],
		'rts-search'      => [ 'js/app-search.js', 'all' ],
		'rts-select'      => [ 'js/app-select.js', 'select' ],
		'rts-hero'        => [ 'js/app-hero.js', 'hero' ],
		'rts-lines'       => [ 'js/app-lines.js', 'all' ],
		'rts-stats'       => [ 'js/app-stats.js', 'all' ], // the footer dot grid too
		'rts-solutions'   => [ 'js/app-solutions.js', 'solutions' ],
		'rts-titles'      => [ 'js/app-titles.js', 'all' ],
		'rts-draw'        => [ 'js/app-draw.js', 'draw' ],
		'rts-page-theme'  => [ 'js/app-page-theme.js', 'front' ],
		'rts-video'       => [ 'js/app-video.js', 'video' ],
		'rts-parallax'    => [ 'js/app-parallax.js', 'parallax' ],
		'rts-more-text'   => [ 'js/app-more-text.js', 'more_text' ],
		'rts-article'     => [ 'js/app-article.js', 'article' ],
		'rts-map'         => [ 'js/app-map.js', 'map' ],
		'rts-statement'   => [ 'js/app-statement.js', 'statement' ],
		'rts-gallery'     => [ 'js/app-gallery.js', 'gallery' ],
		'rts-model'       => [ 'js/app-model.js', 'model' ],
		'rts-about'       => [ 'js/app-about.js', 'about' ],
		'rts-lab'         => [ 'js/app-lab.js', 'lab' ],
		'rts-error'       => [ 'js/app-error.js', 'error' ],
		'rts-animations'  => [ 'js/app-animations.js', 'all' ],
	];

	/**
	 * Fonts preloaded in <head> (latin subsets — the site is English).
	 */
	private const PRELOAD_FONTS = [
		'fonts/Inter-latin.woff2',
		'fonts/PlusJakartaSans-latin.woff2',
	];

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_head', [ $this, 'head_meta' ], 1 );
		add_action( 'init', [ $this, 'cleanup_head' ] );

		// Classic widgets — the design has no widget areas edited with blocks.
		add_filter( 'use_widgets_block_editor', '__return_false' );

		// The [gallery] shortcode is styled by .rich-text; WP's inline <style> fights it.
		add_filter( 'use_default_gallery_style', '__return_false' );

		// ACF reads / writes field groups in the theme's acf-json/ by itself.
		// Labels and instructions go through __( …, 'reatiss' ), so uk.po
		// localises the admin per user language.
		add_filter(
			'acf/settings/l10n_textdomain',
			static function () {
				return 'reatiss';
			}
		);
		add_action( 'acf/init', [ $this, 'options_page' ] );
	}

	/**
	 * Site Settings (ACF options page).
	 */
	public function options_page(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			[
				'page_title' => __( 'Site Settings', 'reatiss' ),
				'menu_title' => __( 'Site Settings', 'reatiss' ),
				'menu_slug'  => 'rts-site-settings',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'icon_url'   => 'dashicons-admin-generic',
				'position'   => 59,
			]
		);
	}

	public function setup(): void {
		load_theme_textdomain( 'reatiss', RTS_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			[
				'header_menu'   => __( 'Main menu (header and mobile menu)', 'reatiss' ),
				'footer_menu_1' => __( 'Footer — column 1', 'reatiss' ),
				'footer_menu_2' => __( 'Footer — column 2', 'reatiss' ),
				'footer_menu_3' => __( 'Footer — column 3', 'reatiss' ),
				'footer_menu_4' => __( 'Footer — column 4', 'reatiss' ),
				'footer_menu_5' => __( 'Footer — column 5', 'reatiss' ),
				'legal_menu'    => __( 'Footer — legal links', 'reatiss' ),
			]
		);
	}

	/**
	 * Meta, favicons and the pre-paint script from the markup's inc/_top.php.
	 */
	public function head_meta(): void {
		?>
		<meta name="theme-color" content="#0a0a2a">
		<?php if ( ! has_site_icon() ) { ?>
			<link rel="icon" href="<?php echo esc_url( rts_asset( 'favicon.ico' ) ); ?>" sizes="any">
			<link rel="icon" href="<?php echo esc_url( rts_asset( 'favicon.svg' ) ); ?>" type="image/svg+xml">
			<link rel="apple-touch-icon" href="<?php echo esc_url( rts_asset( 'apple-touch-icon.png' ) ); ?>">
			<link rel="manifest" href="<?php echo esc_url( rts_asset( 'site.webmanifest' ) ); ?>">
		<?php } ?>
		<?php foreach ( self::PRELOAD_FONTS as $font ) { ?>
			<link rel="preload" href="<?php echo esc_url( rts_asset( $font ) ); ?>" as="font" type="font/woff2" crossorigin>
		<?php } ?>
		<script>
			// Always start from the top (no restored mid-page scroll under pinned scenes)
			if ("scrollRestoration" in history) history.scrollRestoration = "manual";
			// Pre-hide flag for title reveals (app-titles.js) — set before first paint
			document.documentElement.classList.add("js");
		</script>
		<?php
	}

	/**
	 * Strips head output this site never uses.
	 */
	public function cleanup_head(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	}

	/**
	 * Which optional scripts the current view needs — from its builder blocks
	 * and its template.
	 *
	 * @return array<string, bool>
	 */
	private function conditions(): array {
		$blocks = array_column( rts_builder_rows(), 'acf_fc_layout' );
		$has    = static function ( array $layouts ) use ( $blocks ): bool {
			return (bool) array_intersect( $layouts, $blocks );
		};

		$single    = is_singular( [ 'post', 'rts_event' ] );
		$shortcode = is_singular() && (bool) preg_match( '/\[(rts_|gallery)/', (string) get_post_field( 'post_content', get_queried_object_id() ) );
		$lists     = is_home() || is_archive() || is_search() || is_page_template( 'templates/events.php' ) || $has( [ 'reports_list' ] );

		return [
			'swiper'    => $single || $shortcode || $has( [ 'expertise_cards', 'labs_cards', 'news_cards', 'event_cards', 'reveals', 'info_cards', 'capabilities', 'gallery', 'content', 'accordion' ] ),
			'draw_svg'  => is_404() || $has( [ 'about_stats', 'clients' ] ),
			'faq'       => $has( [ 'faq', 'accordion' ] ),
			'lists'     => $lists,
			'reports'   => $has( [ 'reports_list' ] ),
			'select'    => $has( [ 'contacts' ] ),
			'hero'      => $has( [ 'hero' ] ),
			'solutions' => $has( [ 'solutions' ] ),
			'draw'      => $has( [ 'expertise_cards', 'stats', 'reveals', 'offer_cards' ] ),
			'front'     => is_front_page(),
			'video'     => $has( [ 'hero', 'page_banner', 'video_banner' ] ),
			'parallax'  => $has( [ 'left_right', 'page_banner', 'video_banner' ] ),
			'more_text' => is_author() || $has( [ 'seo' ] ),
			'article'   => $single || $shortcode || $has( [ 'content', 'accordion' ] ),
			'map'       => $has( [ 'contacts_map' ] ),
			'statement' => $has( [ 'statement' ] ),
			'gallery'   => $has( [ 'gallery' ] ),
			'model'     => $has( [ 'model_3d' ] ),
			'about'     => $has( [ 'about_intro', 'history', 'about_stats', 'clients', 'team' ] ),
			'lab'       => $has( [ 'capabilities' ] ),
			'error'     => is_404(),
		];
	}

	public function enqueue_assets(): void {
		// The block library CSS is dead weight — this theme renders no blocks.
		wp_dequeue_style( 'wp-block-library' );
		wp_deregister_style( 'wp-block-library' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );

		$need = $this->conditions();

		// --- Styles (inc/_top.php order) ---
		$main_deps = [];

		if ( $need['swiper'] ) {
			wp_enqueue_style( 'rts-swiper', rts_asset( 'css/vendors/swiper-bundle.min.css' ), [], null );
			$main_deps[] = 'rts-swiper';
		}

		wp_enqueue_style( 'rts-main', rts_asset( 'css/main.css' ), $main_deps, rts_asset_ver( 'css/main.css' ) );
		wp_enqueue_style( 'rts-style', rts_asset( 'css/style.css' ), [ 'rts-main' ], rts_asset_ver( 'css/style.css' ) );

		$args = [
			'strategy'  => 'defer',
			'in_footer' => true,
		];

		// --- Vendor scripts ---
		wp_deregister_script( 'jquery' );

		$deps = [];
		foreach ( self::VENDORS as $handle => $vendor ) {
			if ( 'all' === $vendor[1] || ! empty( $need[ $vendor[1] ] ) ) {
				wp_enqueue_script( $handle, rts_asset( $vendor[0] ), [], null, $args );
				$deps[] = $handle;
			}
		}

		// --- App scripts ---
		foreach ( self::SCRIPTS as $handle => $script ) {
			if ( 'all' === $script[1] || ! empty( $need[ $script[1] ] ) ) {
				wp_enqueue_script( $handle, rts_asset( $script[0] ), $deps, rts_asset_ver( $script[0] ), $args );
			}
		}

		// Base URL for the libraries the app fetches on demand (lightGallery, model-viewer).
		wp_add_inline_script( 'rts-global', 'window.reatissThemeUrl = ' . wp_json_encode( trailingslashit( RTS_URI ) ) . ';', 'before' );

		wp_localize_script(
			'rts-global',
			'reatiss',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'rts_nonce' ),
				'homeUrl' => RTS_HOME_URL,
			]
		);
	}
}

new RTS_Theme();

/**
 * The header button used to be a text field that always opened the
 * “Consultation” popup; now it is a link group (header_cta). Once, carry the
 * old text over with the popup as its link — on a site imported from an older
 * export too.
 */
class RTS_Header_Cta_Migration {

	private const DONE = 'rts_header_cta_migrated_v1';

	public function __construct() {
		add_action( 'acf/init', [ $this, 'run' ], 20 );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1 );

		// The group's `label` is stored as options_header_cta_label — the old field's name.
		$label = get_option( 'options_header_cta_label' );

		if ( false === $label || ! function_exists( 'update_field' ) ) {
			return;
		}

		update_field(
			'field_8b055c68102a8611',
			[
				'field_360bcde1522deffc' => (string) $label,
				'field_f70df8657e15d568' => '' === trim( (string) $label ) ? 'none' : 'modal',
			],
			'option'
		);
	}
}

new RTS_Header_Cta_Migration();
