<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection wpforms-builder-mailchimp-provider-connection" data-connection_id="{{ data.connection.id }}">
	<input type="hidden" name="providers[{{ data.provider }}][{{ data.connection.id }}][id]" value="{{ data.connection.id }}">
	<input type="hidden" name="providers[{{ data.provider }}][{{ data.connection.id }}][form_id]" value="{{ data.form_id }}">

	<div class="wpforms-builder-provider-connection-title">
		{{ data.connection.connection_name }}

		<button class="wpforms-builder-provider-connection-delete js-wpforms-builder-provider-connection-delete" type="button">
			<i class="fa fa-trash-o"></i>
		</button>

		<input type="hidden" class="wpforms-builder-provider-connection-name" name="providers[{{ data.provider }}][{{ data.connection.id }}][connection_name]" value="{{ data.connection.connection_name }}">
	</div>

	<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-accounts">
		<h4><?php esc_html_e( 'Select Account', 'wpforms-mailchimp' ); ?><span class="required">*</span></h4>

		<select class="js-wpforms-builder-mailchimp-provider-connection-account wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][account_id]"<# if ( _.isEmpty( data.accounts ) ) { #> disabled<# } #>>
			<option value=""><?php esc_html_e( '--- Select Account ---', 'wpforms-mailchimp' ); ?></option>
			<# _.each( data.accounts, function( label, option_id ) { #>
				<option value="{{ option_id }}"<# if ( _.isMatch( data.connection, { account_id: option_id } ) ) { #> selected<# } #>>
					{{ label }}
				</option>
			<# } ); #>
		</select>
	</div>

	<div class="wpforms-builder-mailchimp-provider-connection-data" style="min-height: 25px;"></div>
</div>
