<?php
/**
 * Default page template: page builder blocks, or — when the page has none —
 * a text page (Privacy policy, Terms of use, Public offer).
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
	if ( ! empty( rts_builder_rows() ) ) {
		rts_builder_page();
	} else {
		get_template_part( 'template-parts/global/page-top' );

		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/text-page' );
		}
		?>
		<div class="spacer-xl"></div>
		<?php
	}
	?>
</main>

<?php
get_footer();
