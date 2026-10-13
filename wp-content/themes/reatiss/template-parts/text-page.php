<?php
/**
 * Text page (Privacy policy, Terms of use, Public offer) — Figma 8031:21592.
 * Centred title + one 1236 column of editor content (.rich-text, text-lg).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="text-page">
	<div class="container">
		<div class="section-head text-page__head">
			<h1 class="section-head__title h2"><?php the_title(); ?></h1>
		</div>

		<div class="text-page__content rich-text text-lg">
			<?php the_content(); ?>
		</div>
	</div>
</section>
