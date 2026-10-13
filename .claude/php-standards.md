# PHP Standards

## Classes — `RTS_` prefix, PascalCase
```php
// ✅
class RTS_Theme {}
class RTS_Nav_Walker extends Walker_Nav_Menu {}
```

## Hooks — class methods only
Feature code registers its hooks in a constructor, then the file instantiates once
at the bottom. No loose `add_action()` with a global function name.

```php
// ✅
class RTS_Theme {
    public function __construct() {
        add_action( 'after_setup_theme',  [ $this, 'setup' ] );
        add_filter( 'script_loader_tag',  [ $this, 'add_defer' ], 10, 2 );
    }
}
new RTS_Theme();

// ❌
add_action( 'init', 'rts_setup' );
```

Exception: small template helpers in `functions/helpers.php` stay plain functions —
they are called from templates, not hooked.

## Sanitize inputs, escape outputs — always
```php
// ✅
$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
echo esc_html( $name );
echo esc_url( $url );
echo wp_kses_post( $content );

// ✅ Constants — no escaping needed, value is trusted and hardcoded
echo RTS_URI;

// ❌
echo $_POST['name'];
echo get_field( 'link' ); // ACF output without esc_url()
```

## Database queries — always prepared
```php
// ✅
$wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}posts WHERE ID = %d", $id
) );

// ❌
$wpdb->get_results( "SELECT * FROM wp_posts WHERE ID = $id" );
```

## PHP tags and `if` / `foreach` in HTML templates
In partials that mix HTML and PHP, keep the opening tag and control keyword on the
**same line** — this keeps the markup diffable against the original static templates.

```php
// ✅
<?php if ( ! empty( $email_addr ) && is_email( $email_addr ) ) { ?>
    <a class="contacts__link" href="mailto:<?php echo esc_attr( $email_addr ); ?>">...</a>
<?php } ?>

// ❌
<?php
if ( ! empty( $email_addr ) && is_email( $email_addr ) ) {
```

## Partial guard
Every file under `includes/`, `template-parts/`, and `templates/` starts with:

```php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

## Queries in templates — `WP_Query` with explicit args
Always set `post_type`, `post_status`, `posts_per_page`. Never rely on defaults.
Reset with `wp_reset_postdata()` after a custom loop.
