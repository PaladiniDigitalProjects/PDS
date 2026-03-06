<?php

namespace WPFormsMailchimp\Provider;

use DrewM\MailChimp\MailChimp as LibMailchimp;

/**
 * Class Api which extends 3rd party Mailchimp library to provide more WPForms-related functionality.
 *
 * @since 2.0.0
 */
class Api extends LibMailchimp {

	/**
	 * Number of records to return.
	 * By default API returns 10. We raise it to 1000.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	const LIMIT = 1000;

	/**
	 * Retrieve details about the Mailchimp user account.
	 *
	 * @since 2.0.0
	 *
	 * @return array|bool
	 */
	public function get_account() {

		return $this->get( '' );
	}

	/**
	 * Retrieve lists info.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_lists( $args = [] ) {

		$args     = wp_parse_args( $args, $this->get_defaults() );
		$response = $this->get( 'lists', $args );

		return $this->success() && ! empty( $response['lists'] ) ? $response['lists'] : [];
	}

	/**
	 * Retrieve tags info.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_tags( $list_id, $args = [] ) {

		$args     = wp_parse_args( $args, $this->get_defaults() );
		$response = $this->get( "lists/{$list_id}/segments", $args );

		return $this->success() && ! empty( $response['segments'] ) ? $response['segments'] : [];
	}

	/**
	 * Retrieve merge fields.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_fields( $list_id, $args = [] ) {

		$args     = wp_parse_args( $args, $this->get_defaults() );
		$response = $this->get( "lists/{$list_id}/merge-fields", $args );

		return $this->success() && ! empty( $response['merge_fields'] ) ? $response['merge_fields'] : [];
	}

	/**
	 * Retrieve a list of interest categories.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_interest_cats( $list_id, $args = [] ) {

		$args     = wp_parse_args( $args, $this->get_defaults() );
		$response = $this->get( "lists/{$list_id}/interest-categories", $args );

		return $this->success() && ! empty( $response['categories'] ) ? $response['categories'] : [];
	}

	/**
	 * Retrieve a list of this category's interests.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_interests( $list_id, $args = [] ) {

		$interest_cats = $this->get_interest_cats( $list_id );
		$interests     = [];

		if ( empty( $interest_cats ) ) {
			return $interests;
		}

		$args = wp_parse_args( $args, $this->get_defaults() );

		foreach ( $interest_cats as $cat ) {

			if ( empty( $cat['id'] ) ) {
				continue;
			}

			$cat_id               = $cat['id'];
			$interests[ $cat_id ] = [
				'id'        => $cat_id,
				'name'      => isset( $cat['title'] ) ? trim( $cat['title'] ) : esc_html__( 'Unknown Group', 'wpforms-mailchimp' ),
				'type'      => isset( $cat['type'] ) ? $cat['type'] : 'checkboxes',
				'interests' => [],
			];

			$response = $this->get( "lists/{$list_id}/interest-categories/{$cat_id}/interests", $args );

			if ( ! $this->success() || empty( $response['interests'] ) ) {
				continue;
			}

			$interests[ $cat_id ]['interests'] = $response['interests'];
		}

		return $interests;
	}

	/**
	 * Retrieve member info.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_list_member( $list_id, $email, $args = [] ) {

		$hash     = self::subscriberHash( $email );
		$response = $this->get( "lists/{$list_id}/members/{$hash}", $args );

		return $this->success() && ! empty( $response ) ? $response : [];
	}

	/**
	 * Add or update list member.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id               List ID.
	 * @param string $email                 Email address.
	 * @param array  $args                  Arguments.
	 * @param bool   $skip_merge_validation If true, member data will be accepted without merge field values.
	 *
	 * @return array
	 */
	public function add_update_list_member( $list_id, $email, $args = [], $skip_merge_validation = false ) {

		$hash     = self::subscriberHash( $email );
		$response = $this->put( "lists/{$list_id}/members/{$hash}?skip_merge_validation={$skip_merge_validation}", $args );

		return $this->success() && ! empty( $response ) ? $response : [];
	}

	/**
	 * Update list member.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id               List ID.
	 * @param string $email                 Email address.
	 * @param array  $args                  Arguments.
	 * @param bool   $skip_merge_validation If true, member data will be accepted without merge field values.
	 *
	 * @return array
	 */
	public function update_list_member( $list_id, $email, $args = [], $skip_merge_validation = true ) {

		$hash     = self::subscriberHash( $email );
		$response = $this->patch( "lists/{$list_id}/members/{$hash}?skip_merge_validation={$skip_merge_validation}", $args );

		return $this->success() && ! empty( $response ) ? $response : [];
	}

	/**
	 * Archive list member.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 *
	 * @return bool
	 */
	public function archive_list_member( $list_id, $email ) {

		$hash = self::subscriberHash( $email );

		$this->delete( "lists/{$list_id}/members/{$hash}" );

		return $this->success();
	}

	/**
	 * Delete list member.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 *
	 * @return bool
	 */
	public function delete_list_member( $list_id, $email ) {

		$hash = self::subscriberHash( $email );

		$this->post( "lists/{$list_id}/members/{$hash}/actions/delete-permanent" );

		return $this->success();
	}

	/**
	 * Add or remove member tags.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 * @param array  $tags    Tags.
	 *
	 * @return bool
	 */
	public function update_member_tags( $list_id, $email, $tags ) {

		$hash = self::subscriberHash( $email );

		$this->post( "lists/{$list_id}/members/{$hash}/tags", $tags );

		return $this->success();
	}

	/**
	 * Add a new note for a specific subscriber.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 * @param array  $note    Note.
	 *
	 * @return array
	 */
	public function add_member_note( $list_id, $email, $note ) {

		$hash     = self::subscriberHash( $email );
		$response = $this->post( "lists/{$list_id}/members/{$hash}/notes", $note );

		return $this->success() && ! empty( $response ) ? $response : [];
	}

	/**
	 * Add an event for a list member.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 * @param array  $event   Event.
	 *
	 * @return bool
	 */
	public function add_member_event( $list_id, $email, $event ) {

		$hash = self::subscriberHash( $email );

		$this->post( "lists/{$list_id}/members/{$hash}/events", $event );

		return $this->success();
	}

	/**
	 * List member events.
	 *
	 * @since 2.0.0
	 *
	 * @param string $list_id List ID.
	 * @param string $email   Email address.
	 * @param array  $args    Arguments.
	 *
	 * @return array
	 */
	public function get_member_events( $list_id, $email, $args = [] ) {

		$args     = wp_parse_args( $args, $this->get_defaults() );
		$hash     = self::subscriberHash( $email );
		$response = $this->get( "lists/{$list_id}/members/{$hash}/events", $args );

		return $this->success() && ! empty( $response['events'] ) ? $response['events'] : [];
	}

	/**
	 * Retrieve the HTTP status code from the headers or API response body.
	 *
	 * @since 2.0.0
	 *
	 * @return int HTTP status code.
	 */
	public function get_http_status_code() {

		$response = $this->getLastResponse();

		if ( ! empty( $response['headers'] ) && isset( $response['headers']['http_code'] ) ) {
			return (int) $response['headers']['http_code'];
		}

		$remote_body = ! empty( $response['body'] ) ? json_decode( $response['body'], true ) : [];

		if ( isset( $remote_body['status'] ) ) {
			return (int) $remote_body['status'];
		}

		return 418;
	}

	/**
	 * Retrieve default arguments for request.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_defaults() {

		return [
			'count' => self::LIMIT,
		];
	}
}
