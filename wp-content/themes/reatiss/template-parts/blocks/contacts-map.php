<?php
/**
 * Contacts map — Figma 8069:19671. Live Google Map ("Muted Blue", app-map.js;
 * the Maps script loads near the viewport) or an uploaded picture centred on
 * the office. Both carry the pin + office card (hidden until the pin is
 * clicked). Without an API key the Google variant falls back to the picture.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_key     = trim( (string) rts_option( 'google_maps_key' ) );
$rts_image   = rts_image_id( $args['image'] ?? 0 );
$rts_google  = 'image' !== ( $args['mode'] ?? 'google' ) && '' !== $rts_key;
$rts_offices = rts_offices();
$rts_office  = $rts_offices[ max( 1, (int) ( $args['office'] ?? 1 ) ) - 1 ] ?? ( $rts_offices[0] ?? null );

if ( ! $rts_google && ! $rts_image ) {
	return;
}

$rts_card_id = 'map-card-' . (int) ( $args['index'] ?? 0 );
?>
<?php if ( $rts_google ) { ?>
<section class="contacts-map js-map is-card-closed" aria-label="<?php esc_attr_e( 'Office on the map', 'reatiss' ); ?>"
	data-api-key="<?php echo esc_attr( $rts_key ); ?>"
	data-lat="<?php echo esc_attr( (string) (float) ( $args['lat'] ?? 0 ) ); ?>" data-lng="<?php echo esc_attr( (string) (float) ( $args['lng'] ?? 0 ) ); ?>" data-zoom="<?php echo esc_attr( (string) (int) ( $args['zoom'] ?? 15 ) ); ?>">
	<div class="contacts-map__canvas js-map-canvas"></div>
<?php } else { ?>
<section class="contacts-map contacts-map--image js-map is-card-closed" aria-label="<?php esc_attr_e( 'Office on the map', 'reatiss' ); ?>">
	<?php
	ob_start();
	rts_picture( $rts_image, $args['image_mobile'] ?? 0, [], 'full' );
	echo str_replace( '<picture>', '<picture class="contacts-map__image">', (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture().
	?>
<?php } ?>
	<?php if ( $rts_office ) { ?>
		<div class="contacts-map__marker js-map-marker">
			<button class="contacts-map__pin js-map-pin" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $rts_card_id ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: office location */ __( '%s office address', 'reatiss' ), (string) $rts_office['location'] ) ); ?>">
				<?php rts_icon( 'icons/map-pin.svg' ); ?>
			</button>
			<div class="contacts-map__card office-card" id="<?php echo esc_attr( $rts_card_id ); ?>">
				<button class="btn-round btn-round--close contacts-map__close js-map-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
					<?php rts_icon( 'icons/close.svg' ); ?>
				</button>
				<?php rts_office_title( $rts_office ); ?>
				<?php rts_contact_list( (string) ( $rts_office['address'] ?? '' ), (string) ( $rts_office['phone'] ?? '' ), (string) ( $rts_office['email'] ?? '' ) ); ?>
			</div>
		</div>
	<?php } ?>
</section>
