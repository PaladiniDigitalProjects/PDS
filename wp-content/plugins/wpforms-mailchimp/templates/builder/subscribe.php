<?php
/**
 * Subscribe action template.
 *
 * @var string $field_email HTML markup for select that has a list of email fields.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection-block wpforms-builder-mailchimp-provider-subscribe">
	<h4><?php esc_html_e( 'Subscribe', 'wpforms-mailchimp' ); ?></h4>

	<div class="wpforms-builder-provider-connection-setting">
		<?php echo $field_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<# if ( ! _.isEmpty( data.groups ) ) { #>
	<div class="wpforms-builder-provider-connection-settings-group wpforms-builder-mailchimp-provider-subscribe-groups">
		<h4><?php esc_html_e( 'Select Groups', 'wpforms-mailchimp' ); ?></h4>
		<p class="description"><?php esc_html_e( 'We also noticed that you have some groups (segments) in your list. You can select specific list groups below if needed. This is optional.', 'wpforms-mailchimp' ); ?></p>

		<# _.each( data.groups, function( group, group_id ) { #>
		<div class="wpforms-builder-provider-connection-setting">
			<label for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-group-{{ group_id }}">{{ group['name'] }}</label>
			<select class="choicesjs-select" id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-group-{{ group_id }}" name="providers[{{ data.provider }}][{{ data.connection.id }}][groups_data][{{ group_id }}]<# if ( group['type'] === 'checkboxes' ) { #>[]<# } #>" <# if ( group['type'] === 'checkboxes' ) { #>multiple<# } #>>
				<# if ( _.isEmpty( group['interests'] ) ) { #><option value="" disabled><?php esc_html_e( '--- No Groups ---', 'wpforms-mailchimp' ); ?></option><# } else { #><option value=""><?php esc_html_e( 'Select group(s)', 'wpforms-mailchimp' ); ?></option><# } #>

				<# _.each( group['interests'], function( item ) { #>
				<option value="{{ item['id'] }}"<# if ( ! _.isEmpty( data.connection.groups ) && _.has( data.connection.groups, group_id ) && _.find( data.connection.groups[ group_id ], function( interest_id ) { return interest_id == item['id']; } ) ) { #> selected<# } #>>
					{{ item['name'] }}
				</option>
				<# } ); #>
			</select>
		</div>
		<# } ); #>
	</div>
	<# } #>

	<div class="wpforms-builder-provider-connection-settings-group wpforms-builder-mailchimp-provider-subscribe-tags">
		<div class="wpforms-builder-provider-connection-setting wpforms-builder-mailchimp-provider-subscribe-tags-add">
			<h4><?php esc_html_e( 'Select Tags', 'wpforms-mailchimp' ); ?></h4>

			<label for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-tags-add">
				<?php esc_html_e( 'Tags To Add', 'wpforms-mailchimp' ); ?>
			</label>
			<select class="js-wpforms-builder-mailchimp-provider-item-select choicesjs-select" id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-tags-add" name="providers[{{ data.provider }}][{{ data.connection.id }}][tags][add][]" multiple>
				<# if ( _.isEmpty( data.tags ) ) { #><option value="" disabled><?php esc_html_e( '--- No Tags ---', 'wpforms-mailchimp' ); ?></option><# } else { #><option value="" disabled><?php esc_html_e( 'Select tag(s)', 'wpforms-mailchimp' ); ?></option><# } #>

				<# _.each( data.tags, function( value, key ) { #>
				<option value="{{ value }}"<# if ( ( ! _.isEmpty( data.connection.tag_names ) && _.find( data.connection.tag_names, function( tag ) { return tag == value; } ) ) || ( ! _.isEmpty( data.connection.tag_news ) && _.find( data.connection.tag_news, function( tag ) { return tag == value; } ) ) ) { #> selected<# } #>>
					{{ value }}
				</option>
				<# } ); #>
			</select>
			<p class="description"><?php esc_html_e( 'Select one or more of the existing tags.', 'wpforms-mailchimp' ); ?></p>
		</div>

		<div class="wpforms-builder-provider-connection-setting wpforms-builder-mailchimp-provider-item-input">
			<label for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-tags-new">
				<?php esc_html_e( 'New Tags to Add', 'wpforms-mailchimp' ); ?>
			</label>
			<input type="text" value="<# if ( ! _.isEmpty( data.connection.tag_news ) ) { if ( ! _.isEmpty( data.tags ) ) { #>{{ _.difference( data.connection.tag_news, data.tags ).join( ',' ) }}<# } else { #>{{ data.connection.tag_news.join( ',' ) }}<# } #><# } #>" class="js-wpforms-builder-mailchimp-provider-tags-new" id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-tags-new" name="providers[{{ data.provider }}][{{ data.connection.id }}][tags][new]" placeholder="<?php esc_attr_e( 'e.g., wpforms', 'wpforms-mailchimp' ); ?>">
			<p class="description">
				<?php esc_html_e( 'Comma-separated list of tags is accepted.', 'wpforms-mailchimp' ); ?>
			</p>
		</div>

		<div class="wpforms-builder-provider-connection-setting wpforms-builder-mailchimp-provider-subscribe-tags-delete">
			<label for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-tags-delete">
				<?php esc_html_e( 'Tags To Remove', 'wpforms-mailchimp' ); ?>
			</label>
			<select class="js-wpforms-builder-mailchimp-provider-item-select choicesjs-select" id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-subscribe-tags-delete" name="providers[{{ data.provider }}][{{ data.connection.id }}][tags][remove][]" multiple>
				<# if ( _.isEmpty( data.tags ) ) { #><option value="" disabled><?php esc_html_e( '--- No Tags ---', 'wpforms-mailchimp' ); ?></option><# } else { #><option value="" disabled><?php esc_html_e( 'Select tag(s)', 'wpforms-mailchimp' ); ?></option><# } #>

				<# _.each( data.tags, function( value, key ) { #>
				<option value="{{ value }}"<# if ( ( ! _.isEmpty( data.connection.tag_removes ) && _.find( data.connection.tag_removes, function( tag ) { return tag == value; } ) ) ) { #> selected<# } #>>
					{{ value }}
				</option>
				<# } ); #>
			</select>
			<p class="description"><?php esc_html_e( 'Select one or more of the existing tags.', 'wpforms-mailchimp' ); ?></p>
		</div>
	</div>

	<div class="wpforms-builder-provider-connection-setting wpforms-panel-field wpforms-panel-field-textarea">
		<label for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-note">
			<?php esc_html_e( 'Note', 'wpforms-mailchimp' ); ?>
		</label>
		<textarea
				id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-note"
				class="wpforms-smart-tags-enabled"
				name="providers[{{ data.provider }}][{{ data.connection.id }}][note]"
				placeholder="<?php esc_attr_e( 'Enter a note', 'wpforms-mailchimp' ); ?>"
				rows="4"
				data-type="all" data-fields><# if ( _.has( data.connection, 'note' ) && ! _.isEmpty( data.connection.note ) ) { #>{{ data.connection.note }}<# } #></textarea>
		<p class="description">
			<?php
			printf(
				wp_kses( /* translators: %s - WPForms.com URL to the list of Smart Tags. */
					__( 'You can use <a href="%s" target="_blank" rel="noopener noreferrer">Smart Tags</a> in notes.', 'wpforms-mailchimp' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				'https://wpforms.com/docs/how-to-use-smart-tags-in-wpforms/'
			);
			?>
		</p>
	</div>

	<div class="wpforms-builder-provider-connection-setting">
		<div class="wpforms-panel-field">
			<span class="wpforms-toggle-control">
				<input
					type="checkbox"
					value="1"
					class="js-wpforms-builder-mailchimp-provider-enable-optin"
					id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-enable-optin"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][options][doubleoptin]"
					<# if ( _.has( data.connection.options, 'doubleoptin' ) && data.connection.options.doubleoptin ) { #> checked<# } #>
				>
				<label class="wpforms-toggle-control-icon" for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-enable-optin"></label>
				<label
					for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-enable-optin"
					class="wpforms-toggle-control-label"
				>
					<?php esc_html_e( 'Enable double opt-in', 'wpforms-mailchimp' ); ?>
				</label>
			</span>
			<p class="description">
				<?php esc_html_e( 'Send contacts an opt-in confirmation email when they subscribe to audience.', 'wpforms-mailchimp' ); ?>
			</p>
		</div>
	</div>

	<div class="wpforms-builder-provider-connection-setting">
		<div class="wpforms-panel-field">
			<span class="wpforms-toggle-control">
				<input
					type="checkbox"
					value="1"
					class="js-wpforms-builder-mailchimp-provider-mark-vip"
					id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-mark-vip"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][options][vip]"
					<# if ( _.has( data.connection.options, 'vip' ) && data.connection.options.vip ) { #> checked<# } #>
				>
				<label class="wpforms-toggle-control-icon" for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-mark-vip"></label>
				<label
					for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-mark-vip"
					class="wpforms-toggle-control-label"
				>
					<?php esc_html_e( 'Mark subscriber as VIP', 'wpforms-mailchimp' ); ?>
				</label>
			</span>
		</div>
	</div>

	<div class="wpforms-builder-provider-connection-setting">
		<div class="wpforms-panel-field">
			<span class="wpforms-toggle-control">
				<input
					type="checkbox"
					value="1"
					class="js-wpforms-builder-mailchimp-provider-update-profile"
					id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-update-profile"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][options][update_profile]"
					<# if ( _.has( data.connection.options, 'update_profile' ) && data.connection.options.update_profile ) { #> checked<# } #>
				>
				<label class="wpforms-toggle-control-icon" for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-update-profile"></label>
				<label
					for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-update-profile"
					class="wpforms-toggle-control-label"
				>
					<?php esc_html_e( 'Update the profile if this contact is already in an audience', 'wpforms-mailchimp' ); ?>
				</label>
			</span>
		</div>
	</div>

	<div class="wpforms-builder-provider-connection-setting<# if ( _.has( data.connection.options, 'update_profile' ) && data.connection.options.update_profile ) { #> wpforms-hidden<# } #>">
		<div class="wpforms-panel-field">
			<span class="wpforms-toggle-control">
				<input
					type="checkbox"
					value="1"
					class="js-wpforms-builder-mailchimp-provider-notify-user"
					id="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-notify-user"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][options][notify_if_subscribed]"
					<# if ( _.has( data.connection.options, 'notify_if_subscribed' ) && data.connection.options.notify_if_subscribed ) { #> checked<# } #>
				>
				<label class="wpforms-toggle-control-icon" for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-notify-user"></label>
				<label
					for="wpforms-builder-mailchimp-provider-{{ data.connection.id }}-notify-user"
					class="wpforms-toggle-control-label"
				>
					<?php esc_html_e( 'Notify users that they are already subscribed', 'wpforms-mailchimp' ); ?>
				</label>
				<i class="fa fa-question-circle-o wpforms-help-tooltip" title="<?php esc_attr_e( 'The user will not be notified when "Update the profile" option is enabled.', 'wpforms-mailchimp' ); ?>"></i>
			</span>
		</div>
	</div>

</div>
