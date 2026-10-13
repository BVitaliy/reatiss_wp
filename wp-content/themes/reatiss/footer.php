<?php
/**
 * Site footer, the shared overlays and the closing tags
 * (markup: inc/_footer.php + inc/_bottom.php).
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/global/site-footer' );
get_template_part( 'template-parts/global/modals' );

wp_footer();
?>
</body>

</html>
