<?php
/**
 * Request form — Figma 8102:23473. The shared .form-box centred (925),
 * its own cut; the CF7 form chosen in the block or Site Settings → Форми.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_form = ! empty( $args['form'] ) ? (int) $args['form'] : (int) rts_option( 'request_form' );

if ( ! rts_has_form( $rts_form ) ) {
	return;
}
?>
<section class="request-form">
	<div class="container">
		<div class="request-form__box form-box cut cut--br">
			<div class="form-box__form">
				<?php if ( ! empty( $args['title'] ) ) { ?>
					<h2 class="form-box__title h3"><?php echo esc_html( $args['title'] ); ?></h2>
				<?php } ?>
				<?php rts_form( $rts_form, 'form-box__form' ); ?>
			</div>
		</div>
	</div>
</section>
