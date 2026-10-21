<?php
/**
 * Company history timeline — Figma 8271:40213 (app-about.js): year tabs over
 * a line whose notch slides under the active year, ← / → arrows, drag / swipe;
 * each year opens its panel (title + text | photo). id="history" — the About
 * intro button points at it.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? array_values( array_filter( $args['items'], static fn( $item ) => ! empty( $item['year'] ) ) ) : [];

if ( empty( $args['title'] ) && empty( $rts_items ) ) {
	return;
}

// Unique, attribute-safe keys per year ("1960–80s" → "y196080s").
$rts_keys = [];
foreach ( $rts_items as $rts_n => $rts_item ) {
	$rts_keys[ $rts_n ] = 'y' . preg_replace( '/[^a-z0-9]/i', '', (string) ( $rts_item['year'] ?? '' ) ) . '-' . $rts_n;
}
?>
<section class="about-history js-history" id="history">
	<div class="container">
		<div class="about-history__inner">
			<?php rts_title( $args, 'h2', 'about-history__title h2' ); ?>

			<?php if ( ! empty( $rts_items ) ) { ?>
			<div class="about-history__bar">
				<div class="about-history__years-wrap" data-lenis-prevent-horizontal>
					<div class="about-history__years" role="tablist" aria-label="<?php esc_attr_e( 'Company history', 'reatiss' ); ?>">
						<?php foreach ( $rts_items as $rts_n => $rts_item ) { ?>
							<button class="about-history__year h5<?php echo 0 === $rts_n ? ' is-active' : ''; ?>" type="button" role="tab" id="history-tab-<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>" aria-selected="<?php echo 0 === $rts_n ? 'true' : 'false'; ?>" aria-controls="history-<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>" data-history-tab="<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>"><?php echo esc_html( (string) ( $rts_item['year'] ?? '' ) ); ?></button>
						<?php } ?>
					</div>
					<span class="about-history__line" aria-hidden="true">
						<svg class="about-history__notch" viewBox="0 0 112 16" preserveAspectRatio="none"><path d="M0 0.5H18.9L33.8 15.5H81.5L96.5 0.5H112" /></svg>
					</span>
				</div>
				<div class="about-history__nav">
					<button class="about-history__arrow about-history__arrow--prev js-history-prev" type="button" aria-label="<?php esc_attr_e( 'Previous year', 'reatiss' ); ?>"><?php rts_icon( 'icons/arrow.svg' ); ?></button>
					<button class="about-history__arrow js-history-next" type="button" aria-label="<?php esc_attr_e( 'Next year', 'reatiss' ); ?>"><?php rts_icon( 'icons/arrow.svg' ); ?></button>
				</div>
			</div>

			<div class="about-history__panels">
				<?php foreach ( $rts_items as $rts_n => $rts_item ) { ?>
					<article class="about-history__panel<?php echo 0 === $rts_n ? ' is-active' : ''; ?>" id="history-<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>" role="tabpanel" aria-labelledby="history-tab-<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>" data-history-panel="<?php echo esc_attr( $rts_keys[ $rts_n ] ); ?>"<?php echo 0 === $rts_n ? '' : ' hidden'; ?>>
						<div class="about-history__copy">
							<?php if ( ! empty( $rts_item['title'] ) ) { ?>
								<h3 class="about-history__panel-title h5"><?php echo esc_html( $rts_item['title'] ); ?></h3>
							<?php } ?>
							<?php if ( ! empty( $rts_item['text'] ) ) { ?>
								<p class="about-history__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
							<?php } ?>
						</div>
						<?php if ( rts_image_id( $rts_item['image'] ?? 0 ) ) { ?>
							<div class="about-history__media">
								<?php rts_picture( $rts_item['image'], null, [], 'large' ); ?>
							</div>
						<?php } ?>
					</article>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
</section>
