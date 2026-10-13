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

if ( empty( $args['text'] ) ) {
	return;
}

$rts_more = trim( (string) ( $args['more'] ?? '' ) );
$rts_id   = 'seo-more-' . (int) ( $args['index'] ?? 0 );
?>
<section class="seo-block">
	<div class="container">
		<div class="seo-block__box">
			<h2 class="seo-block__title h5"><?php echo esc_html( (string) ( $args['title'] ?? '' ) ); ?></h2>

			<div class="more-text more-text--clamp js-more-text">
				<div class="more-text__visible rich-text text-md">
					<?php echo wp_kses_post( $args['text'] ); ?>
				</div>

				<?php if ( '' !== $rts_more ) { ?>
					<div class="more-text__hidden" id="<?php echo esc_attr( $rts_id ); ?>">
						<div class="more-text__hidden-inner rich-text text-md">
							<?php echo wp_kses_post( $rts_more ); ?>
						</div>
					</div>

					<button class="more-text__toggle link-arrow js-more-text-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $rts_id ); ?>">
						<span class="more-text__more"><?php esc_html_e( 'Read more', 'reatiss' ); ?></span>
						<span class="more-text__less"><?php esc_html_e( 'Show less', 'reatiss' ); ?></span>
						<?php rts_icon( 'icons/arrow.svg' ); ?>
					</button>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
