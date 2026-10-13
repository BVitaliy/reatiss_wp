<?php
/**
 * CTA box — Figma 8340:73254. Light box centred: title · text · button, two
 * hexagon outlines behind.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<section class="cta-box-section">
	<div class="container">
		<div class="cta-box">
			<svg class="cta-box__bg" viewBox="0 0 1028 1366" fill="none" aria-hidden="true">
				<path d="M910.5 894.541V470.735L514 156.638L117.5 470.735V894.541L514 1208.64L910.5 894.541Z" />
				<path d="M1027.5 957.387V407.89L514 0.63816L0.5 407.89V957.387L514 1364.64L1027.5 957.387Z" />
			</svg>
			<div class="cta-box__body">
				<h2 class="cta-box__title h3"><?php rts_br( $args['title'] ?? '' ); ?></h2>
				<?php if ( ! empty( $args['text'] ) ) { ?>
					<p class="cta-box__text text-lg"><?php echo esc_html( $args['text'] ); ?></p>
				<?php } ?>
			</div>
			<?php rts_button( $args['btn'] ?? [], 'btn btn--primary cta-box__btn' ); ?>
		</div>
	</div>
</section>
