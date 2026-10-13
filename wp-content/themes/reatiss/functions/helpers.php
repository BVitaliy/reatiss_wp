<?php
/**
 * Template helpers.
 *
 * These are called from templates rather than hooked, so they stay plain
 * functions.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme asset URL.
 *
 * @param string $path Path relative to the theme root, e.g. 'img/icons/close.svg'.
 */
function rts_asset( string $path ): string {
	return RTS_URI . '/' . ltrim( $path, '/' );
}

/**
 * Cache-busting version for a theme asset: its modification time, so every
 * edit to a CSS/JS file changes the URL and browsers fetch the new copy.
 * Falls back to RTS_VER if the file is missing.
 *
 * @param string $path Path relative to the theme root.
 */
function rts_asset_ver( string $path ): string {
	$file = RTS_DIR . '/' . ltrim( $path, '/' );

	return is_readable( $file ) ? (string) filemtime( $file ) : RTS_VER;
}

/**
 * Inline an SVG from the theme's img/ directory.
 *
 * The static markup did `include 'img/icons/logo.svg'`; this is the theme
 * equivalent. Files are read once per request and cached in memory.
 *
 * @param string $path Path relative to img/, e.g. 'icons/logo.svg'.
 */
function rts_icon( string $path ): void {
	static $cache = [];

	if ( ! isset( $cache[ $path ] ) ) {
		if ( str_contains( $path, '..' ) ) {
			return;
		}

		$file = RTS_DIR . '/img/' . ltrim( $path, '/' );

		if ( ! is_readable( $file ) || 'svg' !== strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
			$cache[ $path ] = '';
		} else {
			$cache[ $path ] = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
		}
	}

	if ( ! empty( $cache[ $path ] ) ) {
		echo $cache[ $path ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG file.
	}
}

/**
 * The design's arrow button: `.btn` with the arrow tile before the label.
 *
 * Prints nothing without a label or a usable link.
 *
 * @param mixed  $btn   Button group field value ({ label, type, page, url, new_tab }).
 * @param string $class Classes on the <a>, e.g. 'btn btn--primary'.
 */
function rts_button( $btn, string $class = 'btn btn--primary' ): void {
	$label = is_array( $btn ) ? trim( (string) ( $btn['label'] ?? '' ) ) : '';
	$link  = rts_link( $btn );

	if ( '' === $label || ! $link ) {
		return;
	}
	?>
	<a class="<?php echo esc_attr( $class ); ?>"<?php echo rts_link_attrs( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>>
		<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
		<?php echo esc_html( $label ); ?>
	</a>
	<?php
}

/**
 * Would rts_button() print anything? Lets a template skip an empty wrapper.
 *
 * @param mixed $btn Button group field value.
 */
function rts_has_button( $btn ): bool {
	return is_array( $btn ) && '' !== trim( (string) ( $btn['label'] ?? '' ) ) && null !== rts_link( $btn );
}

/**
 * Permalink of the first page using a given page template
 * (map kept in functions/performance.php).
 *
 * @param string $template Template file relative to the theme, e.g. 'templates/visual.php'.
 */
function rts_template_url( string $template ): string {
	$page_id = rts_template_page_id( $template );

	return $page_id ? (string) get_permalink( $page_id ) : RTS_HOME_URL;
}

/**
 * Site settings value (ACF options page), cached per request.
 *
 * Several partials read the same option on one page (contacts in the header
 * and footer, the map key…); ACF does not cache option reads by itself.
 *
 * @param string $name Field name.
 * @return mixed Field value, or null when ACF is inactive.
 */
function rts_option( string $name ) {
	static $cache = [];

	if ( ! array_key_exists( $name, $cache ) ) {
		$cache[ $name ] = function_exists( 'get_field' ) ? get_field( $name, 'option' ) : null;
	}

	return $cache[ $name ];
}

/**
 * tel: link for a human-formatted phone number.
 *
 * @param string $phone E.g. '+38 (099) 999 99 99'.
 */
function rts_phone_href( string $phone ): string {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $phone );
}

/**
 * Splits a textarea value into trimmed, non-empty lines.
 *
 * @param mixed $value Textarea field value.
 * @return string[]
 */
function rts_lines( $value ): array {
	if ( empty( $value ) || ! is_string( $value ) ) {
		return [];
	}

	// /u matters: without it \R also matches byte 0x85 inside UTF-8 sequences.
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $value ) ) ) );
}

/**
 * Hash-only links ("#contact") point at front-page blocks. Off the front page
 * they must lead back to it, otherwise the click does nothing.
 *
 * @param string $url Link from a menu item or a text field.
 */
function rts_resolve_anchor( string $url ): string {
	if ( ! str_starts_with( $url, '#' ) || '#' === $url || is_front_page() ) {
		return $url;
	}

	return RTS_HOME_URL . $url;
}

/**
 * Appends a line to a theme log in RTS_LOG_DIR.
 *
 * @param string $channel Log file name without extension, e.g. 'telegram'.
 * @param string $message Message; context is appended as JSON.
 * @param array  $context Extra data.
 */
function rts_log( string $channel, string $message, array $context = [] ): void {
	if ( ! wp_mkdir_p( RTS_LOG_DIR ) ) {
		return;
	}

	$line = sprintf(
		"[%s] %s%s\n",
		wp_date( 'Y-m-d H:i:s' ),
		$message,
		! empty( $context ) ? ' ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE ) : ''
	);

	file_put_contents( RTS_LOG_DIR . '/' . sanitize_file_name( $channel ) . '.log', $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- append-only local log.
}

/**
 * Resolves a "link" field group: { type: none|page|custom|modal, page, url, new_tab }.
 *
 * Shared by every editor-configurable link and button: a page picked from the
 * site, any typed address (another site, an #anchor), or the consultation
 * popup (`modal`).
 *
 * @param mixed $link Group field value.
 * @return array{url:string,new_tab:bool,modal:string}|null Null when there is no usable link.
 */
function rts_link( $link ): ?array {
	if ( ! is_array( $link ) || empty( $link['type'] ) ) {
		return null;
	}

	$url   = '';
	$modal = '';

	if ( 'page' === $link['type'] && ! empty( $link['page'] ) ) {
		$url = (string) $link['page'];
	} elseif ( 'custom' === $link['type'] && ! empty( $link['url'] ) ) {
		$url = rts_resolve_anchor( trim( (string) $link['url'] ) );
	} elseif ( 'modal' === $link['type'] ) {
		$url   = '#';
		$modal = 'consultation';
	}

	if ( '' === $url ) {
		return null;
	}

	return [
		'url'     => $url,
		'new_tab' => 'custom' === $link['type'] && ! empty( $link['new_tab'] ),
		'modal'   => $modal,
	];
}

/**
 * href + target / popup attributes for a resolved link (rts_link()), escaped.
 *
 * @param array $link Resolved link.
 */
function rts_link_attrs( array $link ): string {
	$html = ' href="' . esc_url( $link['url'] ) . '"';

	if ( ! empty( $link['new_tab'] ) ) {
		$html .= ' target="_blank" rel="noopener noreferrer"';
	}

	if ( ! empty( $link['modal'] ) ) {
		$html .= ' data-modal-open="' . esc_attr( $link['modal'] ) . '"';
	}

	return $html;
}

/**
 * Title separator from Yoast's settings (Settings → Site basics), so titles
 * the theme builds match the ones Yoast builds. "—" when Yoast is off.
 */
function rts_title_separator(): string {
	if ( function_exists( 'YoastSEO' ) ) {
		$separator = YoastSEO()->helpers->options->get_title_separator();

		if ( ! empty( $separator ) ) {
			return html_entity_decode( (string) $separator, ENT_QUOTES, 'UTF-8' );
		}
	}

	return '—';
}

/**
 * Filled social links from Site Settings → Футер.
 *
 * @return array<int, array{icon:string,url:string,label:string}>
 */
function rts_socials(): array {
	$labels = [
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'linkedin'  => 'LinkedIn',
		'x'         => 'X',
	];
	$list   = [];
	$rows   = rts_option( 'socials' );

	foreach ( is_array( $rows ) ? $rows : [] as $row ) {
		$icon = (string) ( $row['icon'] ?? '' );

		if ( empty( $row['link'] ) || ! isset( $labels[ $icon ] ) ) {
			continue;
		}

		$list[] = [
			'icon'  => $icon,
			'url'   => (string) $row['link'],
			'label' => $labels[ $icon ],
		];
	}

	return $list;
}

/**
 * Header modifier: transparent over a dark banner, `header--light` elsewhere.
 * Templates that open with a dark banner hook `rts_header_dark` and return true.
 */
function rts_header_class(): string {
	return apply_filters( 'rts_header_dark', false ) ? '' : 'header--light';
}

/**
 * Prints a textarea value whose line breaks were saved as <br> (ACF
 * `new_lines: br`) — escaped, keeping only the <br>s.
 *
 * @param mixed $text Field value.
 */
function rts_br( $text ): void {
	echo wp_kses( (string) $text, [ 'br' => [] ] );
}
