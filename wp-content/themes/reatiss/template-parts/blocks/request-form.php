<?php
/**
 * Request form — Figma 8102:23473. The shared .form-box centred (925),
 * its own cut; the CF7 form chosen in the block or Site Settings → Forms.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_form = ! empty( $args['form'] ) ? (int) $args['form'] : (int) rts_option( 'request_form' );

$rts_has_form = rts_has_form( $rts_form );

if ( ! $rts_has_form && empty( $args['title'] ) ) {
	return;
}
?>
<section class="request-form">
	<div class="container">
		<div class="request-form__box form-box cut cut--br">
			<div class="form-box__form">
				<?php rts_title( $args, 'h2', 'form-box__title h3' ); ?>
				<?php
				if ( $rts_has_form ) {
					rts_form( $rts_form, 'form-box__form' );
				}
				?>
			</div>
		</div>
	</div>
</section>
