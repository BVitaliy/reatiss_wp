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
		add_filter( 'wpcf7_form_elements', [ $this, 'select_placeholder' ] );
		add_filter( 'wpcf7_mail_components', [ $this, 'mail_layout' ], 10, 3 );
	}

	/**
	 * Every form mail goes out in one layout (template-parts/mail/layout.php).
	 * The form's Mail tab keeps a plain body — “Label: [field]”, one per line;
	 * labels turn bold, links clickable, the subject is the heading. A body
	 * written in HTML is sent as is; “Use HTML content type” alone (a plain
	 * body with the box left ticked) still gets the layout.
	 *
	 * @param array             $components Mail parts: subject, body, additional_headers…
	 * @param WPCF7_ContactForm $form       Form.
	 * @param WPCF7_Mail        $mail       Mail being sent (mail / mail_2).
	 */
	public function mail_layout( $components, $form, $mail ): array {
		$components = (array) $components;
		$template   = $form && $mail ? (array) $form->prop( $mail->name() ) : [];
		$raw        = (string) ( $template['body'] ?? '' );

		if ( '' === trim( $raw ) || preg_match( '/<[a-z!][^>]*>/i', $raw ) ) {
			return $components;
		}

		// With the HTML box ticked CF7 has escaped and wrapped the body already —
		// take the plain text again.
		$body = ! empty( $template['use_html'] )
			? (string) wpcf7_mail_replace_tags( $raw, [ 'exclude_blank' => ! empty( $template['exclude_blank'] ) ] )
			: (string) ( $components['body'] ?? '' );
		$body = esc_html( trim( $body ) );
		$body = (string) preg_replace( '/^([^:\n]{1,40}):/m', '<strong>$1:</strong>', $body );
		$body = nl2br( str_replace( '<a ', '<a style="color: #001d77;" ', make_clickable( $body ) ), false );

		ob_start();
		get_template_part(
			'template-parts/mail/layout',
			null,
			[
				'title'   => (string) ( $components['subject'] ?? '' ),
				'content' => $body,
			]
		);

		$components['body']               = (string) ob_get_clean();
		$components['additional_headers'] = trim( ( $components['additional_headers'] ?? '' ) . "\nContent-Type: text/html; charset=UTF-8" );

		return $components;
	}

	/**
	 * `[select … first_as_label "Pick…"]` prints the label as a plain option;
	 * the markup's select (and app-select.js) expects the placeholder as
	 * `<option value="" selected disabled hidden>` — grey text, not choosable.
	 *
	 * @param string $html Form HTML.
	 */
	public function select_placeholder( $html ): string {
		return (string) preg_replace( '#(<select\b[^>]*>\s*)<option value="">#', '$1<option value="" selected disabled hidden>', (string) $html );
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
