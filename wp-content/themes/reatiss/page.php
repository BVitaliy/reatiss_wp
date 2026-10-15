<?php
/**
 * Default page template: a text page (Privacy policy, Terms of use, Public
 * offer). Pages built from blocks use the “Page builder” template.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<?php
	get_template_part( 'template-parts/global/page-top' );

	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/text-page' );
	}
	?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
