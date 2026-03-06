<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-lists">
	<h4><?php esc_html_e( 'Select Audience', 'wpforms-mailchimp' ); ?><span class="required">*</span></h4>

	<select class="js-wpforms-builder-mailchimp-provider-connection-lists wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][list_id]"<# if ( _.isEmpty( data.lists ) ) { #> disabled<# } #>>

		<# if ( _.isEmpty( data.lists ) ) { #><option value="" selected disabled><?php esc_html_e( '--- No Audiences ---', 'wpforms-mailchimp' ); ?></option><# } else { #><option value=""><?php esc_html_e( '--- Select Audience ---', 'wpforms-mailchimp' ); ?></option><# } #>

		<# _.each( data.lists, function( list ) { #>
		<option value="{{ list.id }}" data-marketing_permissions="{{ list.marketing_permissions }}" <# if ( data.connection.list_id === list.id ) { #> selected<# } #>>
			{{ list.name }}
		</option>
		<# } ); #>
	</select>

	<# if ( _.isEmpty( data.lists ) ) { #>
		<p class="description error-message">
			<?php esc_html_e( 'You have no audience yet. Consider creating at least one.', 'wpforms-mailchimp' ); ?>
		</p>
	<# } #>
</div>

<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-actions">
	<h4><?php esc_html_e( 'Action To Perform', 'wpforms-mailchimp' ); ?><span class="required">*</span></h4>

	<select class="js-wpforms-builder-mailchimp-provider-connection-action wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][action]"<# if ( _.isEmpty( data.lists ) ) { #> disabled<# } #>>
		<option value=""><?php esc_html_e( '--- Select Action ---', 'wpforms-mailchimp' ); ?></option>
		<option value="subscribe"<# if ( 'subscribe' === data.connection.action ) { #> selected<# } #>><?php esc_html_e( 'Subscribe', 'wpforms-mailchimp' ); ?></option>
		<option value="unsubscribe"<# if ( 'unsubscribe' === data.connection.action ) { #> selected<# } #>><?php esc_html_e( 'Unsubscribe', 'wpforms-mailchimp' ); ?></option>
		<option value="archive"<# if ( 'archive' === data.connection.action ) { #> selected<# } #>><?php esc_html_e( 'Archive', 'wpforms-mailchimp' ); ?></option>
		<option value="delete"<# if ( 'delete' === data.connection.action ) { #> selected<# } #>><?php esc_html_e( 'Permanently Delete', 'wpforms-mailchimp' ); ?></option>
		<option value="record_event"<# if ( 'record_event' === data.connection.action ) { #> selected<# } #>><?php esc_html_e( 'Record Event', 'wpforms-mailchimp' ); ?></option>
	</select>
</div>

<!-- Here is where sub-templates will put its compiled HTML. -->
<div class="wpforms-builder-mailchimp-provider-actions-data"></div>

{{{ data.conditional }}}
