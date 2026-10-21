<?php
/**
 * Section list as the page title block — Services (8271:41098), Labs
 * (8069:21925), Expertise (8102:23334). All published items by “Order”;
 * each list has its own decorative line, as in the markup.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_type  = in_array( $args['type'] ?? '', [ 'rts_service', 'rts_lab', 'rts_expertise' ], true ) ? $args['type'] : 'rts_service';
$rts_posts = rts_card_posts( $rts_type, [] );
$rts_title = ! empty( $args['title'] ) ? (string) $args['title'] : esc_html( get_the_title() );

?>
<?php if ( 'rts_service' === $rts_type ) { ?>
<section class="list-page services">
	<div class="run-line list-page__line services__line js-run-line" data-duration="22" data-start="0.19" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 1727" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M2183 0.5H1721.543C1700.822 0.5 1681.675 11.555 1671.314 29.5L1601.743 150C1591.382 167.945 1572.235 179 1551.514 179H1205.5C1173.467 179 1147.5 204.967 1147.5 237V1370.5C1147.5 1402.53 1173.467 1428.5 1205.5 1428.5H1553.93C1573.339 1428.5 1591.461 1438.21 1602.214 1454.36L1765.786 1700.14C1776.539 1716.29 1794.661 1726 1814.07 1726H1995" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 84.2%; --y: 7.2%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<?php rts_title( $args, 'h1', 'section-head__title h2', '--title-w: 10.4em', $rts_title ); ?>
		</div>

		<?php if ( ! empty( $rts_posts ) ) { ?>
		<ul class="cards-grid">
			<?php foreach ( $rts_posts as $rts_post ) { ?>
				<li><?php get_template_part( 'template-parts/cards/lab', null, [ 'post' => $rts_post, 'heading' => 'h2' ] ); ?></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
<?php } elseif ( 'rts_lab' === $rts_type ) { ?>
<section class="list-page">
	<div class="run-line list-page__line js-run-line" data-duration="18" data-start="0.19" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 404" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M-343 0.433L328.915 386.716C337.71 391.773 347.677 394.433 357.822 394.433H478.054C491.743 394.433 504.991 389.592 515.454 380.764L749.593 183.231C760.056 174.404 773.304 169.562 786.993 169.562H1176.96C1192.3 169.562 1206.97 175.62 1217.94 186.345C1281.39 248.391 1382.53 342.464 1422.05 379.087C1432.74 388.997 1446.73 394.434 1461.31 394.434L1568.25 394.433C1578.48 394.433 1588.53 391.725 1597.38 386.583L2262 0.433" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 19.5%; --y: 97.6%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<?php rts_title( $args, 'h1', 'section-head__title h2', '--title-w: 11.8em', $rts_title ); ?>
		</div>

		<?php if ( ! empty( $rts_posts ) ) { ?>
		<ul class="cards-grid">
			<?php foreach ( $rts_posts as $rts_post ) { ?>
				<li><?php get_template_part( 'template-parts/cards/lab', null, [ 'post' => $rts_post, 'heading' => 'h2' ] ); ?></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
<?php } else { ?>
<section class="list-page">
	<div class="run-line list-page__line expertise-list__line js-run-line" data-duration="22" data-start="0.17" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 1442" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M-310 0.5H151.457C172.178 0.5 191.325 11.55 201.686 29.5L271.257 150C281.618 167.95 300.765 179 321.486 179H667.5C699.53 179 725.5 204.97 725.5 237V1076C725.5 1108.033 751.47 1134 783.5 1134H1430.5C1462.53 1134 1488.5 1159.967 1488.5 1192V1383C1488.5 1415.033 1514.47 1441 1546.5 1441H2022" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 13.3%; --y: 8.8%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<?php rts_title( $args, 'h1', 'section-head__title h2', '', $rts_title ); ?>
		</div>

		<?php if ( ! empty( $rts_posts ) ) { ?>
		<ul class="expertise-list">
			<?php foreach ( $rts_posts as $rts_post ) { ?>
				<li><?php get_template_part( 'template-parts/cards/expertise', null, [ 'post' => $rts_post, 'heading' => 'h2' ] ); ?></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</section>
<?php } ?>
