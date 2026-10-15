<?php
/**
 * Reatiss theme bootstrap.
 *
 * This file only defines constants and pulls in feature files.
 * Put logic in functions/ or includes/, never here.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RTS_URI', get_template_directory_uri() );
define( 'RTS_DIR', get_template_directory() );
define( 'RTS_HOME_URL', home_url( '/' ) );

/**
 * Theme version. Asset URLs are versioned by file modification time
 * (rts_asset_ver()); this is only the fallback.
 */
define( 'RTS_VER', '1.0.0' );

/**
 * All custom logs go to this one folder.
 */
define( 'RTS_LOG_DIR', RTS_DIR . '/logs' );

require_once RTS_DIR . '/functions/class-svg-sanitizer.php';
require_once RTS_DIR . '/functions/helpers.php';
require_once RTS_DIR . '/functions/images.php';
require_once RTS_DIR . '/functions/performance.php';
require_once RTS_DIR . '/functions/class-nav-walker.php';
require_once RTS_DIR . '/functions/breadcrumbs.php';
require_once RTS_DIR . '/functions/builder.php';
require_once RTS_DIR . '/functions/cards.php';
require_once RTS_DIR . '/functions/sections.php';
require_once RTS_DIR . '/functions/lists.php';
require_once RTS_DIR . '/functions/articles.php';
require_once RTS_DIR . '/functions/search.php';
require_once RTS_DIR . '/functions/post-types.php';

require_once RTS_DIR . '/functions/init.php';
require_once RTS_DIR . '/functions/admin.php';
require_once RTS_DIR . '/functions/admin-cleanup.php';
require_once RTS_DIR . '/functions/admin-editor.php';

if ( is_admin() ) {
	require_once RTS_DIR . '/functions/admin-duplicate.php';
	require_once RTS_DIR . '/functions/admin-block-previews.php';
}

require_once RTS_DIR . '/functions/class-admin-bar.php';
require_once RTS_DIR . '/functions/media.php';
require_once RTS_DIR . '/functions/security.php';
require_once RTS_DIR . '/functions/migration.php';
require_once RTS_DIR . '/functions/forms.php';
require_once RTS_DIR . '/functions/shortcodes.php';

// ACF 6.8.7+ rejects SVG in image fields; this scopes an exemption to icon fields.
if ( class_exists( 'ACF' ) ) {
	require_once RTS_DIR . '/functions/class-svg-acf.php';
}

// ACF field groups live in acf-json/.
if ( class_exists( 'ACF' ) ) {
	require_once RTS_DIR . '/includes/acf/register-acf-fields.php';
}
