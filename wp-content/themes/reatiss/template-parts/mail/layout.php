<?php
/**
 * Email layout for every Contact Form 7 mail (RTS_Forms::mail_layout()).
 * Inline styles and a table — what mail apps (Gmail, Outlook) understand.
 *
 * @package reatiss
 *
 * @var array $args {
 *     @type string $title   Heading (the mail subject).
 *     @type string $content Body HTML, already escaped.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rts_logo = rts_image_url( rts_option( 'mail_logo' ) );
$rts_logo = '' !== $rts_logo ? $rts_logo : rts_asset( 'img/logo-email.png' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo esc_html( $args['title'] ); ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa;">
	<tr>
		<td align="center" style="padding: 40px 16px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px; background-color: #ffffff; border: 1px solid #eeeff0; border-top: 4px solid #001d77; border-radius: 6px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #021016;">
				<tr>
					<td style="padding: 32px 32px 0; text-align: center;">
						<a href="<?php echo esc_url( RTS_HOME_URL ); ?>"><img src="<?php echo esc_url( $rts_logo ); ?>" width="188" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="display: inline-block; border: 0; outline: none; max-width: 188px; height: auto;" /></a>
					</td>
				</tr>
				<tr>
					<td style="padding: 0 32px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 24px; border-top: 1px solid #eeeff0; border-bottom: 1px solid #eeeff0;">
							<tr>
								<td style="padding: 28px 0;">
									<?php if ( '' !== $args['title'] ) { ?>
										<div style="margin-bottom: 16px; font-size: 18px; line-height: 1.3; font-weight: bold;"><?php echo esc_html( $args['title'] ); ?></div>
									<?php } ?>
									<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by RTS_Forms::mail_layout(). ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding: 20px 32px 28px; font-size: 12px; color: #636363; text-align: center;">
						<a href="<?php echo esc_url( RTS_HOME_URL ); ?>" style="color: #636363;"><?php echo esc_html( wp_parse_url( RTS_HOME_URL, PHP_URL_HOST ) ); ?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
