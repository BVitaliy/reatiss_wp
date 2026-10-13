<?php
/**
 * Author — Figma 8031:20994: photo | name, role, socials, bio (.more-text),
 * then the author's articles (Figma 8031:21519) with the pager.
 * Fields: Users → profile → «Сторінка автора».
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$rts_author  = get_queried_object();
$rts_field   = 'user_' . $rts_author->ID;
$rts_photo   = function_exists( 'get_field' ) ? get_field( 'photo', $rts_field ) : null;
$rts_role    = function_exists( 'get_field' ) ? (string) get_field( 'role', $rts_field ) : '';
$rts_socials = function_exists( 'get_field' ) ? get_field( 'socials', $rts_field ) : [];
$rts_bio     = function_exists( 'get_field' ) ? (string) get_field( 'bio', $rts_field ) : '';
$rts_more    = function_exists( 'get_field' ) ? trim( (string) get_field( 'bio_more', $rts_field ) ) : '';
$rts_name    = $rts_author->display_name;
$rts_icons   = [
	'facebook'  => [ 'share-facebook.svg', 'Facebook' ],
	'instagram' => [ 'share-instagram.svg', 'Instagram' ],
	'x'         => [ 'share-x.svg', 'X' ],
	'tiktok'    => [ 'tiktok.svg', 'TikTok' ],
	'linkedin'  => [ 'linkedin.svg', 'LinkedIn' ],
];

get_header();
?>

<main>
	<?php get_template_part( 'template-parts/global/page-top', null, [ 'modifier' => 'breadcrumbs--left' ] ); ?>

	<section class="author-hero">
		<div class="container author-hero__grid">
			<?php if ( rts_image_id( $rts_photo ) ) { ?>
				<div class="author-hero__media">
					<?php rts_picture( $rts_photo, null, [ 'alt' => $rts_name, 'loading' => 'eager', 'fetchpriority' => 'high' ], 'large' ); ?>
				</div>
			<?php } ?>

			<div class="author-hero__content">
				<div class="author-hero__head">
					<h1 class="author-hero__name h3"><?php echo esc_html( $rts_name ); ?></h1>
					<?php if ( '' !== $rts_role ) { ?>
						<p class="author-hero__role text-lg"><?php echo esc_html( $rts_role ); ?></p>
					<?php } ?>
				</div>

				<?php if ( ! empty( $rts_socials ) && is_array( $rts_socials ) ) { ?>
					<ul class="social-list author-hero__socials">
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

				<span class="step-line author-hero__line"></span>

				<?php if ( '' !== trim( $rts_bio ) ) { ?>
					<div class="more-text more-text--diag author-hero__bio js-more-text">
						<div class="more-text__visible rich-text text-lg">
							<?php echo wp_kses_post( $rts_bio ); ?>
						</div>
						<?php if ( '' !== $rts_more ) { ?>
							<div class="more-text__hidden" id="author-more">
								<div class="more-text__hidden-inner rich-text text-lg">
									<?php echo wp_kses_post( $rts_more ); ?>
								</div>
							</div>
							<button class="more-text__toggle link-arrow js-more-text-toggle" type="button" aria-expanded="false" aria-controls="author-more">
								<span class="more-text__more"><?php esc_html_e( 'Read more', 'reatiss' ); ?></span>
								<span class="more-text__less"><?php esc_html_e( 'Show less', 'reatiss' ); ?></span>
								<?php rts_icon( 'icons/arrow.svg' ); ?>
							</button>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</section>

	<?php if ( have_posts() ) { ?>
		<div class="spacer-xl"></div>
		<section class="author-articles">
			<div class="container">
				<div class="section-head">
					<h2 class="section-head__title h2" style="--title-w: 12.4em"><?php echo esc_html( (string) rts_option( 'author_posts_title' ) ); ?></h2>
				</div>

				<ul class="cards-grid js-load-more-list">
					<?php foreach ( $wp_query->posts as $rts_post ) { ?>
						<li><?php get_template_part( 'template-parts/cards/news', null, [ 'post' => $rts_post ] ); ?></li>
					<?php } ?>
				</ul>

				<?php
				rts_pager(
					$wp_query,
					[
						'type'   => 'post',
						'author' => $rts_author->ID,
					],
					'author-articles__pager'
				);
				?>
			</div>
		</section>
	<?php } ?>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
