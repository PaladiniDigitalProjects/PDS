<?php
/**
 * Delete action template.
 *
 * @var string $field_email HTML markup for select that has a list of email fields.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-delete">
	<h4><?php esc_html_e( 'Permanently Delete', 'wpforms-mailchimp' ); ?></h4>

	<div class="wpforms-builder-provider-connection-setting">
		<p class="description before">
			<?php echo wp_kses( __( 'Contact and their personal data will be permanently removed from an audience. <strong>You cannot add them back later</strong>.', 'wpforms-mailchimp' ), [ 'strong' => [] ] ); ?>
		</p>
		<?php echo $field_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
