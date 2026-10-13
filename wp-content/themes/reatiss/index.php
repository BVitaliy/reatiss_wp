<?php
/**
 * Fallback template. Every real view has its own template; this one only
 * keeps WordPress happy and lists whatever the query returned.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>
	<?php get_template_part( 'template-parts/global/page-top' ); ?>
	<section class="text-page">
		<div class="container">
			<div class="text-page__content rich-text text-lg">
				<?php
				while ( have_posts() ) {
					the_post();
					printf( '<h2><a href="%s">%s</a></h2>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
				}
				?>
			</div>
		</div>
	</section>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
