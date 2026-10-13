<?php
/**
 * Template Name: Події
 *
 * Events list (Figma 8028:20020): the page title + event category chips +
 * event cards two per row + pager. Paged as /events/page/2/.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_query = rts_list_query( 'rts_event', max( 1, (int) get_query_var( 'paged' ) ) );

get_header();
?>

<main>
	<?php
	get_template_part( 'template-parts/global/page-top' );
	get_template_part(
		'template-parts/lists/list',
		null,
		[
			'query'     => $rts_query,
			'type'      => 'rts_event',
			'title'     => (string) rts_option( 'events_title' ),
			'taxonomy'  => 'rts_event_cat',
			'all_label' => (string) rts_option( 'events_all_label' ),
			'all_url'   => (string) get_permalink(),
		]
	);
	?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
