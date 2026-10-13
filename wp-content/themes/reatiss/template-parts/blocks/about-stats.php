<?php
/**
 * About stats — Figma 8271:40267. Free composition at ≥1200 (title, three
 * numbers, two photos, the outline of Ukraine); numbers / photos / texts
 * drift at different speeds (app-about.js), the map draws itself once
 * (DrawSVG, .js-map), numbers count up (app-stats.js). Column below 1200.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = [ $args['item_1'] ?? [], $args['item_2'] ?? [], $args['item_3'] ?? [] ];

/**
 * data-count (+ data-count-sep) for a number with text around it.
 */
$rts_count_attrs = static function ( string $value ): string {
	$count = (int) preg_replace( '/\D+/', '', $value );

	if ( ! $count ) {
		return '';
	}

	return ' data-count="' . esc_attr( (string) $count ) . '"' . ( $count >= 1000 && ! str_contains( $value, ' ' ) ? ' data-count-sep=""' : '' );
};
?>
<section class="about-stats js-stats">
	<div class="container">
		<div class="about-stats__stage">
			<h2 class="about-stats__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>

			<?php
			foreach ( $rts_items as $rts_n => $rts_item ) {
				$rts_value = (string) ( $rts_item['value'] ?? '' );
				$rts_photo = 1 !== $rts_n && rts_image_id( $rts_item['image'] ?? 0 );
				?>
				<div class="about-stats__item about-stats__item--<?php echo esc_attr( (string) ( $rts_n + 1 ) ); ?>">
					<?php if ( 1 === $rts_n && ! empty( $rts_item['map'] ) ) { ?>
						<div class="about-stats__map js-map">
							<?php rts_icon( 'ukraine-map.svg' ); ?>
						</div>
					<?php } ?>
					<p class="about-stats__num js-drift" data-drift="<?php echo 1 === $rts_n ? '-120' : '-140'; ?>"<?php echo $rts_count_attrs( $rts_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>><?php echo esc_html( $rts_value ); ?></p>
					<?php if ( $rts_photo ) { ?>
						<div class="about-stats__photo js-drift" data-drift="60">
							<?php rts_picture( $rts_item['image'], null, [], 'large' ); ?>
						</div>
					<?php } ?>
					<div class="about-stats__copy js-drift" data-drift="<?php echo 1 === $rts_n ? '30' : '20'; ?>">
						<?php if ( ! empty( $rts_item['lead'] ) ) { ?>
							<p class="about-stats__lead h4"><?php echo esc_html( $rts_item['lead'] ); ?></p>
						<?php } ?>
						<?php if ( ! empty( $rts_item['note'] ) ) { ?>
							<p class="about-stats__note text-md"><?php echo esc_html( $rts_item['note'] ); ?></p>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
