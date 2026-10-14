<?php
/**
 * Search results — Figma 7982:27491: title, total, the search field, result
 * rows, pager (Show more + pages). Query: functions/search.php.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

get_header();
?>

<main>
	<?php get_template_part( 'template-parts/global/page-top' ); ?>

	<section class="search-page">
		<div class="run-line search-page__line js-run-line" data-duration="16" data-start="0.71" aria-hidden="true">
			<svg class="run-line__svg" viewBox="0 0 1920 1752" fill="none">
				<path class="run-line__path js-run-path" d="M324.169 1752V652.811C321.007 623.343 296.137 601 266.5 601H156C123.967 601 98 575.033 98 543V212.5V60C98 27.9675 71.5325 0.5 39.5 0.5H-243" />
			</svg>
			<span class="run-line__dot js-run-dot" style="--x: 5.1%; --y: 19.5%;"></span>
		</div>

		<div class="container">
			<div class="search-page__inner">
				<div class="search-page__head">
					<h1 class="h2"><?php echo esc_html( (string) rts_option( 'search_title' ) ); ?></h1>
					<p class="search-page__found text-md"><?php esc_html_e( 'Found:', 'reatiss' ); ?> <b><?php echo esc_html( number_format_i18n( (int) $wp_query->found_posts ) ); ?></b></p>
					<form class="search-page__form" action="<?php echo esc_url( RTS_HOME_URL ); ?>" role="search">
						<label class="search-field search-field--grey">
							<?php rts_icon( 'icons/search.svg' ); ?>
							<span class="visually-hidden"><?php esc_html_e( 'Search', 'reatiss' ); ?></span>
							<input class="search-field__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'reatiss' ); ?>" autocomplete="off">
							<button class="search-field__btn js-search-clear" type="button" aria-label="<?php esc_attr_e( 'Clear search', 'reatiss' ); ?>">
								<?php rts_icon( 'icons/close.svg' ); ?>
							</button>
						</label>
					</form>
				</div>

				<?php if ( have_posts() ) { ?>
					<ul class="result-list js-load-more-list">
						<?php foreach ( $wp_query->posts as $rts_post ) { ?>
							<li><?php get_template_part( 'template-parts/cards/search-row', null, [ 'post' => $rts_post ] ); ?></li>
						<?php } ?>
					</ul>
					<span class="step-line"></span>

					<?php
					rts_pager(
						$wp_query,
						[
							'type' => 'search',
							's'    => get_search_query( false ),
						],
						'search-page__pager'
					);
					?>
				<?php } else { ?>
					<p class="search-page__empty text-lg"><?php echo esc_html( (string) rts_option( 'search_empty' ) ); ?></p>
				<?php } ?>
			</div>
		</div>
	</section>
	<div class="spacer-xl"></div>
</main>

<?php
get_footer();
