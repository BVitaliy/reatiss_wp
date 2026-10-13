<?php
/**
 * 3D model — Figma 8275:46370. A .glb turned with mouse / finger in Google's
 * <model-viewer> (app-model.js, fetched near the viewport); the poster shows
 * until the model is ready and stays without WebGL / JS.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['model'] ) || ! rts_image_id( $args['poster'] ?? 0 ) ) {
	return;
}
?>
<section class="model-3d">
	<div class="container">
		<div class="model-3d__stage js-model" data-src="<?php echo esc_url( (string) $args['model'] ); ?>" data-alt="<?php echo esc_attr( (string) ( $args['alt'] ?? '' ) ); ?>">
			<?php
			ob_start();
			rts_picture( $args['poster'], $args['poster_mobile'] ?? 0, [ 'alt' => (string) ( $args['alt'] ?? '' ) ], 'full' );
			echo str_replace( '<picture>', '<picture class="model-3d__poster">', (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture().
			?>
			<div class="model-3d__zoom">
				<button class="btn-round model-3d__zoom-btn js-model-zoom" type="button" data-zoom="in" aria-label="<?php esc_attr_e( 'Zoom in', 'reatiss' ); ?>"><?php rts_icon( 'icons/plus.svg' ); ?></button>
				<button class="btn-round model-3d__zoom-btn js-model-zoom" type="button" data-zoom="out" aria-label="<?php esc_attr_e( 'Zoom out', 'reatiss' ); ?>"><?php rts_icon( 'icons/minus.svg' ); ?></button>
			</div>
		</div>
		<p class="model-3d__hint text-md">
			<span class="model-3d__hint-mouse"><?php esc_html_e( 'Rotate the model — click and drag', 'reatiss' ); ?></span>
			<span class="model-3d__hint-touch"><?php esc_html_e( 'Swipe sideways to rotate · pinch to zoom', 'reatiss' ); ?></span>
		</p>
	</div>
</section>
