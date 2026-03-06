<?php

namespace WPFormsMailchimp\Provider;

/**
 * Class Connection.
 *
 * @since 2.0.0
 */
class Connection {

	/**
	 * Subscribe connection type.
	 *
	 * @since 2.0.0
	 */
	const TYPE_SUBSCRIBE = 'subscribe';

	/**
	 * Unsubscribe connection type.
	 *
	 * @since 2.0.0
	 */
	const TYPE_UNSUBSCRIBE = 'unsubscribe';

	/**
	 * Archive connection type.
	 *
	 * @since 2.0.0
	 */
	const TYPE_ARCHIVE = 'archive';

	/**
	 * Permanently delete connection type.
	 *
	 * @since 2.0.0
	 */
	const TYPE_DELETE = 'delete';

	/**
	 * Record event connection type.
	 *
	 * @since 2.0.0
	 */
	const TYPE_RECORD_EVENT = 'record_event';

	/**
	 * Connection data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param array $raw_data Connection data.
	 *
	 * @throws \InvalidArgumentException Emitted when something went wrong.
	 */
	public function __construct( $raw_data ) {

		if ( ! is_array( $raw_data ) || empty( $raw_data ) ) {
			throw new \InvalidArgumentException( 'Unexpected connection data.' );
		}

		$this->set_data( $raw_data );
	}

	/**
	 * Sanitize and set connection data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $raw_data Connection data.
	 */
	protected function set_data( $raw_data ) {

		$this->data = array_replace_recursive( $this->get_required_data(), $raw_data );

		$this->set_id()
			 ->set_name()
			 ->set_form_id()
			 ->set_account_id()
			 ->set_list_id()
			 ->set_email()
			 ->set_action()
			 ->set_options();
	}

	/**
	 * Sanitize and set connection ID.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_id() {

		$this->data['id'] = sanitize_key( $this->data['id'] );

		return $this;
	}

	/**
	 * Sanitize and set connection name.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_name() {

		$this->data['connection_name'] = sanitize_text_field( $this->data['connection_name'] );

		return $this;
	}

	/**
	 * Sanitize and set connection form ID.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_form_id() {

		if ( ! wpforms_is_empty_string( $this->data['form_id'] ) ) {
			$this->data['form_id'] = absint( $this->data['form_id'] );
		}

		return $this;
	}

	/**
	 * Sanitize and set connection account ID.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_account_id() {

		$this->data['account_id'] = sanitize_key( $this->data['account_id'] );

		return $this;
	}

	/**
	 * Sanitize and set connection list ID.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_list_id() {

		$this->data['list_id'] = sanitize_key( $this->data['list_id'] );

		return $this;
	}

	/**
	 * Sanitize and set connection email.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_email() {

		if ( ! wpforms_is_empty_string( $this->data['fields']['EMAIL'] ) ) {
			$email                         = explode( '.', $this->data['fields']['EMAIL'] );
			$this->data['fields']['EMAIL'] = absint( $email[0] );
		}

		return $this;
	}

	/**
	 * Sanitize and set connection action.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_action() {

		// BC for connections saved on <= v1.4.2.
		if ( $this->data['action'] === false ) {
			$this->data['action'] = self::TYPE_SUBSCRIBE;

			return $this;
		}

		if ( ! in_array( $this->data['action'], $this->get_types(), true ) ) {
			$this->data['action'] = '';
		}

		return $this;
	}

	/**
	 * Sanitize and set connection options by action.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_options() {

		switch ( $this->data['action'] ) {
			case self::TYPE_SUBSCRIBE:
				$this
					->set_groups()
					->set_tags()
					->set_fields_meta();
				break;

			case self::TYPE_RECORD_EVENT:
				$this->set_event_name();
				break;

			default:
				break;
		}

		return $this;
	}

	/**
	 * Sanitize and set connection groups.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_groups() {

		// Groups already exist in connection data. We just need to sanitize groups IDs.
		if ( ! empty( $this->data['groups'] ) && is_array( $this->data['groups'] ) ) {
			return $this->sanitize_groups();
		}

		return $this->collect_post_groups();
	}

	/**
	 * Sanitize and set connection tags.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_tags() {

		// Tags already exist in connection data. We just need to sanitize tag names.
		if ( isset( $this->data['tag_names'] ) || isset( $this->data['tag_removes'] ) ) {
			$this->data['tag_names']   = ! empty( $this->data['tag_names'] ) ? array_map( 'sanitize_text_field', (array) $this->data['tag_names'] ) : [];
			$this->data['tag_removes'] = ! empty( $this->data['tag_removes'] ) ? array_map( 'sanitize_text_field', (array) $this->data['tag_removes'] ) : [];

			// Remove unused data in this case.
			unset( $this->data['tags'] );

			return $this;
		}

		return $this->collect_post_tags();
	}

	/**
	 * Sanitize groups IDs.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function sanitize_groups() {

		foreach ( $this->data['groups'] as $group_id => $interests ) {

			if ( empty( $interests ) || ! is_array( $interests ) ) {
				continue;
			}

			foreach ( $interests as $key => $unused ) {
				// Connections which were saved on <= v1.4.2, store groups IDs like as array keys.
				// We should re-save them as array values.
				$this->data['groups'][ $group_id ] = ( $key !== 0 ) ? array_keys( $interests ) : $interests;

				break;
			}
		}

		$this->data['groups'] = map_deep( $this->data['groups'], 'sanitize_key' );

		// Remove unused data in this case.
		if ( isset( $this->data['groups_data'] ) ) {
			unset( $this->data['groups_data'] );
		}

		return $this;
	}

	/**
	 * Collect group IDs from global $_POST variable.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function collect_post_groups() {

		// No groups in connection data - it's a saving process.
		$provider_slug = wpforms_mailchimp()->provider_slug;
		$connection_id = $this->data['id'];
		$form_post     = ! empty( $_POST['data'] ) ? json_decode( stripslashes( $_POST['data'] ), true ) : []; // phpcs:ignore
		$groups        = [];

		foreach ( $form_post as $post_pair ) {

			$search_key = "providers[{$provider_slug}][{$connection_id}][groups_data][";

			if (
				empty( $post_pair['value'] ) ||
				empty( $post_pair['name'] ) ||
				false === strpos( $post_pair['name'], $search_key )
			) {
				continue;
			}

			$group_id = sanitize_key(
				str_replace(
					[
						$search_key,
						'][]',
						']',
					],
					'',
					$post_pair['name']
				)
			);

			$groups[ $group_id ][] = map_deep( $post_pair['value'], 'sanitize_key' );
		}

		if ( ! empty( $groups ) ) {
			$this->data['groups'] = $groups;
		}

		return $this;
	}

	/**
	 * Collect all tag names.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function collect_post_tags() {

		$add_tags    = $this->collect_add_tags();
		$remove_tags = $this->collect_remove_tags();

		// If user provided new tags.
		if ( isset( $this->data['tags']['new'] ) ) {
			$this->data['tag_news'] = wpforms_chain( $this->data['tags']['new'] )
				->trim()
				->explode( ',' )
				->map(
					static function( $name ) {

						// A tag name cannot be over 100 characters.
						return wp_html_excerpt( sanitize_text_field( $name ), 100 );
					}
				)
				->array_filter(
					static function( $name ) {

						return ! wpforms_is_empty_string( $name );
					}
				)
				->array_merge( (array) $add_tags )
				->array_unique()
				->array_diff( (array) $add_tags )
				->value();
		}

		$this->data['tag_names']   = $add_tags;
		$this->data['tag_removes'] = $remove_tags;

		unset( $this->data['tags'] );

		return $this;
	}

	/**
	 * Collect connection tags which will be added to a contact.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	protected function collect_add_tags() {

		return $this->collect_post_tags_by_source( 'add' );
	}

	/**
	 * Collect connection tags which will be removed from a contact.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	protected function collect_remove_tags() {

		return $this->collect_post_tags_by_source( 'remove' );
	}

	/**
	 * Collect tag names from global $_POST variable.
	 *
	 * @since 2.1.0
	 *
	 * @param string $source_key Source key.
	 *
	 * @return array
	 */
	private function collect_post_tags_by_source( $source_key ) {

		// No tags in connection data - it's a saving process. We need to grab tags from $_POST variable.
		$provider_slug = wpforms_mailchimp()->provider_slug;
		$connection_id = $this->data['id'];
		$form_post     = ! empty( $_POST['data'] ) ? json_decode( stripslashes( $_POST['data'] ), true ) : []; // phpcs:ignore WordPress.Security

		// Native WPForms saving doesn't support multiple selects.
		// We need to get tags data from $_POST variable.
		return wpforms_chain( $form_post )
			->map(
				static function( $post_pair ) use ( $provider_slug, $connection_id, $source_key ) {

					if (
						empty( $post_pair['name'] ) ||
						$post_pair['name'] !== "providers[{$provider_slug}][{$connection_id}][tags][{$source_key}][]"
					) {
						return '';
					}

					return sanitize_text_field( $post_pair['value'] );
				}
			)
			->array_filter()
			->array_values()
			->value();
	}

	/**
	 * Set connection fields meta.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_fields_meta() {

		// Firstly, for connections which were saved on <= v1.4.2.
		if ( ! empty( $this->data['fields'] ) && is_array( $this->data['fields'] ) && empty( $this->data['fields_meta'] ) ) {
			$this->data['fields_meta'] = [];
			$name_fields               = wpforms_get_form_fields( $this->data['form_id'], [ 'name' ] );
			$name_fields_ids           = ! empty( $name_fields ) && is_array( $name_fields ) ? array_column( $name_fields, 'id' ) : [];

			foreach ( $this->data['fields'] as $merge_tag => $value ) {

				// Don't include EMAIL and empty merge fields.
				if ( $merge_tag === 'EMAIL' || wpforms_is_empty_string( $value ) ) {
					continue;
				}

				$field = explode( '.', $value );
				$id    = $field[0]; // WPForms Field ID.
				$key   = ! empty( $field[1] ) ? $field[1] : 'value';

				$field_id = $id;

				if ( in_array( $id, $name_fields_ids, true ) ) {
					$field_id = ( $key === 'value' ) ? "{$id}.full" : "{$id}.{$key}";
				}

				$this->data['fields_meta'][] = [
					'name'     => sanitize_text_field( $merge_tag ),
					'field_id' => sanitize_text_field( $field_id ),
				];
			}

			return $this;
		}

		if ( empty( $this->data['fields_meta'] ) || ! is_array( $this->data['fields_meta'] ) ) {
			$this->data['fields_meta'] = [];

			return $this;
		}

		$this->sanitize_fields_meta();

		return $this;
	}

	/**
	 * Sanitize fields meta.
	 *
	 * @since 2.0.0
	 */
	protected function sanitize_fields_meta() {

		$fields_meta = [];

		foreach ( $this->data['fields_meta'] as $field ) {
			$name     = isset( $field['name'] ) ? sanitize_text_field( $field['name'] ) : '';
			$field_id = isset( $field['field_id'] ) ? sanitize_text_field( $field['field_id'] ) : '';

			if ( wpforms_is_empty_string( $name ) && wpforms_is_empty_string( $field_id ) ) {
				continue;
			}

			$fields_meta[] = [
				'name'     => $name,
				'field_id' => $field_id,
			];
		}

		$this->data['fields_meta'] = $fields_meta;
	}

	/**
	 * Set event name.
	 *
	 * @since 2.0.0
	 *
	 * @return \WPFormsMailchimp\Provider\Connection
	 */
	protected function set_event_name() {

		// Max - 30 characters.
		$this->data['event_name'] = wp_html_excerpt( sanitize_key( $this->data['event_name'] ), 30 );

		// Min - 2 characters.
		if ( 1 === strlen( $this->data['event_name'] ) ) {
			$this->data['event_name'] .= '_';
		}

		return $this;
	}

	/**
	 * Retrieve connection data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_data() {

		return $this->data;
	}

	/**
	 * Retrieve defaults for connection data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_required_data() {

		$required = [
			'id'              => false,
			'connection_name' => false,
			'form_id'         => false,
			'account_id'      => false,
			'list_id'         => false,
			'action'          => false,
			'fields'          => [
				'EMAIL' => false,
			],
		];

		if ( ! empty( $this->data['action'] ) && $this->data['action'] === self::TYPE_RECORD_EVENT ) {
			$required = array_merge( $required, [ 'event_name' => false ] );
		}

		return $required;
	}

	/**
	 * Retrieve the list of all connection types.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_types() {

		return [
			self::TYPE_SUBSCRIBE,
			self::TYPE_UNSUBSCRIBE,
			self::TYPE_ARCHIVE,
			self::TYPE_DELETE,
			self::TYPE_RECORD_EVENT,
		];
	}

	/**
	 * Determine if connection data is valid.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_valid() {

		foreach ( $this->get_required_data() as $key => $value ) {

			if (
				! isset( $this->data[ $key ] ) ||
				$this->data[ $key ] === false ||
				wpforms_is_empty_string( $this->data[ $key ] )
			) {
				return false;
			}
		}

		return true;
	}
}
