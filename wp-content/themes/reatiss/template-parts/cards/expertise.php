<?php
/**
 * Expertise card (home, Expertise page, "Other expertise").
 * Image = featured image (line drawing, drawn in by app-draw.js).
 *
 * @package reatiss
 *
 * @var array $args { post: WP_Post }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_post = $args['post'] ?? null;

if ( ! $rts_post instanceof WP_Post ) {
	return;
}

$rts_text  = (string) get_post_meta( $rts_post->ID, 'card_text', true );
$rts_thumb = (int) get_post_thumbnail_id( $rts_post );
?>
<a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>" class="expertise-card is-hover-arrow">
	<div class="expertise-card__body">
		<h3 class="expertise-card__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></h3>
		<div class="expertise-card__foot">
			<?php if ( '' !== $rts_text ) { ?>
				<p class="expertise-card__text text-md"><?php echo esc_html( $rts_text ); ?></p>
			<?php } ?>
			<span class="link-arrow"><?php esc_html_e( 'Learn more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></span>
		</div>
	</div>
	<?php if ( $rts_thumb ) { ?>
		<div class="expertise-card__media js-draw">
			<?php rts_picture( $rts_thumb, null, [], 'large' ); ?>
		</div>
	<?php } ?>
</a>
