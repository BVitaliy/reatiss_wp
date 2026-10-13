<?php
/**
 * Lab card (home labs, Labs page, Services page).
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
<a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>" class="lab-card is-hover-arrow">
	<div class="lab-card__media cut cut--tr">
		<?php rts_picture( $rts_thumb, null, [ 'alt' => get_the_title( $rts_post ) ], 'large' ); ?>
	</div>
	<div class="lab-card__panel cut cut--bl">
		<h3 class="lab-card__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></h3>
		<?php if ( '' !== $rts_text ) { ?>
			<p class="lab-card__text text-md"><?php echo esc_html( $rts_text ); ?></p>
		<?php } ?>
		<span class="link-arrow"><?php esc_html_e( 'Learn more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></span>
	</div>
</a>
