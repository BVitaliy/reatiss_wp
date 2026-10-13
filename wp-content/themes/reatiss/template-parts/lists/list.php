<?php
/**
 * List page — Blog (Figma 7989:32257) and Events (8028:20020): line, title +
 * category chips, card grid, pager (Show more + pages).
 *
 * @package reatiss
 *
 * @var array $args {
 *     @type WP_Query $query     List query.
 *     @type string   $type      'post' | 'rts_event'.
 *     @type string   $title     Page title (may hold <br>).
 *     @type string   $taxonomy  Filter taxonomy.
 *     @type string   $all_label "All …" chip.
 *     @type string   $all_url   "All …" chip URL.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_query = $args['query'] ?? null;

if ( ! $rts_query instanceof WP_Query ) {
	return;
}

$rts_is_event = 'rts_event' === ( $args['type'] ?? '' );
$rts_term     = is_tax() || is_category() || is_tag() ? (int) get_queried_object_id() : 0;
?>
<section class="list-page">
	<div class="run-line list-page__line js-run-line" data-duration="18" data-start="0.19" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 404" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M-343 0.433L328.915 386.716C337.71 391.773 347.677 394.433 357.822 394.433H478.054C491.743 394.433 504.991 389.592 515.454 380.764L749.593 183.231C760.056 174.404 773.304 169.562 786.993 169.562H1176.96C1192.3 169.562 1206.97 175.62 1217.94 186.345C1281.39 248.391 1382.53 342.464 1422.05 379.087C1432.74 388.997 1446.73 394.434 1461.31 394.434L1568.25 394.433C1578.48 394.433 1588.53 391.725 1597.38 386.583L2262 0.433" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 19.5%; --y: 97.6%;"></span>
	</div>
	<div class="container">
		<div class="section-head">
			<h1 class="section-head__title h2" style="--title-w: 12.3em<?php echo $rts_is_event ? '; --title-wrap: wrap' : ''; ?>"><?php rts_br( $args['title'] ?? '' ); ?></h1>
			<?php rts_filter_chips( (string) ( $args['taxonomy'] ?? 'category' ), (string) ( $args['all_label'] ?? '' ), (string) ( $args['all_url'] ?? '' ) ); ?>
		</div>

		<?php if ( $rts_query->have_posts() ) { ?>
			<ul class="cards-grid<?php echo $rts_is_event ? ' cards-grid--2' : ''; ?> js-load-more-list">
				<?php foreach ( $rts_query->posts as $rts_post ) { ?>
					<li><?php get_template_part( 'template-parts/cards/' . ( $rts_is_event ? 'event' : 'news' ), null, [ 'post' => $rts_post ] ); ?></li>
				<?php } ?>
			</ul>

			<?php
			rts_pager(
				$rts_query,
				[
					'type'     => $rts_is_event ? 'rts_event' : 'post',
					'taxonomy' => $rts_term ? (string) get_queried_object()->taxonomy : '',
					'term'     => $rts_term,
				]
			);
			?>
		<?php } else { ?>
			<p class="text-lg"><?php esc_html_e( 'Nothing here yet.', 'reatiss' ); ?></p>
		<?php } ?>
	</div>
</section>
