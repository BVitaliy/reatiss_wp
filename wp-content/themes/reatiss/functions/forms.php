<?php
/**
 * Contact Form 7 integration.
 *
 * Forms are edited in CF7 with the markup's field HTML
 * (`label.field > span.field__label + [tag class:field__input] + span.field__error`);
 * `[rts_submit "Send"]` renders the design's arrow button. CF7's own styles
 * and auto-paragraphs are off — the design styles the fields.
 *
 * Front-end behaviour (error states, loader, "thanks" popup): js/app-cf7.js.
 *
 * @package reatiss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RTS_Forms {

	public function __construct() {
		add_action( 'wpcf7_init', [ $this, 'register_tags' ] );
		add_filter( 'wpcf7_load_css', '__return_false' );
		add_filter( 'wpcf7_autop_or_not', '__return_false' );
	}

	public function register_tags(): void {
		wpcf7_add_form_tag( 'rts_submit', [ $this, 'submit_tag' ] );
	}

	/**
	 * [rts_submit class:extra "Send"] → the design's primary button.
	 *
	 * @param WPCF7_FormTag $tag Form tag.
	 */
	public function submit_tag( $tag ): string {
		$label   = ! empty( $tag->values[0] ) ? (string) $tag->values[0] : __( 'Send', 'reatiss' );
		$classes = trim( 'btn btn--primary ' . $tag->get_class_option( '' ) );

		ob_start();
		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="submit">
			<span class="btn__tile"><?php rts_icon( 'icons/arrow.svg' ); ?></span>
			<?php echo esc_html( $label ); ?>
		</button>
		<?php
		return (string) ob_get_clean();
	}
}

new RTS_Forms();

/**
 * Does the CF7 form exist?
 *
 * @param int $form_id CF7 form post ID.
 */
function rts_has_form( int $form_id ): bool {
	return $form_id > 0 && function_exists( 'wpcf7_contact_form' ) && wpcf7_contact_form( $form_id );
}

/**
 * Renders a CF7 form with the markup's form classes.
 *
 * @param int    $form_id CF7 form post ID.
 * @param string $classes Classes for the <form>, e.g. 'modal__form'.
 */
function rts_form( int $form_id, string $classes = '' ): void {
	if ( ! rts_has_form( $form_id ) ) {
		return;
	}

	echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 output.
		sprintf(
			'[contact-form-7 id="%d" html_class="%s"]',
			$form_id,
			esc_attr( trim( 'rts-form ' . $classes ) )
		)
	);
}
