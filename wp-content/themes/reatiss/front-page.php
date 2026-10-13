<?php
/**
 * Front page — blocks from the page builder. The page colour switches to dark
 * while a dark section (solutions, stats) holds the screen (app-page-theme.js).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="js-page-theme">
	<?php rts_render_builder(); ?>
</main>

<?php
get_footer();
