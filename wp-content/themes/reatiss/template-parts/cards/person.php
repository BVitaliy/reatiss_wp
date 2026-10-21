<?php
/**
 * Team member card (.lab-card--person) + the popup content in a <template>
 * (copied into the shared team panel by app-about.js).
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

$rts_name    = get_the_title( $rts_post );
$rts_role    = (string) get_post_meta( $rts_post->ID, 'role', true );
$rts_thumb   = (int) get_post_thumbnail_id( $rts_post );
$rts_bio     = (string) get_post_meta( $rts_post->ID, 'bio', true );
$rts_socials = function_exists( 'get_field' ) ? get_field( 'socials', $rts_post->ID ) : [];
$rts_icons   = [
	'facebook'  => [ 'share-facebook.svg', 'Facebook' ],
	'instagram' => [ 'share-instagram.svg', 'Instagram' ],
	'x'         => [ 'share-x.svg', 'X' ],
	'tiktok'    => [ 'tiktok.svg', 'TikTok' ],
	'linkedin'  => [ 'linkedin.svg', 'LinkedIn' ],
];
?>
<a href="#team-<?php echo esc_attr( (string) $rts_post->ID ); ?>" class="lab-card lab-card--person is-hover-arrow js-team-open" data-modal-open="team" aria-haspopup="dialog"<?php echo rts_aria_label( get_the_title( $rts_post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
	<div class="lab-card__media cut cut--tr">
		<?php rts_picture( $rts_thumb, null, [ 'alt' => $rts_name . ( '' !== $rts_role ? ', ' . $rts_role : '' ) ], 'large' ); ?>
	</div>
	<div class="lab-card__panel cut cut--bl">
		<h3 class="lab-card__title h5"><?php echo esc_html( $rts_name ); ?></h3>
		<?php if ( '' !== $rts_role ) { ?>
			<p class="lab-card__text text-md"><?php echo esc_html( $rts_role ); ?></p>
		<?php } ?>
		<span class="link-arrow"><?php esc_html_e( 'Learn more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></span>
	</div>
</a>
<template class="js-team-bio">
	<div class="team-modal">
		<?php if ( $rts_thumb ) { ?>
			<div class="team-modal__photo cut cut--tr">
				<?php rts_picture( $rts_thumb, null, [ 'alt' => $rts_name, 'loading' => 'eager' ], 'large' ); ?>
			</div>
		<?php } ?>
		<div class="team-modal__body">
			<div class="team-modal__head">
				<p class="team-modal__name h3" id="team-modal-title"><?php echo esc_html( $rts_name ); ?></p>
				<?php if ( '' !== $rts_role ) { ?>
					<p class="team-modal__role text-lg"><?php echo esc_html( $rts_role ); ?></p>
				<?php } ?>
			</div>
			<?php if ( ! empty( $rts_socials ) && is_array( $rts_socials ) ) { ?>
				<ul class="social-list team-modal__socials">
					<?php
					foreach ( $rts_socials as $rts_social ) {
						$rts_icon = $rts_icons[ $rts_social['icon'] ?? '' ] ?? null;

						if ( ! $rts_icon || empty( $rts_social['link'] ) ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url( $rts_social['link'] ); ?>" class="social-btn social-btn--light" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: person, 2: network */ __( '%1$s on %2$s', 'reatiss' ), $rts_name, $rts_icon[1] ) ); ?>" target="_blank" rel="noopener"><?php rts_icon( 'icons/' . $rts_icon[0] ); ?></a></li>
					<?php } ?>
				</ul>
			<?php } ?>
			<?php if ( '' !== $rts_bio ) { ?>
				<div class="team-modal__text rich-text text-lg">
					<?php echo rts_label_links( wp_kses_post( wpautop( $rts_bio ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses, then escaped labels. ?>
				</div>
			<?php } ?>
		</div>
	</div>
</template>
