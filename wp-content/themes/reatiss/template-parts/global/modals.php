<?php
/**
 * Modals — Figma 7967:15775 (consultation, base of "thanks") · 7976:18111 (service)
 * · 8278:46503 (reports) · 8271:40574 (team member) · 7976:20379 (toast).
 * ONE .modal wrapper: overlay + panels. Open with data-modal-open="name".
 * Texts, forms and promos come from Site Settings.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_promo = ! empty( rts_option( 'promo_enable' ) ) && ! empty( rts_option( 'promo_title' ) ) && ! is_404();
$rts_toast = ! empty( rts_option( 'toast_enable' ) ) && ! empty( rts_option( 'toast_title' ) ) && ! is_404();
$rts_forms = [
	'consultation' => [
		'title' => (string) rts_option( 'consultation_title' ),
		'form'  => (int) rts_option( 'consultation_form' ),
		'class' => '',
	],
	'reports'      => [
		'title' => (string) rts_option( 'reports_title' ),
		'form'  => (int) rts_option( 'reports_form' ),
		'class' => ' modal__panel--reports',
	],
];
?>
<div class="modal js-modal" data-lenis-prevent aria-hidden="true">
	<div class="modal__overlay js-modal-close"></div>

	<?php // Team member (about page) — filled from the clicked card's <template>. ?>
	<div class="modal__panel modal__panel--team" data-modal="team" role="dialog" aria-modal="true" aria-labelledby="team-modal-title">
		<button class="btn-round btn-round--close modal__close js-modal-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
			<?php rts_icon( 'icons/close.svg' ); ?>
		</button>
		<div class="js-team-modal-body"></div>
	</div>

	<?php
	foreach ( $rts_forms as $rts_name => $rts_modal ) {
		if ( ! rts_has_form( $rts_modal['form'] ) ) {
			continue;
		}
		?>
		<div class="modal__panel<?php echo esc_attr( $rts_modal['class'] ); ?> cut cut--br" data-modal="<?php echo esc_attr( $rts_name ); ?>" role="dialog" aria-modal="true" aria-labelledby="modal-<?php echo esc_attr( $rts_name ); ?>-title">
			<button class="btn-round btn-round--close modal__close js-modal-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
				<?php rts_icon( 'icons/close.svg' ); ?>
			</button>
			<div class="modal__form">
				<?php if ( '' !== $rts_modal['title'] ) { ?>
					<p class="modal__title h3" id="modal-<?php echo esc_attr( $rts_name ); ?>-title"><?php echo esc_html( $rts_modal['title'] ); ?></p>
				<?php } ?>
				<?php rts_form( $rts_modal['form'], 'modal__form' ); ?>
			</div>
		</div>
		<?php
	}
	?>

	<?php // Thank you — after any CF7 form is sent (js/app-cf7.js). ?>
	<div class="modal__panel modal__panel--thanks cut cut--br" data-modal="thanks" role="dialog" aria-modal="true" aria-labelledby="modal-thanks-title">
		<button class="btn-round btn-round--close modal__close js-modal-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
			<?php rts_icon( 'icons/close.svg' ); ?>
		</button>
		<div class="modal__thanks">
			<p class="modal__title h3" id="modal-thanks-title"><?php echo esc_html( (string) rts_option( 'thanks_title' ) ); ?></p>
			<?php if ( ! empty( rts_option( 'thanks_text' ) ) ) { ?>
				<p class="modal__thanks-text text-lg"><?php echo esc_html( (string) rts_option( 'thanks_text' ) ); ?></p>
			<?php } ?>
			<?php if ( ! empty( rts_option( 'thanks_btn' ) ) ) { ?>
				<button class="btn btn--primary js-modal-close" type="button">
					<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
					<?php echo esc_html( (string) rts_option( 'thanks_btn' ) ); ?>
				</button>
			<?php } ?>
		</div>
	</div>

	<?php if ( $rts_promo ) { ?>
		<?php // Service promo — opens by itself once per session (data-auto-open ms). ?>
		<div class="modal__panel modal__panel--wide cut cut--br" data-modal="service" data-auto-open="<?php echo esc_attr( (string) (int) ( (float) rts_option( 'promo_delay' ) * 1000 ) ); ?>" role="dialog" aria-modal="true" aria-labelledby="modal-service-title">
			<button class="btn-round btn-round--close modal__close js-modal-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
				<?php rts_icon( 'icons/close.svg' ); ?>
			</button>
			<div class="modal__service">
				<?php if ( rts_image_id( rts_option( 'promo_image' ) ) ) { ?>
					<div class="modal__service-media">
						<?php rts_picture( rts_option( 'promo_image' ), rts_option( 'promo_image_mobile' ), [], 'large' ); ?>
					</div>
				<?php } ?>
				<div class="modal__service-body">
					<div class="modal__service-text">
						<p class="h3" id="modal-service-title"><?php echo esc_html( (string) rts_option( 'promo_title' ) ); ?></p>
						<?php if ( ! empty( rts_option( 'promo_text' ) ) ) { ?>
							<p class="text-lg"><?php echo esc_html( (string) rts_option( 'promo_text' ) ); ?></p>
						<?php } ?>
					</div>
					<?php rts_button( rts_option( 'promo_btn' ) ); ?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<?php
if ( $rts_toast ) {
	$rts_toast_link = rts_link( rts_option( 'toast_link' ) );
	$rts_toast_img  = rts_image_id( rts_option( 'toast_image' ) );
	$rts_toast_href = $rts_toast_link ? $rts_toast_link['url'] : '';
	$rts_toast_tab  = $rts_toast_link && $rts_toast_link['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : '';
	?>
	<?php // Corner toast (promo, no overlay). The picture is fetched right before it shows (app-modals.js). ?>
	<aside class="toast js-toast" aria-label="<?php esc_attr_e( 'Offer', 'reatiss' ); ?>" data-delay="<?php echo esc_attr( (string) (int) ( (float) rts_option( 'toast_delay' ) * 1000 ) ); ?>">
		<button class="btn-round btn-round--close toast__close js-toast-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'reatiss' ); ?>">
			<?php rts_icon( 'icons/close.svg' ); ?>
		</button>
		<?php if ( $rts_toast_img ) { ?>
			<<?php echo $rts_toast_href ? 'a href="' . esc_url( $rts_toast_href ) . '"' . $rts_toast_tab : 'span'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?> class="toast__media" tabindex="-1" aria-hidden="true">
				<picture>
					<img data-src="<?php echo esc_url( rts_image_url( $rts_toast_img, 'medium_large' ) ); ?>" alt="" width="800" height="800" decoding="async">
				</picture>
			</<?php echo $rts_toast_href ? 'a' : 'span'; ?>>
		<?php } ?>
		<div class="toast__body">
			<?php if ( $rts_toast_href ) { ?>
				<a href="<?php echo esc_url( $rts_toast_href ); ?>" class="toast__title h5"<?php echo $rts_toast_tab . rts_aria_label( (string) rts_option( 'toast_title' ), '' !== $rts_toast_tab ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attributes / escaped inside. ?>><?php echo esc_html( (string) rts_option( 'toast_title' ) ); ?></a>
			<?php } else { ?>
				<p class="toast__title h5"><?php echo esc_html( (string) rts_option( 'toast_title' ) ); ?></p>
			<?php } ?>
			<?php if ( ! empty( rts_option( 'toast_text' ) ) ) { ?>
				<p class="toast__text text-md"><?php echo esc_html( (string) rts_option( 'toast_text' ) ); ?></p>
			<?php } ?>
			<?php if ( $rts_toast_href ) { ?>
				<a href="<?php echo esc_url( $rts_toast_href ); ?>" class="link-arrow"<?php echo $rts_toast_tab . rts_aria_label( sprintf( /* translators: %s: banner title */ __( 'Learn more: %s', 'reatiss' ), (string) rts_option( 'toast_title' ) ), '' !== $rts_toast_tab ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attributes / escaped inside. ?>><?php esc_html_e( 'Learn more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></a>
			<?php } ?>
		</div>
	</aside>
<?php } ?>

<?php // Fullscreen video (opened by [data-video-open="src"]). ?>
<div class="video-modal js-video-modal" data-lenis-prevent role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Video', 'reatiss' ); ?>" aria-hidden="true">
	<button class="btn-round btn-round--close video-modal__close js-video-close" type="button" aria-label="<?php esc_attr_e( 'Close video', 'reatiss' ); ?>">
		<?php rts_icon( 'icons/close.svg' ); ?>
	</button>
	<div class="video-modal__stage js-video-close">
		<video class="video-modal__video js-video-player" controls playsinline preload="none"></video>
	</div>
</div>
