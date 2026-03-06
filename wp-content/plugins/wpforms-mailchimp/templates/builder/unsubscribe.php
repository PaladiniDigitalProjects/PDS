<?php
/**
 * Unsubscribe action template.
 *
 * @var string $field_email HTML markup for select that has a list of email fields.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-unsubscribe">
	<h4><?php esc_html_e( 'Unsubscribe', 'wpforms-mailchimp' ); ?></h4>

	<div class="wpforms-builder-provider-connection-setting">
		<p class="description before">
			<?php esc_html_e( 'Unsubscribed contacts count towards Mailchimp account bill. To remove them from billing, please use Archive action instead.', 'wpforms-mailchimp' ); ?>
		</p>
		<?php echo $field_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
