<?php
/**
 * Article — Figma 7999:18510 (blog post) and its event variant 8031:20617.
 * Head: date + author pills (event: date + location), title; the editor
 * content (.article-content.rich-text, inserts via shortcodes); share +
 * tags. With “In this article navigation” on, the sticky "In this article" list is built from
 * the content's <h2>s by app-article.js.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_post     = get_post();
$rts_is_event = 'rts_event' === $rts_post->post_type;
$rts_toc_set  = function_exists( 'get_field' ) ? get_field( 'toc' ) : null;
$rts_toc      = ! $rts_is_event && ( null === $rts_toc_set || ! empty( $rts_toc_set ) ); // on until switched off
$rts_terms    = get_the_terms( $rts_post, $rts_is_event ? 'rts_event_cat' : 'post_tag' );
$rts_location = $rts_is_event ? (string) get_post_meta( $rts_post->ID, 'location', true ) : '';
$rts_date     = $rts_is_event ? rts_event_dates( $rts_post->ID ) : get_the_date();
?>
<article class="article-page<?php echo $rts_toc ? ' article-page--nav' : ''; ?><?php echo $rts_is_event ? ' article-page--event' : ''; ?>" data-article-page>
	<header class="container article-page__head">
		<div class="article-page__meta">
			<?php if ( '' !== $rts_date ) { ?>
				<span class="article-page__pill"><?php rts_icon( 'icons/calendar.svg' ); ?><?php echo esc_html( $rts_date ); ?></span>
			<?php } ?>
			<?php if ( $rts_is_event && '' !== $rts_location ) { ?>
				<span class="article-page__pill"><?php echo esc_html( $rts_location ); ?></span>
			<?php } elseif ( ! $rts_is_event ) { ?>
				<a href="<?php echo esc_url( get_author_posts_url( (int) $rts_post->post_author ) ); ?>" class="article-page__pill"><?php rts_icon( 'icons/user.svg' ); ?><?php echo esc_html( get_the_author_meta( 'display_name', (int) $rts_post->post_author ) ); ?></a>
			<?php } ?>
		</div>
		<h1 class="article-page__title h3"><?php the_title(); ?></h1>
	</header>

	<div class="container article-page__layout">
		<div class="article-content rich-text text-lg">
			<?php the_content(); ?>

			<div class="article-share">
				<div class="article-share__group">
					<p class="h5"><?php echo esc_html( (string) rts_option( 'share_title' ) ); ?></p>
					<?php rts_share_buttons(); ?>
				</div>
				<?php if ( ! empty( $rts_terms ) && ! is_wp_error( $rts_terms ) ) { ?>
					<ul class="chips article-share__tags">
						<?php foreach ( $rts_terms as $rts_term ) { ?>
							<li><a href="<?php echo esc_url( (string) get_term_link( $rts_term ) ); ?>" class="chip chip--white"><?php echo esc_html( $rts_term->name ); ?></a></li>
						<?php } ?>
					</ul>
				<?php } ?>
			</div>
		</div>

		<?php if ( $rts_toc ) { ?>
	<aside class="article-toc" data-article-toc>
		<div class="article-toc__bar">
			<button class="article-toc__toggle" type="button" aria-expanded="false" data-article-toc-toggle>
				<span class="article-toc__current" data-article-toc-current><?php esc_html_e( 'In this article', 'reatiss' ); ?></span>
				<?php rts_icon( 'icons/chevron.svg' ); ?>
			</button>
			<nav class="article-toc__panel" aria-label="<?php esc_attr_e( 'In this article', 'reatiss' ); ?>">
				<div class="article-toc__panel-inner" data-lenis-prevent>
					<p class="article-toc__title h5"><?php esc_html_e( 'In this article', 'reatiss' ); ?></p>
					<ul class="article-toc__list"></ul>
				</div>
			</nav>
		</div>
	</aside>
		<?php } ?>
	</div>
</article>
