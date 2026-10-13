<?php
/**
 * Single service / lab / expertise — the page builder, same as pages
 * (picked through `single_template_hierarchy` in functions/builder.php).
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
