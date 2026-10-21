<?php
/**
 * Footer — Figma 7967:9237.
 * Background: cursor-reactive dot grid (.dot-field, app-stats.js).
 * Link columns are the “Footer — column N” menus; the column title is the menu name.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_email     = (string) rts_option( 'contact_email' );
$rts_phone     = (string) rts_option( 'contact_phone' );
$rts_text      = (string) rts_option( 'footer_text' );
$rts_copyright = str_replace( '{year}', wp_date( 'Y' ), (string) rts_option( 'footer_copyright' ) );
$rts_socials   = rts_socials();
$rts_columns   = [];

foreach ( [ 1, 2, 3, 4, 5 ] as $rts_n ) {
	$rts_location = 'footer_menu_' . $rts_n;

	if ( has_nav_menu( $rts_location ) ) {
		$rts_columns[ $rts_location ] = wp_get_nav_menu_name( $rts_location );
	}
}
?>
<footer class="footer">
	<div class="dot-field js-dot-field" aria-hidden="true"></div>
	<div class="container">

		<div class="footer__top">
			<div class="footer__about">
				<a href="<?php echo esc_url( RTS_HOME_URL ); ?>" class="logo" aria-label="<?php esc_attr_e( 'Reatiss — home', 'reatiss' ); ?>">
					<?php rts_logo( 'footer_logo', true ); ?>
				</a>
				<?php if ( '' !== $rts_text ) { ?>
					<p class="footer__text text-md"><?php echo esc_html( $rts_text ); ?></p>
				<?php } ?>

				<?php if ( ! empty( $rts_socials ) ) { ?>
					<div class="footer__social">
						<p class="footer__label"><?php echo esc_html( (string) rts_option( 'socials_title' ) ); ?></p>
						<ul class="footer__social-list">
							<?php foreach ( $rts_socials as $rts_social ) { ?>
								<li>
									<a href="<?php echo esc_url( $rts_social['url'] ); ?>" class="footer__social-link" aria-label="<?php echo esc_attr( $rts_social['label'] ); ?>" target="_blank" rel="noopener">
										<?php rts_social_icon( $rts_social ); ?>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</div>

			<nav class="footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'reatiss' ); ?>">
				<?php foreach ( $rts_columns as $rts_location => $rts_title ) { ?>
					<div class="footer__col">
						<p class="footer__label"><?php echo esc_html( $rts_title ); ?></p>
						<?php
						wp_nav_menu(
							[
								'theme_location' => $rts_location,
								'container'      => false,
								'items_wrap'     => '<ul class="footer__links">%3$s</ul>',
								'depth'          => 1,
								'walker'         => new RTS_Nav_Walker(),
							]
						);
						?>
					</div>
				<?php } ?>

				<?php if ( '' !== $rts_email || '' !== $rts_phone ) { ?>
					<div class="footer__col footer__col--contact">
						<p class="footer__label"><?php echo esc_html( (string) rts_option( 'contact_title' ) ); ?></p>
						<ul class="footer__links">
							<?php if ( '' !== $rts_email ) { ?>
								<li><a<?php echo rts_contact_link_attrs( 'email', $rts_email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>><?php rts_icon( 'icons/mail.svg' ); ?><?php echo esc_html( antispambot( $rts_email ) ); ?></a></li>
							<?php } ?>
							<?php if ( '' !== $rts_phone ) { ?>
								<li><a<?php echo rts_contact_link_attrs( 'phone', $rts_phone ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>><?php rts_icon( 'icons/phone.svg' ); ?><?php echo esc_html( $rts_phone ); ?></a></li>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</nav>
		</div>

		<div class="footer__bottom">
			<div class="footer__legal">
				<?php if ( '' !== $rts_copyright ) { ?>
					<p><?php echo esc_html( $rts_copyright ); ?></p>
				<?php } ?>
				<?php
				if ( has_nav_menu( 'legal_menu' ) ) {
					wp_nav_menu(
						[
							'theme_location' => 'legal_menu',
							'container'      => false,
							'items_wrap'     => '<ul class="footer__legal-links">%3$s</ul>',
							'depth'          => 1,
							'walker'         => new RTS_Nav_Walker(),
						]
					);
				}
				?>
			</div>
			<a href="https://redstone.agency/" class="footer__dev" target="_blank" rel="noopener">
				<?php esc_html_e( 'Website Development:', 'reatiss' ); ?>
				<img src="<?php echo esc_url( rts_asset( 'img/redstone.svg' ) ); ?>" alt="Redstone" width="83" height="12" loading="lazy">
			</a>
		</div>

	</div>
</footer>
