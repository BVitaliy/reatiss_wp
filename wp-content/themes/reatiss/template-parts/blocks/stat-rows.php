<?php
/**
 * Stat rows — Figma 8271:40694 (expertise, dark): lead on top, text on the
 * left, number rows on the right (count-up, app-stats.js); cursor dot grid.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

if ( empty( $args['lead'] ) && empty( $args['text'] ) && empty( $rts_items ) ) {
	return;
}
?>
<section class="stats-rows section-dark js-stats">
	<div class="dot-field js-dot-field" aria-hidden="true"></div>
	<div class="container stats-rows__grid">
		<?php if ( ! empty( $args['lead'] ) ) { ?>
			<p class="stats-rows__lead h3"><?php echo esc_html( $args['lead'] ); ?></p>
		<?php } ?>
		<?php if ( ! empty( $args['text'] ) ) { ?>
			<p class="stats-rows__text text-lg"><?php echo esc_html( $args['text'] ); ?></p>
		<?php } ?>

		<?php if ( ! empty( $rts_items ) ) { ?>
		<ul class="stat-rows">
			<?php
			foreach ( $rts_items as $rts_item ) {
				$rts_value = (string) ( $rts_item['value'] ?? '' );
				$rts_count = (int) preg_replace( '/\D+/', '', $rts_value );
				?>
				<li class="stat-row">
					<span class="step-line"></span>
					<div class="stat-row__body">
						<?php if ( '' !== $rts_value ) { ?>
							<p class="stat-row__num stat-num"<?php echo $rts_count ? ' data-count="' . esc_attr( (string) $rts_count ) . '"' : ''; ?>><?php echo esc_html( $rts_value ); ?></p>
						<?php } ?>
						<?php if ( ! empty( $rts_item['label'] ) ) { ?>
							<h3 class="stat-row__label h5"><?php echo esc_html( $rts_item['label'] ); ?></h3>
						<?php } ?>
						<?php if ( ! empty( $rts_item['text'] ) ) { ?>
							<p class="stat-row__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
						<?php } ?>
					</div>
				</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
