<?php
/**
 * SEO text block — Figma 7967:9222. Visible + hidden editor text
 * (.more-text, app-more-text.js); the first paragraph is clamped to 5 lines
 * below 1200 until opened.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['title'] ) && empty( $args['text'] ) ) {
	return;
}

$rts_more = trim( (string) ( $args['more'] ?? '' ) );
$rts_id   = 'seo-more-' . (int) ( $args['index'] ?? 0 );
?>
<section class="seo-block">
	<div class="container">
		<div class="seo-block__box">
			<?php rts_title( $args, 'h2', 'seo-block__title h5' ); ?>

			<?php if ( ! empty( $args['text'] ) ) { ?>
			<div class="more-text more-text--clamp js-more-text">
				<div class="more-text__visible rich-text text-md">
					<?php echo rts_label_links( wp_kses_post( $args['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses, then escaped labels. ?>
				</div>

				<?php if ( '' !== $rts_more ) { ?>
					<div class="more-text__hidden" id="<?php echo esc_attr( $rts_id ); ?>">
						<div class="more-text__hidden-inner rich-text text-md">
							<?php echo rts_label_links( wp_kses_post( $rts_more ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses, then escaped labels. ?>
						</div>
					</div>

					<button class="more-text__toggle link-arrow js-more-text-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $rts_id ); ?>">
						<span class="more-text__more"><?php esc_html_e( 'Read more', 'reatiss' ); ?></span>
						<span class="more-text__less"><?php esc_html_e( 'Show less', 'reatiss' ); ?></span>
						<?php rts_icon( 'icons/arrow.svg' ); ?>
					</button>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
</section>
