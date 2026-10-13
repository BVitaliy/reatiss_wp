# Security

## AJAX — always verify nonce and register both hooks
```php
// ✅
add_action( 'wp_ajax_rts_load_more',        [ $this, 'handle' ] );
add_action( 'wp_ajax_nopriv_rts_load_more', [ $this, 'handle' ] );

public function handle(): void {
    check_ajax_referer( 'rts_nonce', 'nonce' );
    // ... logic
    wp_send_json_success( $data );
}
```

Omitting `wp_ajax_nopriv_` makes the endpoint silently fail for logged-out
visitors — which is everyone on a public site.

## Pass nonce to JS via wp_localize_script
```php
wp_localize_script( 'rts-global', 'reatiss', [
    'nonce'   => wp_create_nonce( 'rts_nonce' ),
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
] );
```

## Admin-only actions — check capabilities first
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Unauthorized', 'reatiss' ) );
}
```

## Validate before saving, sanitize before use
```php
$page = absint( $_POST['page'] ?? 0 );
if ( $page < 1 ) {
    wp_send_json_error( [ 'message' => 'Invalid page' ] );
}
```

## Never expose ACF output raw
`get_field()` returns whatever the editor typed, including a `wysiwyg` field's HTML.
Escape at the point of output — `esc_html()` / `esc_url()` / `esc_attr()`, or
`wp_kses_post()` when HTML is intended.

## Uploads and file fields
ACF `file` fields return a URL — run it through `esc_url()`. Never build a
filesystem path from user input.
