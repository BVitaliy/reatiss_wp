<?php
/**
 * Editor inserts — the markup's article blocks as shortcodes, so editors can
 * place them anywhere in classic-editor / ACF WYSIWYG content:
 *
 *   [rts_cta title="Typical cycle time is 3-4 months." button="Process Report Example" url="https://…" size="big"]
 *   [rts_slider ids="12,13,14"]                         — .article-slider (app-article.js)
 *   [rts_youtube url="https://youtu.be/…" image="15"]   — .article-video facade (app-article.js)
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

		// Post / event content: shortcodes run at 11, unwrap right after.
		add_filter( 'the_content', 'rts_unwrap_inserts', 12 );
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
				<a class="btn btn--primary"<?php echo rts_link_attrs( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>>
					<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
					<?php echo esc_html( $atts['button'] ); ?>
				</a>
			<?php } ?>
		</div>
		<?php
		return $this->compact( (string) ob_get_clean() );
	}

	/**
	 * Image slider (.article-slider) from media library IDs.
	 *
	 * @param array|string $atts Attributes.
	 */
	public function slider( $atts ): string {
		$atts = shortcode_atts( [ 'ids' => '' ], $atts, 'rts_slider' );
		$ids  = array_filter( array_map( 'absint', explode( ',', (string) $atts['ids'] ) ) );

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
		return $this->compact( (string) ob_get_clean() );
	}

	/**
	 * YouTube facade (.article-video): cover image → youtube-nocookie iframe on click.
	 *
	 * @param array|string $atts Attributes.
	 */
	public function youtube( $atts ): string {
		$atts = shortcode_atts(
			[
				'url'   => '',
				'image' => '',
				'label' => __( 'Play video', 'reatiss' ),
			],
			$atts,
			'rts_youtube'
		);

		if ( '' === $atts['url'] ) {
			return '';
		}

		ob_start();
		?>
		<button class="article-video js-youtube" type="button" data-youtube="<?php echo esc_attr( $atts['url'] ); ?>" aria-label="<?php echo esc_attr( $atts['label'] ); ?>">
			<?php rts_picture( absint( $atts['image'] ), null, [ 'alt' => '' ], 'large' ); ?>
			<span class="article-video__play"><?php rts_icon( 'icons/youtube.svg' ); ?></span>
		</button>
		<?php
		return $this->compact( (string) ob_get_clean() );
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

	return wp_kses( rts_unwrap_inserts( $html ), $allowed );
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
