<?php
/**
 * 404 — Figma 8038:21975. Texts: Site Settings → 404.
 *
 * Text + "Back to home" | "404" (one SVG: the 4s outlined from the mockup,
 * the 0 — in place of the logo mark, client — from the same Jakarta Medium). Two running lines (8038:22449 top, 8054:19447 bottom), from 992 up.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_title = (string) rts_option( 'error_title' );
$rts_text  = (string) rts_option( 'error_text' );
$rts_btn   = (string) rts_option( 'error_btn' );
?>
<section class="error-page">
  <div class="run-line error-page__line error-page__line--top js-run-line" data-duration="18" data-start="0.49" aria-hidden="true">
    <svg class="run-line__svg" viewBox="0 0 1920 314" preserveAspectRatio="xMidYMin slice" fill="none">
      <path class="run-line__path js-run-path" d="M1920 0.5L1628 0.5C1597.9 0.5 1573.5 24.9 1573.5 54.999C1573.5 85.098 1549.1 109.498 1519 109.498L985.995 109.499L790.996 109.498L158.999 109.498C126.966 109.498 100.999 135.466 100.999 167.498L100.999 255.5C100.999 287.532 75.031 313.5 42.999 313.5L-48.002 313.5" />
    </svg>
    <span class="run-line__dot js-run-dot" style="--x: 47.4%; --y: 34.9%;"></span>
  </div>

  <div class="run-line error-page__line error-page__line--bottom js-run-line" data-duration="18" data-start="0.363" aria-hidden="true">
    <svg class="run-line__svg" viewBox="0 0 1920 462" preserveAspectRatio="xMidYMin slice" fill="none">
      <path class="run-line__path js-run-path" d="M-20 787.54L1053.74 217.212C1062.118 212.762 1071.46 210.435 1080.947 210.435H1485.229C1495.947 210.435 1506.455 213.404 1515.587 219.014L1725.326 347.855C1734.456 353.465 1744.966 356.435 1755.686 356.435L1869.186 356.435C1879.246 356.435 1889.136 353.818 1897.876 348.841L2509.996 0.435" />
    </svg>
    <span class="run-line__dot js-run-dot" style="--x: 46%; --y: 68.7%;"></span>
  </div>

  <div class="container error-page__grid">
    <div class="error-page__text">
      <?php if ( '' !== $rts_title ) { ?>
        <h1 class="h2"><?php echo esc_html( $rts_title ); ?></h1>
      <?php } ?>
      <?php if ( '' !== $rts_text ) { ?>
        <p class="error-page__lead text-lg"><?php echo esc_html( $rts_text ); ?></p>
      <?php } ?>
      <?php if ( '' !== $rts_btn ) { ?>
        <a href="<?php echo esc_url( RTS_HOME_URL ); ?>" class="btn btn--primary"<?php echo rts_aria_label( $rts_btn ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
          <span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
          <?php echo esc_html( $rts_btn ); ?>
        </a>
      <?php } ?>
    </div>

    <div class="error-page__art js-error-art">
      <span class="visually-hidden"><?php esc_html_e( 'Error 404', 'reatiss' ); ?></span>
      <?php rts_icon( '404-art.svg' ); ?>
    </div>
  </div>
</section>
