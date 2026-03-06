<?php

namespace WPFormsMailchimp\Provider;

use WPForms\Tasks\Meta;
use WPFormsMailchimp\Provider\CustomFields\FieldMapper;

/**
 * Class Process handles entries processing using the provider settings and configuration.
 *
 * @since 2.0.0
 */
class Process extends \WPForms\Providers\Provider\Process {

	/**
	 * Async task action: subscribe.
	 *
	 * @since 2.0.0
	 */
	const ACTION_SUBSCRIBE = 'wpforms_mailchimp_process_action_subscribe';

	/**
	 * Async task action: unsubscribe.
	 *
	 * @since 2.0.0
	 */
	const ACTION_UNSUBSCRIBE = 'wpforms_mailchimp_process_action_unsubscribe';

	/**
	 * Async task action: archive.
	 *
	 * @since 2.0.0
	 */
	const ACTION_ARCHIVE = 'wpforms_mailchimp_process_action_archive';

	/**
	 * Async task action: delete.
	 *
	 * @since 2.0.0
	 */
	const ACTION_DELETE = 'wpforms_mailchimp_process_action_delete';

	/**
	 * Async task action: record events.
	 *
	 * @since 2.0.0
	 */
	const ACTION_EVENTS = 'wpforms_mailchimp_process_action_events';

	/**
	 * Get the Core loader class of a provider.
	 *
	 * @since 2.0.0
	 *
	 * @var \WPFormsMailchimp\Provider\Core
	 */
	protected $core;

	/**
	 * Current connection data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $connection = [];

	/**
	 * Main class that communicates with the Mailchimp API.
	 *
	 * @since 2.0.0
	 *
	 * @var Api|null
	 */
	private $api = null;

	/**
	 * Process constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param \WPFormsMailchimp\Provider\Core $core Core instance of the provider class.
	 */
	public function __construct( Core $core ) {

		parent::__construct( $core );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function hooks() {

		add_action( self::ACTION_SUBSCRIBE, [ $this, 'task_async_action_trigger' ] );
		add_action( self::ACTION_UNSUBSCRIBE, [ $this, 'task_async_action_trigger' ] );
		add_action( self::ACTION_ARCHIVE, [ $this, 'task_async_action_trigger' ] );
		add_action( self::ACTION_DELETE, [ $this, 'task_async_action_trigger' ] );
		add_action( self::ACTION_EVENTS, [ $this, 'task_async_action_trigger' ] );
	}

	/**
	 * Receive all wpforms_process_complete params and do the actual processing.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields    Array of form fields.
	 * @param array $entry     Submitted form content.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  ID of a saved entry.
	 */
	public function process( $fields, $entry, $form_data, $entry_id ) {

		// Only run if we have required data.
		if ( empty( $form_data['providers'][ $this->core->slug ] ) ) {
			return;
		}

		$this->fields    = $fields;
		$this->entry     = $entry;
		$this->form_data = $form_data;
		$this->entry_id  = $entry_id;

		$this->process_connections();
	}

	/**
	 * Iteration loop for connections - call action for each connection.
	 *
	 * @since 2.0.0
	 */
	protected function process_connections() {

		foreach ( $this->form_data['providers'][ $this->core->slug ] as $connection_id => $connection_data ) :
			$connection_data = (array) $connection_data;

			// BC: make sure that connection ID is present.
			if ( ! isset( $connection_data['id'] ) || wpforms_is_empty_string( $connection_data['id'] ) ) {
				$connection_data += [ 'id' => $connection_id ];
			}

			// BC: make sure that form ID is present.
			if ( ! isset( $connection_data['form_id'] ) || wpforms_is_empty_string( $connection_data['form_id'] ) ) {
				$connection_data += [ 'form_id' => $this->form_data['id'] ];
			}

			// Check if connection data is valid and ready for use.
			if ( ! $this->is_valid_connection( $connection_data ) ) {
				continue;
			}

			// Save connection data to the property for use later.
			$this->connection = ( new Connection( $connection_data ) )->get_data();

			// Check for conditional logic.
			if ( ! $this->is_conditionals_passed() ) {
				continue;
			}

			// Register AS task for a connection.
			$this->register_action_task();
		endforeach;
	}

	/**
	 * Action Scheduler task registration.
	 *
	 * @since 2.0.0
	 */
	protected function register_action_task() {

		$actions = [
			'subscribe'    => self::ACTION_SUBSCRIBE,
			'unsubscribe'  => self::ACTION_UNSUBSCRIBE,
			'archive'      => self::ACTION_ARCHIVE,
			'delete'       => self::ACTION_DELETE,
			'record_event' => self::ACTION_EVENTS,
		];

		// If it's not supported action, bail.
		if ( ! isset( $actions[ $this->connection['action'] ] ) ) {
			return;
		}

		// For subscribe action, we have additional operations before registering a new AS task.
		if ( $this->connection['action'] === 'subscribe' ) {
			$this->action_subscribe();

			return;
		}

		// Register AS task.
		wpforms()->obj( 'tasks' )
				 ->create( $actions[ $this->connection['action'] ] )->async()
				 ->params( $this->connection, $this->fields, $this->form_data, $this->entry_id )
				 ->register();
	}

	/**
	 * Process the addon async tasks.
	 *
	 * @since 2.0.0
	 *
	 * @param int $meta_id Task meta ID.
	 */
	public function task_async_action_trigger( $meta_id ) {

		$meta = $this->get_task_meta( $meta_id );

		// We expect a certain type and number of params.
		if ( ! is_array( $meta ) || count( $meta ) !== 4 ) {
			return;
		}

		// We expect a certain meta data structure for this task.
		list( $this->connection, $this->fields, $this->form_data, $this->entry_id ) = $meta;

		if ( is_null( $this->get_api() ) ) {
			$this->log_errors( 'Unable to process because API could not be initialized.' );

			return;
		}

		$action = $this->connection['action'];
		$method = "task_async_action_{$action}";

		// Finally, fire the actual action processing.
		if ( method_exists( $this, $method ) ) {
			$this->{$method}();
		}
	}

	/**
	 * Action: Subscribe.
	 *
	 * @since 2.0.0
	 */
	protected function action_subscribe() {

		if ( is_null( $this->get_api() ) ) {
			$this->log_errors( 'Unable to process because API could not be initialized.' );

			return;
		}

		$list_id = $this->connection['list_id'];
		$email   = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];

		// Firstly, we need to check if contact already exists.
		$contact = $this->api->get_list_member( $list_id, $email );

		if ( ! in_array( $this->api->get_http_status_code(), [ 200, 404 ], true ) ) {
			/* translators: %s - last error returned by either the network transport, or by the API. */
			$this->log_errors( sprintf( 'Unable to check if a contact with that email already subscribed: %s', $this->api->getLastError() ) );

			return;
		}

		$contact_status = ! empty( $contact['status'] ) ? $contact['status'] : '';

		// If contact already subscribed and we're not supposed to update, bail.
		if ( $contact_status === 'subscribed' && ! isset( $this->connection['options']['update_profile'] ) ) {

			/**
			 * Filter the error message if a contact is already subscribed.
			 *
			 * @since 2.0.0
			 *
			 * @param string $already_subscribed_error The error message if a contact is already subscribed.
			 * @param array  $fields                   Array of form fields.
			 * @param array  $form_data                Form data and settings.
			 * @param array  $connection               Connection data.
			 */
			$already_subscribed_error = apply_filters( 'wpforms_mailchimp_provider_process_action_subscribe_contact_subscribed_error', esc_html__( 'A contact with that email address is already subscribed.', 'wpforms-mailchimp' ), $this->fields, $this->form_data, $this->connection );

			// Notify users through error message.
			if ( isset( $this->connection['options']['notify_if_subscribed'] ) ) {
				wpforms()->obj( 'process' )->errors[ $this->form_data['id'] ]['header'] = $already_subscribed_error;
			}

			$this->log_errors( $already_subscribed_error );

			return;
		}

		/**
		 * Modify whether a contact that currently has a status of `unsubscribed` on list is resubscribe.
		 *
		 * @param bool  $allow_resubscription If a contact should be resubscribed.
		 * @param array $fields               Array of form fields.
		 * @param array $form_data            Form data and settings.
		 * @param array $connection           Connection data.
		 */
		$allow_resubscription = (bool) apply_filters( 'wpforms_mailchimp_provider_process_action_subscribe_allow_resubscription', true, $this->fields, $this->form_data, $this->connection );

		// If a contact was unsubscribed and resubscription is not allowed, bail.
		if ( $contact_status === 'unsubscribed' && $allow_resubscription !== true ) {

			/**
			 * Filter the error message if contact re-subscription isn't allowed.
			 *
			 * @since 2.0.0
			 *
			 * @param string $resubscription_error The error message if a contact is already subscribed.
			 * @param array  $fields               Array of form fields.
			 * @param array  $form_data            Form data and settings.
			 * @param array  $connection           Connection data.
			 */
			$resubscription_error = apply_filters( 'wpforms_mailchimp_provider_process_action_subscribe_contact_resubscription_error', esc_html__( 'A contact with that email address was unsubscribed and resubscription is not allowed.', 'wpforms-mailchimp' ), $this->fields, $this->form_data, $this->connection );

			// Notify users through error message.
			if ( isset( $this->connection['options']['notify_if_subscribed'] ) ) {
				wpforms()->obj( 'process' )->errors[ $this->form_data['id'] ]['header'] = $resubscription_error;
			}

			$this->log_errors( $resubscription_error );

			return;
		}

		// Register AS task.
		wpforms()->obj( 'tasks' )
				 ->create( self::ACTION_SUBSCRIBE )->async()
				 ->params( $this->connection, $this->fields, $this->form_data, $this->entry_id )
				 ->register();
	}

	/**
	 * AS task: Subscribe action.
	 *
	 * @since 2.0.0
	 */
	protected function task_async_action_subscribe() {

		$list_id        = $this->connection['list_id'];
		$email          = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];
		$contact        = $this->api->get_list_member( $list_id, $email );
		$is_new_contact = empty( $contact['status'] );

		// Setup data.
		$data = $this->setup_subscribe_data( $contact );

		/**
		 * Modify the subscription data before it is executed.
		 *
		 * @param array $data       Subscription data.
		 * @param array $fields     Array of form fields.
		 * @param array $form_data  Form data and settings.
		 * @param int   $entry_id   Entry ID.
		 * @param array $connection Connection data.
		 */
		$data = apply_filters( 'wpforms_mailchimp_provider_process_action_subscribe_request_data', $data, $this->fields, $this->form_data, $this->entry_id, $this->connection );

		// API call.
		$contact = $this->api->add_update_list_member( $list_id, $email, $data, ! $is_new_contact );

		if ( ! empty( $contact ) ) {
			$this->action_subscribe_finally( $contact, $is_new_contact );
		} else {
			$this->log_errors();
		}

		/**
		 * Fire when request was sent both successfully or not.
		 *
		 * @since 2.0.0
		 *
		 * @param array $contact        Mailchimp contact data.
		 * @param bool  $is_new_contact True if it's a new contact.
		 * @param array $data           Subscription data.
		 * @param array $connection     Connection data.
		 * @param Api   $api            Mailchimp API instance.
		 * @param array $args           Additional arguments.
		 */
		do_action(
			'wpforms_mailchimp_provider_process_action_subscribe_after',
			$contact,
			$is_new_contact,
			$data,
			$this->connection,
			$this->api,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry_id'  => $this->entry_id,
			]
		);
	}

	/**
	 * The final things for subscribe action - set tags, GDPR permissions and attach note.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Notes will be added for a new contact and during an update contact process.
	 *
	 * @param array $contact        Contact data.
	 * @param bool  $is_new_contact True if it's a new contact.
	 */
	protected function action_subscribe_finally( $contact, $is_new_contact ) {

		// Setup tags for a new contact OR if updating is allowed.
		if ( $is_new_contact || isset( $this->connection['options']['update_profile'] ) ) {
			$this->setup_tags( $contact );
			$this->setup_note( $contact );
		}

		// Those data are set up only once, for a new contact.
		if ( $is_new_contact ) {
			$this->setup_marketing_permissions( $contact );
		}
	}

	/**
	 * Set up data for subscription.
	 *
	 * @since 2.0.0
	 *
	 * @param array $contact Contact data.
	 *
	 * @return array
	 */
	protected function setup_subscribe_data( $contact ) {

		$data = [];

		// Determine if it's a new contact OR updating already exist contact.
		if ( empty( $contact ) || isset( $this->connection['options']['update_profile'] ) ) {

			// Set merge fields and interests.
			$data['merge_fields'] = $this->setup_merge_fields();
			$data['interests']    = $this->setup_groups( $contact );

			// Filter empty data elements.
			$data = array_filter( $data );

			// VIP status for contact.
			$data['vip'] = isset( $this->connection['options']['vip'] );
		}

		// If a contact has unsubscribed status, then try to re-subscribe
		// and re-send opt-in confirmation email - set a pending status.
		// Otherwise, preserve the same status.
		if ( ! empty( $contact['status'] ) ) {
			$data['status'] = $contact['status'] === 'unsubscribed' ? 'pending' : $contact['status'];

			return $data;
		}

		$saved_entry = wpforms()->obj( 'entry' )->get( (int) $this->entry_id );
		$ip_address  = ! empty( $saved_entry->ip_address ) ? $saved_entry->ip_address : '';

		// For a new contact.
		$data['email_address'] = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];
		$data['status_if_new'] = isset( $this->connection['options']['doubleoptin'] ) ? 'pending' : 'subscribed';
		$data['email_type']    = 'html';
		$data['ip_signup']     = $ip_address;
		$data['ip_opt']        = $ip_address;

		return $data;
	}

	/**
	 * Set up merge fields.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function setup_merge_fields() {

		if ( empty( $this->connection['fields_meta'] ) ) {
			return [];
		}

		$fields = $this->api->get_fields( $this->connection['list_id'] );

		if ( empty( $fields ) ) {
			return [];
		}

		return $this->get_merge_fields_data( $fields );
	}

	/**
	 * Retrieve merge fields data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $mc_fields Mailchimp audience merge fields.
	 *
	 * @return array
	 */
	protected function get_merge_fields_data( $mc_fields ) {

		$mc_fields_tag = array_column( $mc_fields, 'tag' );
		$result        = [];

		foreach ( $this->connection['fields_meta'] as $meta ) {

			// Don't include EMAIL merge fields.
			if (
				! isset( $meta['name'], $meta['field_id'] ) ||
				$meta['name'] === 'EMAIL'
			) {
				continue;
			}

			$meta_field   = $this->get_field_meta( $meta );
			$wpf_field_id = $meta_field['id'];
			$value_source = $meta_field['value_source'];

			// Check if a mapped WPForms field has a value.
			if ( ! isset( $this->fields[ $wpf_field_id ][ $value_source ] ) ) {
				continue;
			}

			// Check if a mapped MC field exists.
			$mc_field_tag = $meta['name'];
			$mc_field_key = array_search( $mc_field_tag, $mc_fields_tag, true );

			if ( $mc_field_key === false ) {
				continue;
			}

			// Prepare fields data.
			$mc_field_props  = $mc_fields[ $mc_field_key ];
			$mc_field_type   = ! empty( $mc_field_props['type'] ) ? $mc_field_props['type'] : 'text';
			$wpf_field_props = $this->form_data['fields'][ $wpf_field_id ];
			$wpf_field       = $this->fields[ $wpf_field_id ];

			// Run magic here!
			$value = ( new FieldMapper() )->init( $mc_field_props, $wpf_field_props, $wpf_field )->get_value( $value_source );

			/**
			 * Dynamic filter allows theme/plugin devs to change a formatted value by type.
			 *
			 * @since 2.0.0
			 *
			 * @param mixed $value
			 * @param array $mc_field_props
			 * @param array $wpf_field_props
			 * @param array $wpf_field
			 */
			$value = apply_filters(
				"wpforms_mailchimp_provider_process_get_merge_fields_data_{$mc_field_type}_value",
				$value,
				$mc_field_props,
				$wpf_field_props,
				$wpf_field
			);

			// We shouldn't pass null values.
			if ( ! is_null( $value ) ) {
				$result[ $mc_field_tag ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Set up segments (groups).
	 *
	 * @since 2.0.0
	 *
	 * @param array $contact Contact data.
	 *
	 * @return array
	 */
	protected function setup_groups( $contact ) {

		$connection_groups = ! empty( $this->connection['groups'] ) ? $this->connection['groups'] : [];
		$contact_groups    = ! empty( $contact['interests'] ) ? $contact['interests'] : [];
		$update_existing   = isset( $this->connection['options']['update_profile'] );

		if (
			( empty( $connection_groups ) && empty( $contact_groups ) ) ||
			( ! empty( $contact_groups ) && ! $update_existing )
		) {
			return [];
		}

		$groups = [];

		foreach ( $connection_groups as $id => $segments ) {
			foreach ( $segments as $segment_id ) {
				$groups[ $segment_id ] = true;
			}
		}

		if ( ! $update_existing ) {
			return $groups;
		}

		if ( ! empty( $groups ) ) {
			$groups = array_merge( $contact_groups, $groups );
		} else {
			$groups = array_fill_keys( array_keys( $contact_groups ), false );
		}

		return $groups;
	}

	/**
	 * Set up tags.
	 *
	 * @since 2.0.0
	 *
	 * @param array $contact Contact data.
	 */
	protected function setup_tags( $contact ) {

		$connection_add_tags    = ! empty( $this->connection['tag_names'] ) ? (array) $this->connection['tag_names'] : [];
		$connection_add_tags    = ! empty( $this->connection['tag_news'] ) ? array_merge( $connection_add_tags, (array) $this->connection['tag_news'] ) : $connection_add_tags;
		$connection_remove_tags = ! empty( $this->connection['tag_removes'] ) ? (array) $this->connection['tag_removes'] : [];

		if ( empty( $connection_add_tags ) && empty( $connection_remove_tags ) ) {
			return;
		}

		$contact_tags = ! empty( $contact['tags'] ) ? array_column( $contact['tags'], 'name' ) : [];
		$add_tags     = array_diff( $connection_add_tags, $contact_tags );
		$remove_tags  = array_intersect( $connection_remove_tags, $contact_tags );
		$tags         = [];

		foreach ( $add_tags as $tag_name ) {
			$tags[] = [
				'name'   => $tag_name,
				'status' => 'active',
			];
		}

		foreach ( $remove_tags as $tag_name ) {
			$tags[] = [
				'name'   => $tag_name,
				'status' => 'inactive',
			];
		}

		if ( empty( $tags ) ) {
			return;
		}

		if ( ! $this->api->update_member_tags( $contact['list_id'], $contact['email_address'], [ 'tags' => $tags ] ) ) {
			$this->log_errors();
		}
	}

	/**
	 * Set up a note.
	 *
	 * @since 2.0.0
	 *
	 * @param array $contact Contact data.
	 */
	protected function setup_note( $contact ) {

		$note = ! empty( $this->connection['note'] ) ? wpforms_process_smart_tags( $this->connection['note'], $this->form_data, $this->fields, $this->entry_id, 'mailchimp-note' ) : '';

		if ( empty( $note ) ) {
			return;
		}

		if ( ! $this->api->add_member_note( $contact['list_id'], $contact['email_address'], [ 'note' => $note ] ) ) {
			$this->log_errors();
		}
	}

	/**
	 * Set up marketing permissions.
	 *
	 * @since 2.0.0
	 *
	 * @param array $contact Contact data.
	 */
	protected function setup_marketing_permissions( $contact ) {

		if (
			empty( $contact['marketing_permissions'] ) ||
			( ! empty( $contact['marketing_permissions'] ) && ! wpforms_has_field_type( 'gdpr-checkbox', $this->form_data ) )
		) {
			return;
		}

		$permissions = [];

		foreach ( $contact['marketing_permissions'] as $permission ) {

			if ( empty( $permission['marketing_permission_id'] ) ) {
				continue;
			}

			$permissions[] = [
				'marketing_permission_id' => $permission['marketing_permission_id'],
				'enabled'                 => true,
			];
		}

		if ( empty( $permissions ) ) {
			return;
		}

		if ( empty( $this->api->update_list_member( $contact['list_id'], $contact['email_address'], [ 'marketing_permissions' => $permissions ] ) ) ) {
			$this->log_errors();
		}
	}

	/**
	 * AS task: Unsubscribe action.
	 *
	 * @since 2.0.0
	 */
	protected function task_async_action_unsubscribe() {

		$list_id = $this->connection['list_id'];
		$email   = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];

		// Firstly, we need to check if contact exists.
		$contact = $this->api->get_list_member( $list_id, $email );

		if ( ! in_array( $this->api->get_http_status_code(), [ 200, 404 ], true ) ) {
			/* translators: %s - last error returned by either the network transport or by the API. */
			$this->log_errors( sprintf( 'Unable to check if a contact with that email exists: %s', $this->api->getLastError() ) );

			return;
		}

		// If contact is not found, bail.
		if ( empty( $contact['status'] ) ) {
			$this->log_errors( 'A contact with that email could not be found.' );

			return;
		}

		// If contact already unsubscribed, bail.
		if ( $contact['status'] === 'unsubscribed' ) {
			$this->log_errors( 'A contact with that email already unsubscribed.' );

			return;
		}

		/**
		 * Modify allowed contact statuses, which can unsubscribe.
		 *
		 * @param array $allow_statuses Allowed contact statuses, which should be resubscribed.
		 * @param array $fields         Array of form fields.
		 * @param array $form_data      Form data and settings.
		 * @param array $connection     Connection data.
		 */
		$allow_statuses = (array) apply_filters( 'wpforms_mailchimp_provider_process_action_unsubscribe_allowed_statuses', [ 'subscribed' ], $this->fields, $this->form_data, $this->connection );

		// If contact is NOT subscribed, bail.
		if ( ! in_array( $contact['status'], $allow_statuses, true ) ) {
			$this->log_errors( 'Unsubscription process is not allowed.' );

			return;
		}

		/**
		 * Modify the unsubscription data before it is executed.
		 *
		 * @param array $data       Unsubscription data.
		 * @param array $fields     Array of form fields.
		 * @param array $form_data  Form data and settings.
		 * @param int   $entry_id   Entry ID.
		 * @param array $connection Connection data.
		 */
		$data = apply_filters( 'wpforms_mailchimp_provider_process_action_unsubscribe_request_data', [ 'status' => 'unsubscribed' ], $this->fields, $this->form_data, $this->entry_id, $this->connection );

		// API call.
		$contact = $this->api->update_list_member( $list_id, $email, $data );

		if ( empty( $contact ) ) {
			$this->log_errors();
		}

		/**
		 * Fire when request was sent successfully or not.
		 *
		 * @since 2.0.0
		 *
		 * @param array $contact    Mailchimp contact data.
		 * @param array $data       Unsubscription data.
		 * @param array $connection Connection data.
		 * @param Api   $api        Mailchimp API instance.
		 * @param array $args       Additional arguments.
		 */
		do_action(
			'wpforms_mailchimp_provider_process_action_unsubscribe_after',
			$contact,
			$data,
			$this->connection,
			$this->api,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry_id'  => $this->entry_id,
			]
		);
	}

	/**
	 * AS task: Archive action.
	 *
	 * @since 2.0.0
	 */
	protected function task_async_action_archive() {

		$list_id = $this->connection['list_id'];
		$email   = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];

		// API call.
		if ( ! $this->api->archive_list_member( $list_id, $email ) ) {
			$this->log_errors();
		}

		/**
		 * Fire when request was sent successfully or not.
		 *
		 * @since 2.0.0
		 *
		 * @param array $connection Connection data.
		 * @param Api   $api        Mailchimp API instance.
		 * @param array $args       Additional arguments.
		 */
		do_action(
			'wpforms_mailchimp_provider_process_action_archive_after',
			$this->connection,
			$this->api,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry_id'  => $this->entry_id,
			]
		);
	}

	/**
	 * AS task: Permanently Delete action.
	 *
	 * @since 2.0.0
	 */
	protected function task_async_action_delete() {

		$list_id = $this->connection['list_id'];
		$email   = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];

		// API call.
		if ( ! $this->api->delete_list_member( $list_id, $email ) ) {
			$this->log_errors();
		}

		/**
		 * Fire when request was sent successfully or not.
		 *
		 * @since 2.0.0
		 *
		 * @param array $connection Connection data.
		 * @param Api   $api        Mailchimp API instance.
		 * @param array $args       Additional arguments.
		 */
		do_action(
			'wpforms_mailchimp_provider_process_action_delete_after',
			$this->connection,
			$this->api,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry_id'  => $this->entry_id,
			]
		);
	}

	/**
	 * AS task: Record Event action.
	 *
	 * @since 2.0.0
	 */
	protected function task_async_action_record_event() {

		$list_id = $this->connection['list_id'];
		$email   = $this->fields[ $this->connection['fields']['EMAIL'] ]['value'];

		$events       = $this->api->get_member_events( $list_id, $email, [ 'fields' => 'events.name' ] );
		$events       = ! empty( $events ) ? array_unique( array_column( $events, 'name' ) ) : [];
		$is_new_event = ! in_array( $this->connection['event_name'], $events, true );

		/**
		 * Filter the value, that allows event recording.
		 * Theme/plugin authors can add a conditional check when an event should be recorded.
		 *
		 * @since 2.0.0
		 *
		 * @param bool   $allow_event
		 * @param bool   $is_new_event
		 * @param string $email
		 * @param array  $connection
		 * @param Api    $api
		 */
		$allow_event = (bool) apply_filters( 'wpforms_mailchimp_provider_process_action_record_event_allow_event', true, $is_new_event, $email, $this->connection, $this->api );

		// Bail, if it's not true.
		if ( $allow_event !== true ) {
			return;
		}

		// Setup data.
		$data = [
			'name'       => $this->connection['event_name'],
			'properties' => [
				'form_title'      => ! empty( $this->form_data['settings']['form_title'] ) ? esc_html( $this->form_data['settings']['form_title'] ) : '', // MC validation: value should be of type string.
				'form_id'         => (string) $this->form_data['id'],
				'connection_name' => esc_html( $this->connection['connection_name'] ),
			],
		];

		if ( ! empty( $this->entry_id ) ) {
			$data['properties']['entry_id'] = (string) $this->entry_id;
		}

		/**
		 * Modify the event data before it is executed.
		 *
		 * @param array $data       Event data.
		 * @param array $fields     Array of form fields.
		 * @param array $form_data  Form data and settings.
		 * @param int   $entry_id   Entry ID.
		 * @param array $connection Connection data.
		 */
		$data = apply_filters( 'wpforms_mailchimp_provider_process_action_record_event_request_data', $data, $this->fields, $this->form_data, $this->entry_id, $this->connection );

		// API call.
		if ( ! $this->api->add_member_event( $list_id, $email, $data ) ) {
			$this->log_errors();
		}

		/**
		 * Fire when request was sent successfully or not.
		 *
		 * @since 2.0.0
		 *
		 * @param array $connection Connection data.
		 * @param Api   $api        Mailchimp API instance.
		 * @param array $args       Additional arguments.
		 */
		do_action(
			'wpforms_mailchimp_provider_process_action_record_event_after',
			$this->connection,
			$this->api,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry_id'  => $this->entry_id,
			]
		);
	}

	/**
	 * Get the API client based on connection and provider options.
	 *
	 * @since 2.0.0
	 *
	 * @return Api|null Null on error.
	 */
	protected function get_api() {

		$options = $this->core->get_provider_options();

		// Validate existence of required data.
		if ( empty( $options[ $this->connection['account_id'] ]['api'] ) ) {
			return null;
		}

		$apikey = trim( $options[ $this->connection['account_id'] ]['api'] );

		// Prepare an API client.
		try {
			$this->api = new Api( $apikey );

			return $this->api;
		} catch ( \Exception $e ) {
			$this->log_errors( $e->getMessage() );

			return null;
		}
	}

	/**
	 * Check passed data for the provided connection.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Connection data.
	 *
	 * @return bool False if connection data is invalid, true otherwise.
	 */
	protected function is_valid_connection( $data ) {

		try {
			$connection = new Connection( $data );
		} catch ( \InvalidArgumentException $e ) {
			$this->log_errors( sprintf( 'Unable to process because something is wrong with the connection data. %s', $e->getMessage() ), $data );

			return false;
		}

		if ( ! $connection->is_valid() ) {
			$this->log_errors( 'Unable to process because connection data is invalid.', $data );

			return false;
		}

		// Make sure that we have an email value.
		$connection = $connection->get_data();

		if (
			empty( $connection['fields'] ) ||
			! isset( $connection['fields']['EMAIL'] ) ||
			! isset( $this->fields[ $connection['fields']['EMAIL'] ] ) ||
			empty( $this->fields[ $connection['fields']['EMAIL'] ]['value'] )
		) {
			$this->log_errors( 'Unable to process because EMAIL was not provided.', $connection );

			return false;
		}

		return true;
	}

	/**
	 * Process Conditional Logic for the provided connection.
	 *
	 * @since 2.0.0
	 *
	 * @return bool False if CL rules stopped the connection execution, true otherwise.
	 */
	protected function is_conditionals_passed() {

		$pass = $this->process_conditionals( $this->fields, $this->form_data, $this->connection );

		// Check the conditional logic.
		if ( ! $pass ) {
			wpforms_log(
				sprintf( 'The Mailchimp connection %s was not processed due to conditional logic.', $this->connection['name'] ?? '' ),
				$this->fields,
				[
					'type'    => [ 'provider', 'conditional_logic' ],
					'parent'  => $this->entry_id,
					'form_id' => $this->form_data['id'],
				]
			);
		}

		return $pass;
	}

	/**
	 * Prepare and retrieve a field meta.
	 *
	 * @since 2.0.0
	 *
	 * @param array $meta Raw field meta.
	 *
	 * @return array
	 */
	protected function get_field_meta( $meta ) {

		$meta_field   = explode( '.', $meta['field_id'] );
		$field_id     = $meta_field[0];
		$value_source = ! empty( $meta_field[1] ) ? $meta_field[1] : 'value';

		// BC for Name field.
		$value_source = $value_source === 'full' ? 'value' : $value_source;

		// Determine a value source for Payment fields.
		$payment_fields   = (array) wpforms_payment_fields();
		$payment_fields[] = 'payment-total';

		if (
			! empty( $this->fields[ $field_id ]['type'] ) &&
			in_array( $this->fields[ $field_id ]['type'], $payment_fields, true )
		) {
			$value_source = apply_filters( 'wpforms_mailchimp_provider_process_get_field_meta_payment_value_source', $value_source );
		}

		return [
			'id'           => $field_id,
			'value_source' => $value_source,
		];
	}

	/**
	 * Get task meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param int $meta_id Task meta ID.
	 *
	 * @return array|null Null when no data available.
	 */
	protected function get_task_meta( $meta_id ) {

		$task_meta = new Meta();
		$meta      = $task_meta->get( (int) $meta_id );

		// We should actually receive something.
		if ( empty( $meta ) || empty( $meta->data ) ) {
			return null;
		}

		return $meta->data;
	}

	/**
	 * Log an API-related error with all the data.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $response   API response data, may be array or string.
	 * @param array $connection Specific connection data that errored.
	 */
	protected function log_errors( $response = [], $connection = [] ) {

		if ( empty( $response ) && ! empty( $this->api ) ) {
			$response = $this->api->getLastResponse();

			if ( isset( $response['headers'] ) ) {
				unset( $response['headers'] );
			}
		}

		wpforms_log(
			"Submission to Mailchimp failed.(#{$this->entry_id})",
			[
				'response'   => $response,
				'connection' => ! empty( $connection ) ? $connection : $this->connection,
			],
			[
				'type'    => [ 'provider', 'error' ],
				'parent'  => $this->entry_id,
				'form_id' => $this->form_data['id'],
			]
		);
	}
}
