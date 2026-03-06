<?php
/**
 * Archive action template.
 *
 * @var string $field_email HTML markup for select that has a list of email fields.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-archive">
	<h4><?php esc_html_e( 'Archive', 'wpforms-mailchimp' ); ?></h4>

	<div class="wpforms-builder-provider-connection-setting">
		<p class="description before">
			<?php esc_html_e( 'Contact will be removed from an audience and will not count towards Mailchimp account billing. You can add them back to an audience later.', 'wpforms-mailchimp' ); ?>
		</p>
		<?php echo $field_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
