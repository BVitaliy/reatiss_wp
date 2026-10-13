<?php
/**
 * On Shelf — Available Analysis Reports · Figma 8271:41619 (+ line 8356:36530).
 * Report rows open the "Request access to the Reports" popup; Show more +
 * pages (10 a page, /on-shelf/page/2/).
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_query = rts_list_query( 'rts_report', max( 1, (int) get_query_var( 'paged' ) ) );

if ( ! $rts_query->have_posts() ) {
	return;
}
?>
<section class="list-page shelf">
	<div class="run-line list-page__line shelf__line js-run-line" data-duration="20" data-start="0.836" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 545" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M2398.5 544H2358.61C2328.97 544 2304.1 521.657 2300.94 492.189L2279.06 288.311C2275.9 258.843 2251.03 236.5 2221.39 236.5H1829.5C1797.47 236.5 1771.5 210.533 1771.5 178.5V60C1771.5 27.9675 1745.03 0.5 1713 0.5H252.5C220.467 0.5 194.5 26.4675 194.5 58.5V406C194.5 438.033 168.533 464 136.5 464H-299" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 10.1%; --y: 65.1%;"></span>
	</div>
	<div class="container">
		<div class="section-head">
			<h1 class="section-head__title h2" style="--title-w: 10.4em; --title-wrap: wrap"><?php rts_br( ! empty( $args['title'] ) ? $args['title'] : esc_html( get_the_title() ) ); ?></h1>
		</div>

		<div class="shelf__inner">
			<ul class="result-list js-load-more-list">
				<?php foreach ( $rts_query->posts as $rts_post ) { ?>
					<li><?php get_template_part( 'template-parts/cards/report', null, [ 'post' => $rts_post ] ); ?></li>
				<?php } ?>
			</ul>
			<span class="step-line"></span>
		</div>

		<?php rts_pager( $rts_query, [ 'type' => 'rts_report' ], 'shelf__pager' ); ?>
	</div>
</section>
