<?php
/**
 * Template Name: Page builder
 *
 * A page built from the builder blocks — every block is available here.
 * Pages on the default template are text pages (title + editor).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<?php rts_builder_page(); ?>
</main>

<?php
get_footer();
