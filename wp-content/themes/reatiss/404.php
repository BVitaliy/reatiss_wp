<?php
/**
 * 404 · Figma 8038:21975.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<?php get_template_part( 'template-parts/error-page' ); ?>
</main>

<?php
get_footer();
