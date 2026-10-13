<?php
/**
 * Statement — big text that plays once in view (app-statement.js): word
 * fill (statement.php) or letter decode (statement-decode.php); centred or
 * on the right (expertise, with its own line). Opts out of the title decode.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['text'] ) ) {
	return;
}

$rts_right   = 'right' === ( $args['align'] ?? '' );
$rts_classes = 'statement';
$rts_classes .= 'decode' === ( $args['animation'] ?? '' ) ? ' statement--decode' : '';
$rts_classes .= $rts_right ? ' statement--right expertise-detail__statement' : '';
?>
<section class="<?php echo esc_attr( $rts_classes ); ?>" data-title-anim="off">
	<?php if ( $rts_right ) { ?>
		<div class="run-line statement__line js-run-line" data-duration="14" data-start="0.52" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 391" preserveAspectRatio="xMidYMin slice" fill="none">
				<path class="run-line__path js-run-path" d="M905.5 0V153.5C905.5 185.533 879.53 211.5 847.5 211.5H501.486C480.765 211.5 461.618 222.555 451.257 240.5L381.686 361C371.325 378.945 352.178 390 331.457 390H-130" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 22.7%; --y: 68.3%;"></span>
		</div>
	<?php } else { ?>
		<div class="run-line statement__line js-run-line" data-duration="14" data-start="0.53" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 391" preserveAspectRatio="xMidYMin slice" fill="none">
				<path class="run-line__path js-run-path" d="M848.5 0V153.5C848.5 185.533 822.533 211.5 790.5 211.5H444.486C423.765 211.5 404.618 222.555 394.257 240.5L324.686 361C314.325 378.945 295.178 390 274.457 390H-187" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 19.7%; --y: 68.1%;"></span>
		</div>
	<?php } ?>

	<div class="container">
		<p class="statement__text h3 js-statement"><?php echo esc_html( $args['text'] ); ?></p>
	</div>
</section>
