<?php
/**
 * Capabilities — Figma 8356:36580. Scroll story (app-lab.js): ≥1200 the
 * stage is pinned and each scroll step brings the next row (photo wipes up,
 * panel parts stagger in); below 1200 a Swiper slider. Row: photo | number,
 * title, text, icon list, optional link.
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
?>
<section class="capabilities js-capabilities">
	<div class="container">
		<h2 class="capabilities__title h2"><?php rts_br( $args['title'] ?? '' ); ?></h2>
	</div>

	<div class="capabilities__track">
		<div class="capabilities__stage swiper">
			<div class="capabilities__slides swiper-wrapper">
				<?php
				foreach ( $rts_items as $rts_n => $rts_item ) {
					$rts_link = rts_link( $rts_item['link'] ?? [] );
					$rts_list = is_array( $rts_item['list'] ?? null ) ? $rts_item['list'] : [];
					?>
					<article class="capability swiper-slide<?php echo 0 === $rts_n ? ' is-active' : ''; ?>" style="--i: <?php echo esc_attr( (string) $rts_n ); ?>">
						<div class="capability__media">
							<?php rts_picture( $rts_item['image'] ?? 0, $rts_item['image_mobile'] ?? 0, [], 'large' ); ?>
						</div>
						<div class="capability__panel">
							<div class="capability__head">
								<div class="capability__mark">
									<span class="step-line"></span>
									<span class="capability__num"><?php echo esc_html( (string) ( $rts_item['num'] ?? '' ) ); ?></span>
								</div>
								<h3 class="capability__title h4"><?php echo esc_html( (string) ( $rts_item['title'] ?? '' ) ); ?></h3>
							</div>
							<div class="capability__body">
								<?php if ( ! empty( $rts_item['text'] ) ) { ?>
									<p class="capability__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></p>
								<?php } ?>
								<?php if ( ! empty( $rts_list ) ) { ?>
									<ul class="capability__list text-md">
										<?php foreach ( $rts_list as $rts_row ) { ?>
											<li class="capability__item">
												<?php if ( rts_image_id( $rts_row['icon'] ?? 0 ) ) { ?>
													<span class="capability__icon"><?php rts_image( $rts_row['icon'], [ 'alt' => '' ], 'thumbnail' ); ?></span>
												<?php } ?>
												<?php echo esc_html( (string) ( $rts_row['text'] ?? '' ) ); ?>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>
							</div>
							<?php if ( $rts_link && ! empty( $rts_item['link']['label'] ) ) { ?>
								<a class="link-line capability__link"<?php echo rts_link_attrs( $rts_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?>><?php echo esc_html( $rts_item['link']['label'] ); ?> <span class="link-line__icon" aria-hidden="true"><?php rts_icon( 'icons/arrow.svg' ); ?></span></a>
							<?php } ?>
						</div>
					</article>
				<?php } ?>
			</div>
			<div class="grid-slider__pagination capabilities__pagination"></div>
		</div>
	</div>
</section>
