<?php
/**
 * On Shelf — Available RE materials · Figma 8422:36759 (table 8469:26153,
 * line 8356:36530). Title + optional text, then a two-column table (part
 * number and die marking | functionality; the mockup's "№" column is left
 * out — client). Rows open the "Request access to the Reports" popup with
 * the report's description above the form; Show more + pages (10 a page,
 * /on-shelf/page/2/).
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_query = rts_list_query( 'rts_report', max( 1, (int) get_query_var( 'paged' ) ) );
$rts_cols  = [
	! empty( $args['col_part'] ) ? (string) $args['col_part'] : __( 'Part number and die marking', 'reatiss' ),
	! empty( $args['col_func'] ) ? (string) $args['col_func'] : __( 'Functionality', 'reatiss' ),
];

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
		<div class="section-head shelf__head">
			<?php rts_title( $args, 'h1', 'section-head__title h2', '--title-w: 10.4em; --title-wrap: wrap', ! empty( $args['title'] ) ? (string) $args['title'] : esc_html( get_the_title() ) ); ?>

			<?php if ( ! empty( $args['text'] ) ) { ?>
				<div class="shelf__text rich-text text-lg">
					<?php echo rts_label_links( wp_kses_post( $args['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses, then escaped labels. ?>
				</div>
			<?php } ?>
		</div>

		<div class="shelf__inner shelf-table">
			<div class="shelf-table__head" aria-hidden="true">
				<?php foreach ( $rts_cols as $rts_col ) { ?>
					<span class="shelf-table__cell h5"><?php echo esc_html( $rts_col ); ?></span>
				<?php } ?>
			</div>
			<ul class="shelf-table__body js-load-more-list" aria-label="<?php echo esc_attr( implode( ' · ', $rts_cols ) ); ?>">
				<?php foreach ( $rts_query->posts as $rts_post ) { ?>
					<li><?php get_template_part( 'template-parts/cards/report', null, [ 'post' => $rts_post ] ); ?></li>
				<?php } ?>
			</ul>
		</div>

		<?php rts_pager( $rts_query, [ 'type' => 'rts_report' ], 'shelf__pager' ); ?>
	</div>
</section>
