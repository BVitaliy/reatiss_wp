<?php
/**
 * About intro — Figma 8271:40187. Two photos with the text panel over them
 * (.cut--br); the photos drift apart on scroll (data-drift, app-about.js, ≥1200).
 * Below 1200: photo, panel, photo.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( empty( $args['title'] ) && empty( $args['text'] ) && ! rts_has_button( $args['btn'] ?? [] ) && ! rts_image_id( $args['image_1'] ?? 0 ) && ! rts_image_id( $args['image_2'] ?? 0 ) ) {
	return;
}
?>
<section class="about-intro">
	<div class="container">
		<div class="about-intro__stage">
			<?php if ( rts_image_id( $args['image_1'] ?? 0 ) ) { ?>
				<div class="about-intro__photo about-intro__photo--1 js-drift" data-drift="90">
					<?php rts_picture( $args['image_1'], $args['image_1_mobile'] ?? 0, [], 'large' ); ?>
				</div>
			<?php } ?>

			<?php if ( rts_image_id( $args['image_2'] ?? 0 ) ) { ?>
				<div class="about-intro__photo about-intro__photo--2 js-drift" data-drift="-90">
					<?php rts_picture( $args['image_2'], $args['image_2_mobile'] ?? 0, [], 'large' ); ?>
				</div>
			<?php } ?>

			<div class="about-intro__panel cut cut--br">
				<div class="about-intro__content">
					<?php rts_title( $args, 'h2', 'about-intro__title h3' ); ?>
					<span class="step-line about-intro__line"></span>
					<?php if ( ! empty( $args['text'] ) ) { ?>
						<div class="about-intro__text rich-text text-lg">
							<?php echo rts_label_links( wp_kses_post( $args['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses, then escaped labels. ?>
						</div>
					<?php } ?>
				</div>
				<?php rts_button( $args['btn'] ?? [], 'btn btn--primary about-intro__btn' ); ?>
			</div>
		</div>
	</div>
</section>
