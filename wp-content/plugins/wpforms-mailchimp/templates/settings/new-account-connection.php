<?php
/**
 * New account template.
 *
 * @var string $provider_name Provider name.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<?php
	printf(
		wp_kses( /* translators: %1$s - Documentation URL, %2$s - current provider name. */
			__(
				'If you need help connecting WPForms to %2$s, <a href="%1$s" rel="noopener noreferrer" target="_blank">read our documentation</a>.',
				'wpforms-mailchimp'
			),
			[
				'a' => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		),
		esc_url( wpforms_utm_link( 'https://wpforms.com/docs/install-use-mailchimp-addon-wpforms/#mailchimp-api', 'Settings - Integration', 'Mailchimp Documentation' ) ),
		esc_html( $provider_name )
	);
	?>
</p>
<input type="text" name="apikey" class="wpforms-required"
	placeholder="<?php printf( /* translators: %s - current provider name. */ esc_attr__( '%s API Key *', 'wpforms-mailchimp' ), esc_html( $provider_name ) ); ?>">
<input type="text" name="label"
	placeholder="<?php printf( /* translators: %s - current provider name. */ esc_attr__( '%s Account Nickname', 'wpforms-mailchimp' ), esc_html( $provider_name ) ); ?>">
<p class="error hidden">
	<?php esc_html_e( 'Something went wrong while performing an AJAX request.', 'wpforms-mailchimp' ); ?>
</p>
