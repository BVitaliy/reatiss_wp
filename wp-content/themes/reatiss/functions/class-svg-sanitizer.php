<?php
/**
 * SVG sanitizer.
 *
 * Uploaded SVGs are inlined into the page so CSS can recolour them, which means
 * an unsanitized file is a stored-XSS vector: SVG carries <script>, on* handlers,
 * external <use>, foreignObject and javascript: URIs.
 *
 * This runs an allow-list pass — anything not explicitly permitted is removed —
 * and is applied twice: once on upload (so the file on disk is already clean) and
 * once on output (so files uploaded before this existed, or dropped in over FTP,
 * cannot slip through).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Svg_Sanitizer {

	/**
	 * Elements allowed to survive. Presentation and shape elements only.
	 */
	private const ALLOWED_TAGS = [
		'svg', 'g', 'defs', 'symbol', 'use', 'title', 'desc',
		'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
		'text', 'tspan', 'textpath',
		'clippath', 'mask', 'pattern', 'marker',
		'lineargradient', 'radialgradient', 'stop',
		'filter', 'fegaussianblur', 'feoffset', 'feblend', 'femerge', 'femergenode',
		'fecolormatrix', 'fecomposite', 'feflood', 'fedropshadow',
		'style',
	];

	/**
	 * Attributes allowed on any element.
	 */
	private const ALLOWED_ATTRS = [
		'id', 'class', 'style', 'transform', 'viewbox', 'xmlns', 'xmlns:xlink',
		'version', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
		'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'dx', 'dy',
		'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width',
		'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray', 'stroke-dashoffset',
		'stroke-opacity', 'stroke-miterlimit', 'opacity', 'color',
		'clip-path', 'clip-rule', 'mask', 'filter', 'offset', 'stop-color',
		'stop-opacity', 'gradientunits', 'gradienttransform', 'patternunits',
		'maskunits', 'markerwidth', 'markerheight', 'orient', 'refx', 'refy',
		'preserveaspectratio', 'text-anchor', 'font-family', 'font-size',
		'font-weight', 'letter-spacing', 'dominant-baseline', 'baseline-shift',
		'in', 'in2', 'result', 'stddeviation', 'mode', 'type', 'values',
		'operator', 'flood-color', 'flood-opacity', 'vector-effect',
		'aria-hidden', 'aria-label', 'role', 'focusable',
		'href', 'xlink:href',
	];

	/**
	 * Cleans an SVG string. Returns '' when the input is not usable SVG.
	 *
	 * @param string $svg Raw file contents.
	 */
	public function sanitize( string $svg ): string {
		if ( '' === trim( $svg ) ) {
			return '';
		}

		// Reject doctype/entity declarations outright rather than trying to clean
		// them — they only appear in XXE payloads in this context.
		if ( preg_match( '/<!(?:DOCTYPE|ENTITY)/i', $svg ) ) {
			return '';
		}

		$svg = $this->strip_encoded_scripts( $svg );

		$previous = libxml_use_internal_errors( true );

		$dom                      = new DOMDocument();
		$dom->preserveWhiteSpace  = false;
		$dom->formatOutput        = false;

		// LIBXML_NONET blocks network fetches; NOENT keeps entities from expanding.
		$loaded = $dom->loadXML( $svg, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOERROR | LIBXML_NOWARNING );

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $dom->documentElement ) {
			return '';
		}

		if ( 'svg' !== strtolower( $dom->documentElement->nodeName ) ) {
			return '';
		}

		$this->clean_node( $dom->documentElement );

		$output = $dom->saveXML( $dom->documentElement );

		return is_string( $output ) ? $output : '';
	}

	/**
	 * Cleans a file in place. Returns true when the file is safe afterwards.
	 *
	 * @param string $path Absolute path to an .svg file.
	 */
	public function sanitize_file( string $path ): bool {
		if ( ! is_readable( $path ) || ! is_writable( $path ) ) {
			return false;
		}

		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.

		if ( false === $raw ) {
			return false;
		}

		$clean = $this->sanitize( $raw );

		if ( '' === $clean ) {
			return false;
		}

		return false !== file_put_contents( $path, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- local file.
	}

	/**
	 * Removes obviously hostile constructs before the DOM pass.
	 *
	 * Base64 or entity-encoded payloads survive parsing as plain text, so the
	 * literal strings are cut first.
	 *
	 * @param string $svg Raw SVG.
	 */
	private function strip_encoded_scripts( string $svg ): string {
		$patterns = [
			'#<\s*script[^>]*>.*?<\s*/\s*script\s*>#is',
			'#<\s*script[^>]*/?>#is',
			'#<\s*foreignObject[^>]*>.*?<\s*/\s*foreignObject\s*>#is',
			'#<\s*(?:iframe|object|embed|handler|set|animate)[^>]*>#is',
		];

		return (string) preg_replace( $patterns, '', $svg );
	}

	/**
	 * Recursively strips disallowed elements and attributes.
	 *
	 * @param DOMElement $node Element to clean.
	 */
	private function clean_node( DOMElement $node ): void {
		// Walk children backwards: removing a node shifts the live NodeList.
		for ( $i = $node->childNodes->length - 1; $i >= 0; $i-- ) {
			$child = $node->childNodes->item( $i );

			if ( $child instanceof DOMElement ) {
				if ( ! in_array( strtolower( $child->nodeName ), self::ALLOWED_TAGS, true ) ) {
					$node->removeChild( $child );
					continue;
				}

				$this->clean_node( $child );
				continue;
			}

			// Comments can hide markup that some parsers resurrect.
			if ( $child instanceof DOMComment || $child instanceof DOMProcessingInstruction ) {
				$node->removeChild( $child );
			}
		}

		$this->clean_attributes( $node );
	}

	/**
	 * @param DOMElement $node Element whose attributes to filter.
	 */
	private function clean_attributes( DOMElement $node ): void {
		if ( ! $node->hasAttributes() ) {
			return;
		}

		for ( $i = $node->attributes->length - 1; $i >= 0; $i-- ) {
			$attr = $node->attributes->item( $i );

			if ( ! $attr ) {
				continue;
			}

			$name  = strtolower( $attr->nodeName );
			$value = $attr->nodeValue;

			// Every event handler starts with "on".
			if ( str_starts_with( $name, 'on' ) || ! in_array( $name, self::ALLOWED_ATTRS, true ) ) {
				$node->removeAttribute( $attr->nodeName );
				continue;
			}

			// href/xlink:href may only point inside the document (<use href="#id">).
			if ( in_array( $name, [ 'href', 'xlink:href' ], true ) && ! str_starts_with( trim( (string) $value ), '#' ) ) {
				$node->removeAttribute( $attr->nodeName );
				continue;
			}

			// style may not smuggle url() or expressions.
			if ( 'style' === $name && preg_match( '#(url\s*\(|expression\s*\(|javascript:|@import)#i', (string) $value ) ) {
				$node->removeAttribute( $attr->nodeName );
			}
		}
	}
}
