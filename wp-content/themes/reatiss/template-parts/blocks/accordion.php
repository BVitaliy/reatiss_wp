<?php
/**
 * Accordion — Figma 8271:41371 (service "Available Analysis Levels"):
 * title + the FAQ accordion (.faq--wide, app-faq.js) in the 1236 column;
 * answers are editor content (.faq__answer--rich .rich-text).
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

if ( empty( $rts_items ) ) {
	return;
}

$rts_id = 'level-' . (int) ( $args['index'] ?? 0 ) . '-';
?>
<section class="service-levels">
	<div class="container">
		<div class="service-levels__inner">
			<div class="section-head section-head--left service-levels__head">
				<h2 class="section-head__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
			</div>

			<div class="faq faq--wide">
				<?php
				foreach ( $rts_items as $rts_n => $rts_item ) {
					$rts_open = ! empty( $rts_item['open'] );
					?>
					<div class="faq__item<?php echo $rts_open ? ' is-open' : ''; ?>">
						<span class="step-line"></span>
						<button class="faq__head h5 js-faq-head" type="button" aria-expanded="<?php echo $rts_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $rts_id . $rts_n ); ?>">
							<?php echo esc_html( (string) ( $rts_item['title'] ?? '' ) ); ?>
							<span class="faq__icon"><?php rts_icon( 'icons/plus.svg' ); ?><?php rts_icon( 'icons/minus.svg' ); ?></span>
						</button>
						<div class="faq__body" id="<?php echo esc_attr( $rts_id . $rts_n ); ?>"><div class="faq__body-inner"><div class="faq__answer faq__answer--rich rich-text text-md">
							<?php echo rts_editor_content( (string) ( $rts_item['content'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses + shortcodes in rts_editor_content(). ?>
						</div></div></div>
					</div>
				<?php } ?>
				<span class="step-line faq__end"></span>
			</div>
		</div>
	</div>
</section>
