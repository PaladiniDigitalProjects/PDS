<?php

namespace WPFormsMailchimp\Provider\Settings;

use InvalidArgumentException;
use WPFormsMailchimp\Provider\Api;
use WPFormsMailchimp\Provider\Connection;
use WPForms\Providers\Provider\Settings\FormBuilder as FormBuilderAbstract;
use WPFormsMailchimp\Provider\Helpers;

/**
 * Class FormBuilder handles functionality inside the Form Builder.
 *
 * @since 2.0.0
 */
class FormBuilder extends FormBuilderAbstract {

	/**
	 * Get the Core loader class of a provider.
	 *
	 * @since 2.0.0
	 *
	 * @var \WPFormsMailchimp\Provider\Core
	 */
	protected $core;

	/**
	 * Connections data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $connections = [];

	/**
	 * Register all hooks (actions and filters).
	 *
	 * @since 2.0.0
	 */
	protected function init_hooks() {

		parent::init_hooks();

		// AJAX-event names.
		static $ajax_events = [
			'ajax_account_save',
			'ajax_account_template_get',
			'ajax_connections_get',
			'ajax_accounts_get',
			'ajax_objects_get',
		];

		// Register callbacks for AJAX events.
		array_walk(
			$ajax_events,
			static function( $ajax_event, $key, $instance ) {

				add_filter(
					"wpforms_providers_settings_builder_{$ajax_event}_{$instance->core->slug}",
					[ $instance, $ajax_event ]
				);
			},
			$this
		);

		// Register callbacks for hooks.
		add_filter( 'wpforms_save_form_args', [ $this, 'save_form' ], 11, 3 );
	}

	/**
	 * Pre-process provider data before saving it in form_data when editing a form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form Form array which is usable with `wp_update_post()`.
	 * @param array $data Data retrieved from $_POST and processed.
	 * @param array $args Empty by default, may have custom data not intended to be saved, but used for processing.
	 *
	 * @return array
	 */
	public function save_form( $form, $data, $args ) {

		// Get a filtered (or modified by another addon) form content.
		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		// Provider exists.
		if ( ! empty( $form_data['providers'][ $this->core->slug ] ) ) {
			$modified_post_content = $this->modify_form_data( $form_data );

			if ( ! empty( $modified_post_content ) ) {
				$form['post_content'] = wpforms_encode( $modified_post_content );

				return $form;
			}
		}

		/*
		 * This part works when modification is locked or current filter was called on NOT Providers panel.
		 * Then we need to restore provider connections from the previous form content.
		 */

		// Get a "previous" form content (current content are still not saved).
		$prev_form = ! empty( $data['id'] ) ? wpforms()->obj( 'form' )->get( $data['id'], [ 'content_only' => true ] ) : [];

		if ( ! empty( $prev_form['providers'][ $this->core->slug ] ) ) {
			$provider = $prev_form['providers'][ $this->core->slug ];

			if ( ! isset( $form_data['providers'] ) ) {
				$form_data = array_merge( $form_data, [ 'providers' => [] ] );
			}

			$form_data['providers'] = array_merge( (array) $form_data['providers'], [ $this->core->slug => $provider ] );
			$form['post_content']   = wpforms_encode( $form_data );
		}

		return $form;
	}

	/**
	 * Prepare modifications for form content, if it's not locked.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form_data Form content.
	 *
	 * @return array|null
	 */
	protected function modify_form_data( $form_data ) {

		/**
		 * Connection is locked.
		 * Why? User clicked the "Save" button when one of the AJAX requests
		 * for retrieval data from API was in progress or failed.
		 */
		if (
			isset( $form_data['providers'][ $this->core->slug ]['__lock__'] ) &&
			absint( $form_data['providers'][ $this->core->slug ]['__lock__'] ) === 1
		) {
			return null;
		}

		// Modify content as we need, done by reference.
		foreach ( $form_data['providers'][ $this->core->slug ] as $connection_id => &$connection ) {

			if ( $connection_id === '__lock__' ) {
				unset( $form_data['providers'][ $this->core->slug ]['__lock__'] );
				continue;
			}

			try {
				$connection = ( new Connection( $connection ) )->get_data();
			} catch ( InvalidArgumentException $e ) {
				continue;
			}
		}
		unset( $connection );

		return $form_data;
	}

	/**
	 * Save the data for a new account and validate it.
	 *
	 * @since 2.0.0
	 *
	 * @return array|null
	 */
	public function ajax_account_save() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['apikey'] ) ) {
			return [
				'error' => esc_html__( 'Please provide a valid API Key.', 'wpforms-mailchimp' ),
			];
		}

		$data = wp_unslash( $_POST );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$api_key = trim( $data['apikey'] );
		$api     = $this->get_api( $api_key );

		if ( ! $api instanceof Api ) {
			return $api;
		}

		// Start account verification.
		$response = $api->get_account();

		if ( empty( $response['account_id'] ) || ! $api->success() ) {
			$details = ! empty( $response['detail'] ) ? $response['detail'] : '';

			// If status code is 401, it means that credentials are invalid or details are missing.
			// See https://mailchimp.com/developer/release-notes/added-support-for-401-unauthorized-response-code/.
			if ( empty( $details ) || $response['status'] === 401 ) {
				return [
					'error' => Helpers::get_api_credentials_error_message(),
				];
			}

			return [ /* translators: %s - error details. */
				'error' => sprintf( esc_html__( 'Mailchimp API error: %s', 'wpforms-mailchimp' ), $details ),
			];
		}

		$option_key = sanitize_key( $response['account_id'] );
		$label      = ! empty( $data['label'] ) ? sanitize_text_field( $data['label'] ) : sanitize_email( $response['email'] );

		// Save this account.
		wpforms_update_providers_options(
			$this->core->slug,
			[
				'api'   => $api_key,
				'label' => $label,
				'date'  => time(),
			],
			$option_key
		);

		return $response;
	}

	/**
	 * Content for the "Add New Account" modal.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function ajax_account_template_get() {

		$content = wpforms_mailchimp()->get( 'template' )->get_settings_template(
			'new-account-connection',
			[ 'provider_name' => $this->core->name ]
		);

		return [
			'title'   => esc_html__( 'New Mailchimp Account', 'wpforms-mailchimp' ),
			'content' => $content,
			'type'    => 'blue',
		];
	}

	/**
	 * Get the list of all saved connections.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function ajax_connections_get() {

		$connections = [
			'connections'  => ! empty( $this->get_connections_data() ) ? array_reverse( $this->get_connections_data(), true ) : [],
			'conditionals' => [],
		];

		// Get conditional logic for each connection ID.
		foreach ( $connections['connections'] as $connection ) {

			if ( empty( $connection['id'] ) ) {
				continue;
			}

			// This will either return an empty placeholder or complete set of rules, as a DOM.
			$connections['conditionals'][ $connection['id'] ] = wpforms_conditional_logic()->builder_block(
				[
					'form'       => $this->form_data,
					'type'       => 'panel',
					'parent'     => 'providers',
					'panel'      => $this->core->slug,
					'subsection' => $connection['id'],
					// TODO: Since WPForms 1.9.6, this property will be redundant and can be removed once addon requirements are updated.
					'reference'  => esc_html__( 'Marketing provider connection', 'wpforms-mailchimp' ),
				],
				false
			);
		}

		// Get accounts as well.
		$accounts = $this->ajax_accounts_get();

		return array_merge( $connections, $accounts );
	}

	/**
	 * Get the list of all accounts.
	 *
	 * @since 2.0.0
	 *
	 * @return array May return an empty subarray.
	 */
	public function ajax_accounts_get() {

		return [
			'accounts' => $this->get_accounts_data(),
		];
	}

	/**
	 * Retrieve MC data (lists, tags, custom fields), that is needed for the process.
	 *
	 * @since 2.0.0
	 *
	 * @return array|null Return null on any kind of error. Array of data otherwise.
	 */
	public function ajax_objects_get() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$options = $this->core->get_provider_options();

		// phpcs:disable
		if (
			empty( $options ) ||
			empty( $_POST['account_id'] ) ||
			empty( $_POST['connection_id'] ) ||
			empty( $_POST['sources'] ) ||
			empty( $options[ sanitize_key( wp_unslash( $_POST['account_id'] ) ) ] )
		) {
			return null;
		}

		$account_id = sanitize_key( wp_unslash( $_POST['account_id'] ) );
		$sources    = array_map( 'wp_validate_boolean', wp_unslash( $_POST['sources'] ) );
		// phpcs:enable

		$api = $this->get_api( $options[ $account_id ] );

		if ( ! $api instanceof Api ) {
			return $api;
		}

		// Retrieve lists.
		if ( isset( $sources['lists'] ) ) {
			$sources['lists'] = $api->get_lists(
				[
					'fields' => 'lists.id,lists.name,lists.marketing_permissions',
				]
			);
		}

		$list_id = ! empty( $_POST['list_id'] ) ? sanitize_key( wp_unslash( $_POST['list_id'] ) ) : ''; // phpcs:disable WordPress.Security.NonceVerification.Missing

		// Retrieve tags.
		if ( isset( $sources['tags'] ) ) {
			$connections     = $this->get_connections_data();
			$mc_tags         = array_column(
				$api->get_tags(
					$list_id,
					[
						'fields' => 'segments.name',
						'type'   => 'static',
					]
				),
				'name'
			);
			$sources['tags'] = $mc_tags;
		}

		// Retrieve groups.
		if ( isset( $sources['groups'] ) ) {
			$sources['groups'] = $api->get_interests(
				$list_id,
				[
					'fields' => 'interests.id,interests.name',
				]
			);
		}

		// Retrieve custom fields.
		if ( isset( $sources['mergeFields'] ) ) {
			$required_fields = array_column(
				$api->get_fields(
					$list_id,
					[
						'fields'   => 'merge_fields.tag,merge_fields.name',
						'required' => 'true',
					]
				),
				'name',
				'tag'
			);

			$optional_fields = array_column(
				$api->get_fields(
					$list_id,
					[
						'fields'   => 'merge_fields.tag,merge_fields.name',
						'required' => 'false',
					]
				),
				'name',
				'tag'
			);

			$sources['mergeFields'] = [
				'required' => $required_fields,
				'optional' => $optional_fields,
			];
		}

		return $sources;
	}

	/**
	 * Retrieve an API client.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $data API key or an array from `wp_options` table, which consists of an API key.
	 *
	 * @return null|array|\WPFormsMailchimp\Provider\Api
	 */
	protected function get_api( $data ) {

		if ( empty( $data ) ) {
			return null;
		}

		// If passed data is an array, then it's an account data. Otherwise - it's an API key.
		if ( is_array( $data ) ) {
			$apikey = ! empty( $data['api'] ) ? $data['api'] : '';
		} else {
			$apikey = $data;
		}

		if ( empty( $apikey ) ) {
			return [
				'error' => esc_html__( 'Missing a valid API key.', 'wpforms-mailchimp' ),
			];
		}

		try {
			$api = new Api( $apikey );
		} catch ( \Exception $e ) {
			return [
				'error' => Helpers::get_api_credentials_error_message(),
			];
		}

		return $api;
	}

	/**
	 * Retrieve saved provider connections data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_connections_data() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! isset( $this->form_data['providers'][ $this->core->slug ] ) ) {
			return [];
		}

		if ( ! empty( $this->connections ) ) {
			return $this->connections;
		}

		foreach ( $this->form_data['providers'][ $this->core->slug ] as $connection_id => &$connection ) {
			$connection = (array) $connection;

			// BC for connections <= v1.4.2.
			if ( ! isset( $connection['id'] ) || wpforms_is_empty_string( $connection['id'] ) ) {
				$connection += [ 'id' => $connection_id ];
			}

			if ( ! isset( $connection['form_id'] ) || wpforms_is_empty_string( $connection['form_id'] ) ) {
				$connection += [ 'form_id' => $this->form_data['id'] ];
			}

			try {
				$connection = ( new Connection( $connection ) )->get_data();
			} catch ( InvalidArgumentException $e ) {
				continue;
			}
		}
		unset( $connection );

		$this->connections = $this->form_data['providers'][ $this->core->slug ];

		return $this->connections;
	}

	/**
	 * Retrieve saved provider accounts data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_accounts_data() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$providers = wpforms_get_providers_options();
		$accounts  = [];

		if ( empty( $providers[ $this->core->slug ] ) ) {
			return $accounts;
		}

		$update_required = false;

		// We might have several accounts.
		foreach ( $providers[ $this->core->slug ] as $option_id => $option ) {

			$api = $this->get_api( $option );

			if ( ! $api instanceof Api ) {
				continue;
			}

			// API call.
			$response = $api->get_account();

			// API key may be invalid or has been disabled.
			if ( $api->get_http_status_code() === 401 ) {
				unset( $providers[ $this->core->slug ][ $option_id ] );
				$update_required = true;

				continue;
			}

			if ( ! $api->success() || empty( $response['account_id'] ) ) {
				continue;
			}

			$accounts[ $option_id ] = $option['label'];
		}

		if ( $update_required ) {
			update_option( 'wpforms_providers', $providers );
		}

		return $accounts;
	}

	/**
	 * Display generated fields with all markup for using in provider's connection.
	 * Used internally in templates.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Field name.
	 *
	 * @return array|string
	 */
	public function get_field_html( $name ) {

		$fields = [
			'email' => wpforms_panel_field(
				'select',
				$this->core->slug,
				'EMAIL',
				$this->form_data,
				esc_html__( 'Subscriber Email', 'wpforms-mailchimp' ),
				[
					'parent'        => 'providers',
					'field_name'    => 'providers[' . $this->core->slug . '][%connection_id%][fields][EMAIL]',
					'field_map'     => [ 'text', 'email' ],
					'placeholder'   => esc_html__( '--- Select Email Field ---', 'wpforms-mailchimp' ),
					'after_tooltip' => '<span class="required">*</span>',
					'input_class'   => 'wpforms-required',
					'input_id'      => 'wpforms-panel-field-' . $this->core->slug . '-%connection_id%-email',
				],
				false
			),
		];

		return isset( $fields[ $name ] ) ? $fields[ $name ] : '';
	}

	/**
	 * Use this method to register own templates for the form builder.
	 * Make sure to have `tmpl-` in template name in `<script id="tmpl-*">`.
	 *
	 * @since 2.0.0
	 */
	public function builder_custom_templates() {

		?>
		<!-- Single MC connection -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'connection' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: general data -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-data">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'general' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: SUBSCRIBE -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-subscribe">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'subscribe', [ 'field_email' => $this->get_field_html( 'email' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: REQUIRED FIELDS -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-required-fields">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'required-fields' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: ERROR -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-error">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'error' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: UNSUBSCRIBE -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-unsubscribe">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'unsubscribe', [ 'field_email' => $this->get_field_html( 'email' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: ARCHIVE -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-archive">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'archive', [ 'field_email' => $this->get_field_html( 'email' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: PERMANENTLY DELETE -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-delete">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'delete', [ 'field_email' => $this->get_field_html( 'email' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>

		<!-- Single MC connection block: RECORD EVENT -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-record-event">
			<?php echo wpforms_mailchimp()->get( 'template' )->get_builder_template( 'record-event', [ 'field_email' => $this->get_field_html( 'email' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>
		<?php
	}

	/**
	 * Enqueue JavaScript file(s).
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {

		parent::enqueue_assets();

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-mailchimp-admin-builder',
			WPFORMS_MAILCHIMP_URL . "assets/js/mailchimp-builder{$min}.js",
			[ 'wpforms-admin-builder-providers', 'choicesjs' ],
			WPFORMS_MAILCHIMP_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-mailchimp-admin-builder',
			'wpformsMailchimpBuilderVars',
			[
				'i18n' => [
					'generalAjaxError'    => esc_html__( 'Something went wrong while performing an AJAX request.', 'wpforms-mailchimp' ),
					// The `nameFieldFormats` key is deprecated since 2.5.0.
					'nameFieldFormats'    => [
						'full'   => esc_html__( 'Full', 'wpforms-mailchimp' ),
						'first'  => esc_html__( 'First', 'wpforms-mailchimp' ),
						'middle' => esc_html__( 'Middle', 'wpforms-mailchimp' ),
						'last'   => esc_html__( 'Last', 'wpforms-mailchimp' ),
					],
					'gdpr'                => wp_kses(
						sprintf( /* translators: %1$s - Mailchimp page URL: Collect Consent with GDPR Forms, %2$s - audience name, %3$s - connection name. */
							__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">Marketing Permissions</a> are required for the "<strong>%2$s</strong>" audience of the "<strong>%3$s</strong>" connection. Please add a GDPR field to your form.', 'wpforms-mailchimp' ),
							'https://mailchimp.com/help/collect-consent-with-gdpr-forms/',
							'{audience}',
							'{connection}'
						),
						[
							'a'      => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
							'strong' => [],
						]
					),
					'providerPlaceholder' => esc_html__( '--- Select Mailchimp Field ---', 'wpforms-mailchimp' ),
				],
			]
		);
	}
}
