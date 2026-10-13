<?php
/**
 * Team — Figma 8271:40288 (+ line 8278:46397). Person cards (.lab-card--person)
 * in the list grid; a card opens the shared team popup, filled from its
 * <template class="js-team-bio"> by app-about.js (Figma 8271:40574).
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_posts = rts_card_posts( 'rts_team', $args['items'] ?? [] );

if ( empty( $rts_posts ) ) {
	return;
}
?>
<section class="about-team">
	<div class="run-line about-team__line js-run-line" data-duration="26" data-start="0.17" aria-hidden="true">
		<svg class="run-line__svg" viewBox="0 0 1920 1701" preserveAspectRatio="xMidYMin slice" fill="none">
			<path class="run-line__path js-run-path" d="M2192.5 1700H1731.043C1710.322 1700 1691.175 1688.95 1680.814 1671L1611.243 1550.5C1600.882 1532.55 1581.735 1521.5 1561.014 1521.5H1215C1182.97 1521.5 1157 1495.53 1157 1463.5V1310V609.5C1157 577.468 1131.03 551.5 1099 551.5H260.5C228.47 551.5 202.5 525.533 202.5 493.5V58.5C202.5 26.468 176.53 0.5 144.5 0.5H-28.5" />
		</svg>
		<span class="run-line__dot js-run-dot" style="--x: 84.7%; --y: 92.7%;"></span>
	</div>

	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title h2" style="--title-w: 16em"><?php rts_br( $args['title'] ?? '' ); ?></h2>
		</div>

		<ul class="cards-grid">
			<?php foreach ( $rts_posts as $rts_post ) { ?>
				<li>
					<?php get_template_part( 'template-parts/cards/person', null, [ 'post' => $rts_post ] ); ?>
				</li>
			<?php } ?>
		</ul>
	</div>
</section>
