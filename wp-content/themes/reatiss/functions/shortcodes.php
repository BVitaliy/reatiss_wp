<?php
/**
 * Editor inserts — the markup's article blocks as shortcodes, so editors can
 * place them anywhere in classic-editor / ACF WYSIWYG content:
 *
 *   [rts_cta title="Typical cycle time is 3-4 months." button="Process Report Example" url="https://…" size="big"]
 *   [gallery type="slider" ids="…"]                     — .article-slider (app-article.js);
 *     WordPress's own “Create gallery” with “Display as: Slider” writes it
 *     ([rts_slider ids="…"] from earlier content still works)
 *   YouTube: a link on its own line → the player (oEmbed, youtube-nocookie);
 *     old [rts_youtube url="…"] renders the same player
 *
 * Editor output is printed through rts_editor_content(): wp_kses_post plus
 * the tags these inserts need (picture / source, the inline SVG icon, the
 * video button's data attribute).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Shortcodes {

	public function __construct() {
		add_shortcode( 'rts_cta', [ $this, 'cta' ] );
		add_shortcode( 'rts_slider', [ $this, 'slider' ] );
		add_shortcode( 'rts_youtube', [ $this, 'youtube' ] );
		add_filter( 'post_gallery', [ $this, 'gallery_slider' ], 10, 2 );
		add_filter( 'embed_oembed_html', [ $this, 'youtube_embed' ], 10, 2 );
		add_action( 'print_media_templates', [ $this, 'gallery_setting' ] );

		// Post / event content: shortcodes run at 11, unwrap right after.
		add_filter( 'the_content', 'rts_unwrap_inserts', 12 );
		// After shortcodes — tables printed by them are wrapped as well.
		add_filter( 'the_content', 'rts_wrap_tables', 20 );
		add_filter( 'the_content', 'rts_label_links', 21 );
	}

	/**
	 * Highlighted note with a button (.article-cta).
	 *
	 * @param array|string $atts Attributes.
	 */
	public function cta( $atts ): string {
		$atts = shortcode_atts(
			[
				'title'  => '',
				'button' => '',
				'url'    => '',
				'new'    => '',
				'size'   => '',
			],
			$atts,
			'rts_cta'
		);

		// size="big" — the article's h4 heading (Figma 7999:18510), else the h5 note.
		$big = 'big' === $atts['size'];

		$link = '' !== $atts['url'] ? [
			'url'     => rts_resolve_anchor( $atts['url'] ),
			'new_tab' => '' !== $atts['new'] || str_ends_with( strtolower( $atts['url'] ), '.pdf' ),
			'modal'   => '#consultation' === $atts['url'] ? 'consultation' : '',
		] : null;

		ob_start();
		?>
		<div class="article-cta">
			<?php if ( '' !== $atts['title'] ) { ?>
				<?php if ( $big ) { ?>
					<h2 class="article-cta__title h4"><?php echo esc_html( $atts['title'] ); ?></h2>
				<?php } else { ?>
					<p class="article-cta__title h5"><?php echo esc_html( $atts['title'] ); ?></p>
				<?php } ?>
			<?php } ?>
			<?php if ( $link && '' !== $atts['button'] ) { ?>
				<a class="btn btn--primary"<?php echo rts_link_attrs( $link, $atts['button'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>>
					<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
					<?php echo esc_html( $atts['button'] ); ?>
				</a>
			<?php } ?>
		</div>
		<?php
		return $this->compact( (string) ob_get_clean() );
	}

	/**
	 * Old [rts_slider ids="…"] inserts — still rendered; new sliders are
	 * WordPress galleries with “Display as: Slider” (gallery_slider()).
	 *
	 * @param array|string $atts Attributes.
	 */
	public function slider( $atts ): string {
		$atts = shortcode_atts( [ 'ids' => '' ], $atts, 'rts_slider' );

		return rts_slider_html( explode( ',', (string) $atts['ids'] ) );
	}

	/**
	 * [gallery type="slider" ids="…"] → the markup's image slider.
	 *
	 * @param string $output   Gallery HTML (empty = WP renders its own).
	 * @param array  $attr     Shortcode attributes.
	 */
	public function gallery_slider( $output, $attr ): string {
		if ( 'slider' !== ( $attr['type'] ?? '' ) || empty( $attr['ids'] ) ) {
			return (string) $output;
		}

		return rts_slider_html( explode( ',', (string) $attr['ids'] ) );
	}

	/**
	 * Media modal → Create gallery: a “Display as: Grid / Slider” setting.
	 */
	public function gallery_setting(): void {
		?>
		<script type="text/html" id="tmpl-rts-gallery-type">
			<span class="setting">
				<label for="rts-gallery-type" class="name"><?php esc_html_e( 'Display as', 'reatiss' ); ?></label>
				<select id="rts-gallery-type" data-setting="type">
					<option value="grid"><?php esc_html_e( 'Grid', 'reatiss' ); ?></option>
					<option value="slider"><?php esc_html_e( 'Slider', 'reatiss' ); ?></option>
				</select>
			</span>
		</script>
		<script>
			jQuery( function () {
				if ( ! window.wp || ! wp.media || ! wp.media.view.Settings.Gallery ) {
					return;
				}
				// "grid" is the default, so a grid gallery's shortcode stays as WordPress writes it.
				wp.media.gallery.defaults.type = 'grid';
				wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend( {
					template: function ( view ) {
						return wp.media.template( 'gallery-settings' )( view ) + wp.media.template( 'rts-gallery-type' )( view );
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Old [rts_youtube url="…"] inserts — rendered as the same player a pasted
	 * YouTube link gets (videos are now just a link on its own line).
	 *
	 * @param array|string $atts Attributes.
	 */
	public function youtube( $atts ): string {
		$atts = shortcode_atts(
			[
				'url'   => '',
				'image' => '',
				'label' => '',
			],
			$atts,
			'rts_youtube'
		);

		if ( '' === $atts['url'] ) {
			return '';
		}

		$html = wp_oembed_get( $atts['url'] );

		return $html ? $this->compact( $this->nocookie( (string) $html ) ) : '';
	}

	/**
	 * A YouTube link pasted on its own line → the player, privacy-enhanced
	 * (youtube-nocookie.com: no cookies until the video plays). The size comes
	 * from CSS (.article-content iframe: full width, 16:9).
	 *
	 * @param string $html oEmbed HTML.
	 * @param string $url  Embedded URL.
	 */
	public function youtube_embed( $html, $url ): string {
		$html = (string) $html;

		if ( ! preg_match( '#(youtube\.com|youtu\.be)/#i', (string) $url ) ) {
			return $html;
		}

		return $this->nocookie( $html );
	}

	/**
	 * @param string $html Player iframe.
	 */
	private function nocookie( string $html ): string {
		$html = str_replace( [ 'https://www.youtube.com/embed/', 'https://youtube.com/embed/' ], 'https://www.youtube-nocookie.com/embed/', $html );

		return (string) preg_replace( '/\s(width|height)="\d+"/', '', $html );
	}

	/**
	 * One line between markers. wpautop runs before shortcodes and wraps a
	 * shortcode that shares a paragraph with others in <p> … <br />; the
	 * markers let rts_editor_content() strip exactly those wrappers.
	 *
	 * @param string $html Markup.
	 */
	private function compact( string $html ): string {
		return '<!--rts-insert-->' . trim( (string) preg_replace( '/>\s+</', '><', $html ) ) . '<!--/rts-insert-->';
	}
}

new RTS_Shortcodes();

/**
 * The markup's image slider (.article-slider) for media library IDs.
 *
 * @param array $ids Attachment IDs in order.
 */
function rts_slider_html( array $ids ): string {
	$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );

	if ( empty( $ids ) ) {
		return '';
	}

	rts_prime_posts( $ids );

	ob_start();
	?>
	<div class="article-slider swiper js-article-slider" aria-label="<?php esc_attr_e( 'Images', 'reatiss' ); ?>">
		<div class="swiper-wrapper">
			<?php foreach ( $ids as $id ) { ?>
				<div class="swiper-slide">
					<?php rts_picture( $id, null, [ 'sizes' => '(max-width: 1236px) 100vw, 1236px' ], 'large' ); ?>
				</div>
			<?php } ?>
		</div>
		<div class="swiper-pagination article-slider__pagination"></div>
	</div>
	<?php
	// One line between markers: rts_unwrap_inserts() strips the <p> wpautop adds.
	return '<!--rts-insert-->' . trim( (string) preg_replace( '/>\s+</', '><', (string) ob_get_clean() ) ) . '<!--/rts-insert-->';
}

/**
 * Editor HTML for output: wp_kses_post plus the tags the inserts above use.
 *
 * ACF has already run its content filters (paragraphs, shortcodes) on the
 * field value; this only escapes.
 *
 * @param string $html Formatted WYSIWYG value.
 */
function rts_editor_content( string $html ): string {
	static $allowed = null;

	if ( null === $allowed ) {
		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['picture'] = [ 'class' => true ];
		// Embedded players (a YouTube / Vimeo link pasted on its own line).
		$allowed['iframe']  = [
			'src'             => true,
			'title'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
			'loading'         => true,
		];
		$allowed['source']  = [
			'srcset' => true,
			'media'  => true,
			'type'   => true,
			'sizes'  => true,
		];
		$allowed['button']  = array_merge(
			$allowed['button'] ?? [],
			[
				'class'        => true,
				'type'         => true,
				'data-youtube' => true,
				'aria-label'   => true,
			]
		);
		$allowed['svg']     = [
			'class'       => true,
			'width'       => true,
			'height'      => true,
			'viewbox'     => true,
			'fill'        => true,
			'xmlns'       => true,
			'aria-hidden' => true,
			'focusable'   => true,
		];
		$allowed['g']       = [ 'fill' => true ];
		$allowed['path']    = [
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'fill-rule'       => true,
			'clip-rule'       => true,
		];
		$allowed['img']     = array_merge(
			$allowed['img'] ?? [],
			[
				'srcset'        => true,
				'sizes'         => true,
				'loading'       => true,
				'decoding'      => true,
				'fetchpriority' => true,
			]
		);
	}

	return rts_label_links( wp_kses( rts_unwrap_inserts( $html ), $allowed ) );
}

/**
 * Editor links get an aria-label — their text (an image link: the image's
 * alt), “opens in a new tab” when so — unless the editor set one.
 *
 * @param string $html Editor HTML.
 */
function rts_label_links( string $html ): string {
	if ( ! str_contains( $html, '<a' ) ) {
		return $html;
	}

	return (string) preg_replace_callback(
		'#<a\b(?![^>]*\baria-label=)([^>]*)>(.*?)</a>#is',
		static function ( array $m ): string {
			$text = trim( html_entity_decode( wp_strip_all_tags( $m[2] ), ENT_QUOTES, 'UTF-8' ) );

			if ( '' === $text && preg_match( '#\balt=(["\'])(.*?)\1#i', $m[2], $alt ) ) {
				$text = html_entity_decode( $alt[2], ENT_QUOTES, 'UTF-8' );
			}

			return '<a' . $m[1] . rts_aria_label( $text, (bool) preg_match( '#\btarget=(["\']?)_blank#i', $m[1] ) ) . '>' . $m[2] . '</a>';
		},
		$html
	);
}

/**
 * Removes the <p> / <br> wpautop puts around the block inserts (and the markers).
 *
 * @param string $html Formatted editor HTML.
 */
function rts_unwrap_inserts( string $html ): string {
	return (string) preg_replace(
		[
			'#<p>\s*<!--rts-insert-->#',
			'#<!--/rts-insert-->\s*(?:<br\s*/?>\s*)?</p>#',
			'#<!--/rts-insert-->\s*<br\s*/?>#',
			'#<!--/?rts-insert-->#',
		],
		[ '<!--rts-insert-->', '<!--/rts-insert-->', '<!--/rts-insert-->', '' ],
		$html
	);
}

/**
 * One-time move of [rts_slider ids="…"] into what editors can manage without
 * IDs: in a “Content” block → its “Slider photos” field; anywhere else (post
 * content, other editor fields) → [gallery type="slider" ids="…"], which the
 * media modal edits. Runs on admin_init, so an imported site upgrades itself.
 */
class RTS_Slider_Migration {

	private const DONE = 'rts_slider_migrated_v1';

	private const PATTERN = '/\[rts_slider\s+ids="([\d,\s]+)"\s*\]/';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'run' ] );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1, false );

		global $wpdb;

		$like = '%' . $wpdb->esc_like( '[rts_slider' ) . '%';

		// Post content (articles, events, text pages).
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		foreach ( $posts as $post ) {
			$wpdb->update( $wpdb->posts, [ 'post_content' => $this->to_gallery( $post->post_content ) ], [ 'ID' => $post->ID ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( (int) $post->ID );
		}

		// Builder fields.
		$metas = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		foreach ( $metas as $meta ) {
			$value = (string) $meta->meta_value;

			if ( preg_match( '/^page_builder_(\d+)_text$/', $meta->meta_key, $m ) ) {
				$layouts = get_post_meta( (int) $meta->post_id, 'page_builder', true );

				if ( is_array( $layouts ) && 'content' === ( $layouts[ (int) $m[1] ] ?? '' ) && preg_match( self::PATTERN, $value, $found ) ) {
					$base = 'page_builder_' . $m[1] . '_slider';
					$ids  = array_values( array_filter( array_map( 'trim', explode( ',', $found[1] ) ) ) );

					update_post_meta( (int) $meta->post_id, $base, $ids );
					update_post_meta( (int) $meta->post_id, '_' . $base, 'field_' . substr( sha1( 'group_rts_page_builder/page_builder/content/slider' ), 0, 16 ) );

					$value = trim( (string) preg_replace( self::PATTERN, '', $value, 1 ) );
				}
			}

			update_post_meta( (int) $meta->post_id, $meta->meta_key, $this->to_gallery( $value ) );
		}
	}

	/**
	 * @param string $html Editor HTML.
	 */
	private function to_gallery( string $html ): string {
		return (string) preg_replace( self::PATTERN, '[gallery type="slider" ids="$1"]', $html );
	}
}

/**
 * One-time move of [rts_youtube url="…" image="…"] to a plain YouTube link on
 * its own line (WordPress embeds it as the player).
 */
class RTS_Youtube_Migration {

	private const DONE = 'rts_youtube_migrated_v1';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'run' ] );
	}

	public function run(): void {
		if ( get_option( self::DONE ) ) {
			return;
		}

		update_option( self::DONE, 1, false );

		global $wpdb;

		$like = '%' . $wpdb->esc_like( '[rts_youtube' ) . '%';

		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s", $like ) ) as $post ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $wpdb->posts, [ 'post_content' => $this->to_link( $post->post_content ) ], [ 'ID' => $post->ID ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( (int) $post->ID );
		}

		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) ) as $meta ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			update_post_meta( (int) $meta->post_id, $meta->meta_key, $this->to_link( (string) $meta->meta_value ) );
		}
	}

	/**
	 * @param string $html Editor HTML.
	 */
	private function to_link( string $html ): string {
		return (string) preg_replace( '/\[rts_youtube\s+[^\]]*url="([^"]+)"[^\]]*\]/', "\n$1\n", $html );
	}
}

if ( is_admin() ) {
	new RTS_Slider_Migration();
	new RTS_Youtube_Migration();
}

/**
 * Editor tables scroll sideways on phones inside .article-table.
 *
 * @param string $content Post content.
 */
function rts_wrap_tables( $content ): string {
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
