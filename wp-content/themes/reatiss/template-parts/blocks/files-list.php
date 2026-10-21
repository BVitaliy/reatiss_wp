<?php
/**
 * Reports — files / links list above the request form (no own mockup:
 * the On Shelf list with an icon on the left). A file downloads, a link opens
 * (external → new tab). Icon: pdf.svg for a file, share-link.svg for a link.
 *
 * @package reatiss
 *
 * @var array $args Builder row.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_rows = [];

foreach ( is_array( $args['items'] ?? null ) ? $args['items'] : [] as $rts_item ) {
	$rts_is_file = 'link' !== ( $rts_item['type'] ?? 'file' );
	$rts_link    = $rts_is_file
		? ( ! empty( $rts_item['file'] ) ? [ 'url' => (string) $rts_item['file'], 'new_tab' => false, 'modal' => '' ] : null )
		: rts_link( $rts_item['link'] ?? [] );

	if ( $rts_link && ! empty( $rts_item['title'] ) ) {
		$rts_rows[] = [ $rts_item, $rts_link, $rts_is_file ];
	}
}

if ( empty( $args['title'] ) && empty( $rts_rows ) ) {
	return;
}
?>
<section class="service-files">
	<div class="container">
		<?php if ( ! empty( $args['title'] ) ) { ?>
			<div class="section-head">
				<?php rts_title( $args, 'h2', 'section-head__title h2' ); ?>
			</div>
		<?php } ?>

		<?php if ( ! empty( $rts_rows ) ) { ?>
		<div class="service-files__inner">
			<ul class="result-list">
				<?php
				foreach ( $rts_rows as $rts_row ) {
					list( $rts_item, $rts_link, $rts_is_file ) = $rts_row;
					/* translators: %s: file title */
					$rts_label = $rts_is_file ? sprintf( __( 'Download: %s', 'reatiss' ), $rts_item['title'] ) : $rts_item['title'];
					?>
					<li>
						<span class="step-line"></span>
						<a class="result-row result-row--desc result-row--icon"<?php echo rts_link_attrs( $rts_link, $rts_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in rts_link_attrs(). ?><?php echo $rts_is_file ? ' download' : ''; ?>>
							<span class="result-row__icon"><?php rts_icon( $rts_is_file ? 'icons/pdf.svg' : 'icons/share-link.svg' ); ?></span>
							<span class="result-row__body">
								<span class="result-row__title h5"><?php echo esc_html( $rts_item['title'] ); ?></span>
								<?php if ( ! empty( $rts_item['text'] ) ) { ?>
									<span class="result-row__text text-lg"><?php echo esc_html( $rts_item['text'] ); ?></span>
								<?php } ?>
							</span>
							<?php rts_icon( 'icons/arrow.svg' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
			<span class="step-line"></span>
		</div>
		<?php } ?>
	</div>
</section>
