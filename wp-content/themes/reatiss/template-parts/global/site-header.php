<?php
/**
 * Header — Figma 7967:8872 (over hero) · 7976:22677 (on scroll) · 7982:27375 (search).
 * Pages without a dark banner get `header--light` (rts_header_class()).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_cta   = (string) rts_option( 'header_cta_label' );
$rts_email = (string) rts_option( 'contact_email' );
$rts_phone = (string) rts_option( 'contact_phone' );
?>
<header class="header <?php echo esc_attr( rts_header_class() ); ?> js-header">
	<div class="header__inner">

		<a href="<?php echo esc_url( RTS_HOME_URL ); ?>" class="logo" aria-label="<?php esc_attr_e( 'Reatiss — home', 'reatiss' ); ?>">
			<?php rts_icon( 'logo.svg' ); ?>
		</a>

		<?php if ( has_nav_menu( 'header_menu' ) ) { ?>
			<nav class="header__nav" aria-label="<?php esc_attr_e( 'Main', 'reatiss' ); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'header_menu',
						'container'      => false,
						'items_wrap'     => '<ul class="header__menu">%3$s</ul>',
						'depth'          => 1,
						'walker'         => new RTS_Nav_Walker( '', 'header__link' ),
					]
				);
				?>
			</nav>
		<?php } ?>

		<form class="header__search js-search" action="<?php echo esc_url( RTS_HOME_URL ); ?>" role="search">
			<label class="search-field">
				<?php rts_icon( 'icons/search.svg' ); ?>
				<span class="visually-hidden"><?php esc_html_e( 'Search', 'reatiss' ); ?></span>
				<input class="search-field__input js-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search…', 'reatiss' ); ?>" autocomplete="off">
				<button class="search-field__btn js-search-close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'reatiss' ); ?>">
					<?php rts_icon( 'icons/close.svg' ); ?>
				</button>
			</label>

			<?php // Live results — filled by js/search-live.js. ?>
			<div class="header__results js-search-results" data-lenis-prevent></div>
		</form>

		<div class="header__actions">
			<button class="btn-round header__search-btn js-search-open" type="button" aria-label="<?php esc_attr_e( 'Open search', 'reatiss' ); ?>">
				<?php rts_icon( 'icons/search.svg' ); ?>
			</button>
			<?php if ( '' !== $rts_cta ) { ?>
				<a href="#" class="btn-solid header__cta" data-modal-open="consultation"><?php echo esc_html( $rts_cta ); ?></a>
			<?php } ?>
			<button class="header__burger js-burger" type="button" aria-label="<?php esc_attr_e( 'Menu', 'reatiss' ); ?>" aria-expanded="false" aria-controls="mobile-menu">
				<span></span><span></span><span></span>
			</button>
		</div>

	</div>
</header>

<div class="mobile-menu js-mobile-menu" id="mobile-menu" data-lenis-prevent>
	<?php if ( has_nav_menu( 'header_menu' ) ) { ?>
		<nav aria-label="<?php esc_attr_e( 'Mobile', 'reatiss' ); ?>">
			<?php
			wp_nav_menu(
				[
					'theme_location' => 'header_menu',
					'container'      => false,
					'items_wrap'     => '<ul class="mobile-menu__list">%3$s</ul>',
					'depth'          => 1,
					'walker'         => new RTS_Nav_Walker( '', 'mobile-menu__link' ),
				]
			);
			?>
		</nav>
	<?php } ?>
	<div class="mobile-menu__foot">
		<?php if ( '' !== $rts_cta ) { ?>
			<a href="#" class="btn btn--primary" data-modal-open="consultation">
				<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
				<?php echo esc_html( $rts_cta ); ?>
			</a>
		<?php } ?>
		<?php if ( '' !== $rts_email || '' !== $rts_phone ) { ?>
			<div class="mobile-menu__contacts">
				<?php if ( '' !== $rts_email ) { ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $rts_email ) ); ?>"><?php rts_icon( 'icons/mail.svg' ); ?><?php echo esc_html( antispambot( $rts_email ) ); ?></a>
				<?php } ?>
				<?php if ( '' !== $rts_phone ) { ?>
					<a href="<?php echo esc_attr( rts_phone_href( $rts_phone ) ); ?>"><?php rts_icon( 'icons/phone.svg' ); ?><?php echo esc_html( $rts_phone ); ?></a>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
