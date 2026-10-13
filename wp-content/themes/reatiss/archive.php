<?php
/**
 * Archives: blog categories / tags and event categories — the list page
 * filtered by the term (the term's chip is active).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$rts_is_event = is_tax( 'rts_event_cat' );
$rts_section  = rts_section_page_id( $rts_is_event ? 'rts_event' : 'post' );

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
			'type'      => $rts_is_event ? 'rts_event' : 'post',
			'title'     => (string) rts_option( $rts_is_event ? 'events_title' : 'blog_title' ),
			'taxonomy'  => $rts_is_event ? 'rts_event_cat' : 'category',
			'all_label' => (string) rts_option( $rts_is_event ? 'events_all_label' : 'blog_all_label' ),
			'all_url'   => (string) get_permalink( $rts_section ),
		]
	);
	?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
