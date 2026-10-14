<?php
/**
 * Contacts — Figma 8069:19628. Left: title, socials, lead, main contacts,
 * step line, offices; right: the request form in a light box (.cut--br).
 * Contacts / offices / socials: Site Settings.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_form    = ! empty( $args['form'] ) ? (int) $args['form'] : (int) rts_option( 'contact_form' );
$rts_offices = ! empty( $args['offices'] ) ? rts_offices() : [];
$rts_socials = ! empty( $args['socials'] ) ? rts_socials() : [];
$rts_icons   = [
	'facebook'  => 'share-facebook.svg',
	'instagram' => 'share-instagram.svg',
	'tiktok'    => 'tiktok.svg',
	'x'         => 'share-x.svg',
	'linkedin'  => 'linkedin.svg',
];
?>
<section class="contacts">
	<div class="container contacts__grid">
		<div class="contacts__info">
			<h1 class="contacts__title h1 h1--sm"><?php rts_br( $args['title'] ?? '' ); ?></h1>

			<?php if ( ! empty( $rts_socials ) ) { ?>
				<ul class="social-list">
					<?php foreach ( $rts_socials as $rts_social ) { ?>
						<li><a href="<?php echo esc_url( $rts_social['url'] ); ?>" class="social-btn social-btn--light" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: network */ __( 'Reatiss on %s', 'reatiss' ), $rts_social['label'] ) ); ?>" target="_blank" rel="noopener"><?php rts_icon( 'icons/' . $rts_icons[ $rts_social['icon'] ] ); ?></a></li>
					<?php } ?>
				</ul>
			<?php } ?>

			<?php if ( ! empty( $args['lead'] ) ) { ?>
				<p class="contacts__lead text-lg"><?php echo esc_html( $args['lead'] ); ?></p>
			<?php } ?>

			<?php
			if ( ! empty( $args['main'] ) ) {
				rts_contact_list( (string) rts_option( 'contact_address' ), (string) rts_option( 'contact_phone' ), (string) rts_option( 'contact_email' ) );
			}
			?>

			<?php if ( ! empty( $rts_offices ) ) { ?>
				<span class="step-line contacts__line"></span>

				<div class="contacts__offices">
					<?php foreach ( $rts_offices as $rts_office ) { ?>
						<div class="office-card">
							<?php rts_office_title( $rts_office ); ?>
							<?php rts_contact_list( (string) ( $rts_office['address'] ?? '' ), (string) ( $rts_office['phone'] ?? '' ), (string) ( $rts_office['email'] ?? '' ) ); ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<?php if ( rts_has_form( $rts_form ) ) { ?>
			<div class="contacts__form-box form-box cut cut--br">
				<div class="form-box__form">
					<?php if ( ! empty( $args['form_title'] ) ) { ?>
						<h2 class="form-box__title h3"><?php echo esc_html( $args['form_title'] ); ?></h2>
					<?php } ?>
					<?php rts_form( $rts_form, 'form-box__form' ); ?>
				</div>
			</div>
		<?php } ?>
	</div>
</section>
