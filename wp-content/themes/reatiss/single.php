<?php
/**
 * Blog post / event: the article page + related items.
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
		get_template_part( 'template-parts/article/article' );
		?>
		<div class="spacer-xl"></div>
		<?php
		get_template_part( 'template-parts/article/related' );
	}
	?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
