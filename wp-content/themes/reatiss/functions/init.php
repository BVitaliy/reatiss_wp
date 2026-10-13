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
	 * Vendor scripts (inc/_bottom.php order). The theme's own jQuery build
	 * replaces the core one — the markup was written and tested against it.
	 * lightGallery and model-viewer are not here: app-gallery.js / app-model.js
	 * fetch them on demand.
	 */
	private const VENDORS = [
		'jquery'             => 'js/vendors/jquery.min.js',
		'rts-swiper'         => 'js/vendors/swiper-bundle.min.js',
		'rts-gsap'           => 'js/vendors/gsap.min.js',
		'rts-scroll-trigger' => 'js/vendors/ScrollTrigger.min.js',
		'rts-split-text'     => 'js/vendors/SplitText.min.js',
		'rts-motion-path'    => 'js/vendors/MotionPathPlugin.min.js',
		'rts-draw-svg'       => 'js/vendors/DrawSVGPlugin.min.js',
		'rts-lenis'          => 'js/vendors/lenis.min.js',
	];

	/**
	 * App scripts (inc/_bottom.php order).
	 */
	private const SCRIPTS = [
		'rts-global'      => 'js/app-global.js',
		'rts-modals'      => 'js/app-modals.js',
		'rts-header'      => 'js/app-header.js',
		'rts-swiper-init' => 'js/app-swiper.js',
		'rts-faq'         => 'js/app-faq.js',
		'rts-form'        => 'js/app-form.js',
		'rts-cf7'         => 'js/app-cf7.js',
		'rts-wp'          => 'js/app-wp.js',
		'rts-load-more'   => 'js/app-load-more.js',
		'rts-select'      => 'js/app-select.js',
		'rts-hero'        => 'js/app-hero.js',
		'rts-lines'       => 'js/app-lines.js',
		'rts-stats'       => 'js/app-stats.js',
		'rts-solutions'   => 'js/app-solutions.js',
		'rts-titles'      => 'js/app-titles.js',
		'rts-draw'        => 'js/app-draw.js',
		'rts-page-theme'  => 'js/app-page-theme.js',
		'rts-video'       => 'js/app-video.js',
		'rts-parallax'    => 'js/app-parallax.js',
		'rts-more-text'   => 'js/app-more-text.js',
		'rts-article'     => 'js/app-article.js',
		'rts-map'         => 'js/app-map.js',
		'rts-statement'   => 'js/app-statement.js',
		'rts-gallery'     => 'js/app-gallery.js',
		'rts-model'       => 'js/app-model.js',
		'rts-about'       => 'js/app-about.js',
		'rts-lab'         => 'js/app-lab.js',
		'rts-error'       => 'js/app-error.js',
		'rts-animations'  => 'js/app-animations.js',
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
	}

	public function setup(): void {
		load_theme_textdomain( 'reatiss', RTS_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			[
				'header_menu'   => __( 'Головне меню (шапка і мобільне меню)', 'reatiss' ),
				'footer_menu_1' => __( 'Футер — колонка 1', 'reatiss' ),
				'footer_menu_2' => __( 'Футер — колонка 2', 'reatiss' ),
				'footer_menu_3' => __( 'Футер — колонка 3', 'reatiss' ),
				'footer_menu_4' => __( 'Футер — колонка 4', 'reatiss' ),
				'footer_menu_5' => __( 'Футер — колонка 5', 'reatiss' ),
				'legal_menu'    => __( 'Футер — юридичні посилання', 'reatiss' ),
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

	public function enqueue_assets(): void {
		// The block library CSS is dead weight — this theme renders no blocks.
		wp_dequeue_style( 'wp-block-library' );
		wp_deregister_style( 'wp-block-library' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );

		// --- Styles (inc/_top.php order) ---
		wp_enqueue_style( 'rts-swiper', rts_asset( 'css/vendors/swiper-bundle.min.css' ), [], null );
		wp_enqueue_style( 'rts-main', rts_asset( 'css/main.css' ), [ 'rts-swiper' ], rts_asset_ver( 'css/main.css' ) );
		wp_enqueue_style( 'rts-style', rts_asset( 'css/style.css' ), [ 'rts-main' ], rts_asset_ver( 'css/style.css' ) );

		$args = [
			'strategy'  => 'defer',
			'in_footer' => true,
		];

		// --- Vendor scripts ---
		wp_deregister_script( 'jquery' );

		$deps = [];
		foreach ( self::VENDORS as $handle => $path ) {
			wp_enqueue_script( $handle, rts_asset( $path ), [], null, $args );
			$deps[] = $handle;
		}

		// --- App scripts ---
		foreach ( self::SCRIPTS as $handle => $path ) {
			wp_enqueue_script( $handle, rts_asset( $path ), $deps, rts_asset_ver( $path ), $args );
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
