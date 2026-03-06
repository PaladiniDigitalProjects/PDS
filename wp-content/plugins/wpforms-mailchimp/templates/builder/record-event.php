<?php
/**
 * Record event action template.
 *
 * @var string $field_email HTML markup for select that has a list of email fields.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-record-event">
	<h4><?php esc_html_e( 'Record Events', 'wpforms-mailchimp' ); ?></h4>

	<div class="wpforms-builder-provider-connection-setting">
		<?php echo $field_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<div class="wpforms-builder-provider-connection-setting">
		<label><?php esc_html_e( 'Event Name', 'wpforms-mailchimp' ); ?><span class="required">*</span></label>
		<input type="text" value="<# if ( _.has( data.connection, 'event_name' ) && ! _.isEmpty( data.connection.event_name ) ) { #>{{ data.connection.event_name }}<# } #>" class="js-wpforms-builder-mailchimp-provider-event-name wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][event_name]" placeholder="<?php esc_attr_e( 'e.g., wpforms', 'wpforms-mailchimp' ); ?>">
		<p class="description">
			<?php esc_html_e( 'Must be 2-30 characters long and only contain letters, numbers, underscores, and dashes.', 'wpforms-mailchimp' ); ?>
		</p>
	</div>
</div>
