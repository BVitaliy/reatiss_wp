<?php
/**
 * Inner page top: breadcrumbs (`.page-top > .container > nav.breadcrumbs`).
 *
 * @package reatiss
 *
 * @var array $args { modifier: '' | 'breadcrumbs--left' }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_items = rts_breadcrumbs();

if ( count( $rts_items ) < 2 ) {
	return;
}

?>
<div class="page-top">
	<div class="container">
		<?php rts_breadcrumbs_nav( $rts_items, (string) ( $args['modifier'] ?? '' ) ); ?>
	</div>
</div>
