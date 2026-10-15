<?php
/**
 * Content — Figma 8271:41515 (service), 8271:41728 (lab), 8271:40683
 * (expertise, with its own line): centred title + editor body in the 1236
 * column (.article-content.rich-text). Inserts in the text: galleries shown as
 * sliders, [rts_cta], [rts_youtube] (functions/shortcodes.php); the “Slider
 * photos” field adds a slider under the text.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['text'] ) ) {
	return;
}

$rts_line = ! empty( $args['line'] );
?>
<section class="service-content<?php echo $rts_line ? ' expertise-detail__content' : ''; ?>">
	<?php if ( $rts_line ) { ?>
		<div class="run-line expertise-detail__content-line js-run-line" data-duration="14" data-start="0.52" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 391" preserveAspectRatio="xMidYMin slice" fill="none">
				<path class="run-line__path js-run-path" d="M1177 0V153.5C1177 185.533 1202.97 211.5 1235 211.5H1581.014C1601.735 211.5 1620.882 222.555 1631.243 240.5L1700.814 361C1711.175 378.945 1730.322 390 1751.043 390H2212.5" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 85.7%; --y: 68.3%;"></span>
		</div>
	<?php } ?>

	<div class="container">
		<div class="service-content__inner">
			<?php if ( ! empty( $args['title'] ) ) { ?>
				<h2 class="service-content__title h2"><?php rts_br( $args['title'] ); ?></h2>
			<?php } ?>

			<div class="article-content rich-text text-lg">
				<?php echo rts_editor_content( (string) $args['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses + shortcodes in rts_editor_content(). ?>
				<?php echo rts_editor_content( rts_slider_html( is_array( $args['slider'] ?? null ) ? $args['slider'] : [] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
			</div>
		</div>
	</div>
</section>
