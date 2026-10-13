<?php
/**
 * FAQ — Figma 7967:9186. Title + link on the left, accordion on the right
 * (app-faq.js); stacks below 992. Questions: own or Site Settings → FAQ.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = 'own' === ( $args['source'] ?? '' ) ? ( $args['items'] ?? [] ) : rts_option( 'faq_items' );
$rts_items = is_array( $rts_items ) ? $rts_items : [];

if ( empty( $rts_items ) ) {
	return;
}

$rts_link = rts_link( $args['link'] ?? [] );
$rts_id   = 'faq-' . (int) ( $args['index'] ?? 0 ) . '-';
?>
<section class="home-faq">
	<div class="container home-faq__grid">
		<div class="home-faq__head">
			<h2 class="home-faq__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
			<?php if ( $rts_link && ! empty( $args['link']['label'] ) ) { ?>
				<a class="link-line"<?php echo rts_link_attrs( $rts_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $args['link']['label'] ); ?> <span class="link-line__icon" aria-hidden="true"><?php rts_icon( 'icons/arrow.svg' ); ?></span></a>
			<?php } ?>
		</div>

		<div class="faq home-faq__list">
			<?php
			foreach ( $rts_items as $rts_n => $rts_item ) {
				if ( empty( $rts_item['question'] ) ) {
					continue;
				}

				$rts_open = ! empty( $rts_item['open'] );
				?>
				<div class="faq__item<?php echo $rts_open ? ' is-open' : ''; ?>">
					<span class="step-line"></span>
					<button class="faq__head h5 js-faq-head" type="button" aria-expanded="<?php echo $rts_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $rts_id . $rts_n ); ?>">
						<?php echo esc_html( $rts_item['question'] ); ?>
						<span class="faq__icon"><?php rts_icon( 'icons/plus.svg' ); ?><?php rts_icon( 'icons/minus.svg' ); ?></span>
					</button>
					<div class="faq__body" id="<?php echo esc_attr( $rts_id . $rts_n ); ?>"><div class="faq__body-inner"><div class="faq__answer faq__answer--rich rich-text text-md"><?php echo wp_kses_post( (string) ( $rts_item['answer'] ?? '' ) ); ?></div></div></div>
				</div>
			<?php } ?>
			<span class="step-line faq__end"></span>
		</div>
	</div>
</section>
