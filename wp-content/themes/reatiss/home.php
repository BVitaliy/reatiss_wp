<?php
/**
 * Blog page (Settings → Reading → Posts page).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

get_header();
?>

<main>
	<?php
	get_template_part( 'template-parts/global/page-top' );
	get_template_part(
		'template-parts/lists/list',
		null,
		[
			'query'     => $wp_query,
			'type'      => 'post',
			'title'     => (string) rts_option( 'blog_title' ),
			'taxonomy'  => 'category',
			'all_label' => (string) rts_option( 'blog_all_label' ),
			'all_url'   => (string) get_permalink( (int) get_option( 'page_for_posts' ) ),
		]
	);
	?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
