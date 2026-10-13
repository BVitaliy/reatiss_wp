<?php
/**
 * Event card (.news-card--event: date + location chip).
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

$rts_thumb    = (int) get_post_thumbnail_id( $rts_post );
$rts_dates    = rts_event_dates( $rts_post->ID );
$rts_location = (string) get_post_meta( $rts_post->ID, 'location', true );
?>
<a href="<?php echo esc_url( get_permalink( $rts_post ) ); ?>" class="news-card news-card--event is-hover-arrow">
	<span class="news-card__media cut cut--bl">
		<?php rts_picture( $rts_thumb, null, [], 'large' ); ?>
	</span>
	<span class="news-card__body">
		<span class="news-card__title h5"><?php echo esc_html( get_the_title( $rts_post ) ); ?></span>
		<span class="news-card__meta">
			<span class="news-card__info">
				<?php if ( '' !== $rts_dates ) { ?>
					<span class="news-card__date"><?php rts_icon( 'icons/calendar.svg' ); ?><?php echo esc_html( $rts_dates ); ?></span>
				<?php } ?>
				<?php if ( '' !== $rts_location ) { ?>
					<span class="chip"><?php echo esc_html( $rts_location ); ?></span>
				<?php } ?>
			</span>
			<span class="link-arrow"><?php esc_html_e( 'Read more', 'reatiss' ); ?> <?php rts_icon( 'icons/arrow.svg' ); ?></span>
		</span>
	</span>
</a>
