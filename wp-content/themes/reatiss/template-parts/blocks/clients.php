<?php
/**
 * Client logos — Figma 8347:73455. 5 × 3 grid between step lines that draw
 * in on scroll (app-about.js). Logos are CSS masks (--logo), so any mono
 * PNG / SVG is recoloured by the design; each cell links to the client.
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
<section class="about-clients">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2" style="--title-w: 14em"><?php rts_br( $args['title'] ?? '' ); ?></h2>
		</div>

		<div class="about-clients__grid js-clients">
			<svg class="about-clients__lines" viewBox="0 0 1862 776" preserveAspectRatio="none" fill="none" aria-hidden="true">
				<path d="M16.5 0V254.67L0.5 264.713V512L16.5 521.33V776" />
				<path d="M385.5 0V254.67L369.5 264.713V512L385.5 521.33V776" />
				<path d="M754.5 0V254.67L738.5 264.713V512L754.5 521.33V776" />
				<path d="M1123.5 0V254.67L1107.5 264.713V512L1123.5 521.33V776" />
				<path d="M1492.5 0V254.67L1476.5 264.713V512L1492.5 521.33V776" />
				<path d="M1861.5 0V254.67L1845.5 264.713V512L1861.5 521.33V776" />
			</svg>
			<ul class="about-clients__list">
				<?php
				foreach ( $rts_items as $rts_item ) {
					$rts_logo = rts_image_id( $rts_item['logo'] ?? 0 );

					if ( ! $rts_logo ) {
						continue;
					}

					$rts_meta  = wp_get_attachment_metadata( $rts_logo );
					$rts_ratio = ! empty( $rts_meta['width'] ) && ! empty( $rts_meta['height'] ) ? $rts_meta['width'] . ' / ' . $rts_meta['height'] : '3 / 1';
					$rts_style = sprintf(
						"--logo: url('%s'); --logo-w: %d; --logo-ratio: %s;",
						esc_url( (string) wp_get_attachment_url( $rts_logo ) ),
						! empty( $rts_item['width'] ) ? (int) $rts_item['width'] : 200,
						$rts_ratio
					);
					$rts_name  = (string) ( $rts_item['name'] ?? '' );
					$rts_url   = (string) ( $rts_item['link'] ?? '' );
					?>
					<li class="client-card">
						<?php if ( '' !== $rts_url ) { ?>
							<a class="client-card__link" href="<?php echo esc_url( $rts_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: client name */ __( '%s (opens in a new tab)', 'reatiss' ), $rts_name ) ); ?>">
								<span class="client-card__logo" style="<?php echo esc_attr( $rts_style ); ?>" aria-hidden="true"></span>
							</a>
						<?php } else { ?>
							<span class="client-card__link" aria-label="<?php echo esc_attr( $rts_name ); ?>">
								<span class="client-card__logo" style="<?php echo esc_attr( $rts_style ); ?>" aria-hidden="true"></span>
							</span>
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</section>
