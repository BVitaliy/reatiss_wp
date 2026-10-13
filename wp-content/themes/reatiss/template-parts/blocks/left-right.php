<?php
/**
 * Text + photo (.left-right) — home About (7967:9007), Let's work together
 * (7967:9105), About / Labs / service / expertise intros. Photo drifts on
 * scroll (app-parallax.js); stacks below 992.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_variants = [
	'text-media' => '',
	'media-text' => ' left-right--reverse',
	'media-left' => ' left-right--media-left',
];
$rts_variant  = $rts_variants[ $args['variant'] ?? '' ] ?? '';
?>
<section class="left-right<?php echo esc_attr( $rts_variant ); ?>">
	<div class="container left-right__grid">
		<div class="left-right__content">
			<h2 class="left-right__title h3"><?php rts_br( $args['title'] ?? '' ); ?></h2>
			<span class="step-line left-right__line"></span>
			<?php if ( ! empty( $args['text'] ) ) { ?>
				<div class="left-right__text rich-text text-lg">
					<?php echo wp_kses_post( $args['text'] ); ?>
				</div>
			<?php } ?>
			<?php
			$rts_files = is_array( $args['files'] ?? null ) ? array_filter( $args['files'], static fn( $row ) => ! empty( $row['file'] ) ) : [];

			if ( ! empty( $rts_files ) ) {
				// Report files instead of the button (service "Process Report Example"):
				// the title opens the PDF in a new tab, the icon downloads it.
				?>
				<ul class="result-list result-list--files left-right__files">
					<?php foreach ( $rts_files as $rts_file ) { ?>
						<li>
							<span class="step-line"></span>
							<div class="result-row result-row--file">
								<a class="result-row__title text-lg" href="<?php echo esc_url( $rts_file['file'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( (string) ( $rts_file['title'] ?? '' ) ); ?></a>
								<a class="result-row__file" href="<?php echo esc_url( $rts_file['file'] ); ?>" download aria-label="<?php echo esc_attr( sprintf( /* translators: %s: file title */ __( 'Download PDF: %s', 'reatiss' ), (string) ( $rts_file['title'] ?? '' ) ) ); ?>"><?php rts_icon( 'icons/pdf.svg' ); ?></a>
							</div>
						</li>
					<?php } ?>
				</ul>
				<span class="step-line"></span>
				<?php
			} else {
				rts_button( $args['btn'] ?? [], 'btn btn--primary left-right__btn' );
			}
			?>
		</div>

		<?php if ( rts_image_id( $args['image'] ?? 0 ) ) { ?>
			<div class="left-right__media" data-parallax-media data-parallax-intensity="120">
				<?php
				// The picture itself is the parallax layer.
				ob_start();
				rts_picture( $args['image'], $args['image_mobile'] ?? 0, [], 'large' );
				echo str_replace( '<picture>', '<picture data-parallax-target>', (string) ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_picture().
				?>
			</div>
		<?php } ?>
	</div>
</section>
