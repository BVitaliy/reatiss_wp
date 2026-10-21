<?php
/**
 * Stats — Figma 7967:9028 (+ line 7976:18082).
 * Dark section: on the home page the whole page turns dark (app-page-theme.js),
 * elsewhere the section is dark on its own. Numbers count up (data-count, app-stats.js),
 * each card has a cursor-reactive dot grid.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

if ( empty( $args['lead'] ) && empty( $rts_items ) ) {
	return;
}
?>
<section class="home-stats js-stats" data-page-theme="dark">

	<div class="run-line home-stats__line js-run-line" data-duration="10" data-start="0.57" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 823 520" preserveAspectRatio="xMinYMin meet" fill="none">
			<path class="run-line__path js-run-path" d="M822.5 0V282.5C822.5 314.533 796.533 340.5 764.5 340.5H418.486C397.765 340.5 378.618 351.555 368.257 369.5L298.686 490C288.325 507.945 269.178 519 248.457 519H-213" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 43%; --y: 77.2%;"></span>
	</div>

	<div class="container">
		<?php if ( ! empty( $args['lead'] ) ) { ?>
			<p class="home-stats__lead h3"><?php echo esc_html( $args['lead'] ); ?></p>
		<?php } ?>

		<?php if ( ! empty( $rts_items ) ) { ?>
		<ul class="home-stats__list">
			<?php
			foreach ( $rts_items as $rts_item ) {
				$rts_value = (string) ( $rts_item['value'] ?? '' );
				$rts_count = (int) preg_replace( '/\D+/', '', $rts_value );
				?>
				<li class="stat-card">
					<span class="stat-card__dots js-stat-dots" aria-hidden="true"></span>
					<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
						<div class="stat-card__media js-draw">
							<?php rts_picture( $rts_item['image'], null, [ 'alt' => '' ], 'large' ); ?>
						</div>
					<?php } ?>
					<div class="stat-card__body">
						<?php if ( '' !== $rts_value ) { ?>
							<p class="stat-card__num stat-num"<?php echo $rts_count ? ' data-count="' . esc_attr( (string) $rts_count ) . '"' : ''; ?><?php echo $rts_count >= 1000 && ! str_contains( $rts_value, ' ' ) ? ' data-count-sep=""' : ''; ?>><?php echo esc_html( $rts_value ); ?></p>
						<?php } ?>
						<?php if ( ! empty( $rts_item['label'] ) ) { ?>
							<p class="stat-card__label h5"><?php echo esc_html( $rts_item['label'] ); ?></p>
						<?php } ?>
						<?php if ( ! empty( $rts_item['text'] ) ) { ?>
							<p class="stat-card__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
						<?php } ?>
					</div>
				</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
