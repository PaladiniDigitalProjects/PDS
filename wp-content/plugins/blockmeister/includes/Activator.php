<?php

namespace ProDevign\BlockMeister;

class Activator {

	public function activate() {

		// check if other version of plugin is active (if so then deactivate the other one and explain this in an admin notice)
		$plugin_being_activated = $_GET[ 'plugin'];
		$other_plugin = $plugin_being_activated === 'blockmeister/blockmeister.php' ? 'blockmeister-premium/blockmeister.php' : 'blockmeister/blockmeister.php';

		// only one version (free or premium) should be active
		if ( $is_other_plugin_active = is_plugin_active( $other_plugin ) ) {
			deactivate_plugins( $other_plugin );
		}

		if ( $is_installing = $this->set_time_and_version_options() ) {
			$this->install();
		}
	}

	/**
	 * Add time and version on DB
	 */
	public function set_time_and_version_options() {
		$is_installing  = ! get_option( 'blockmeister_installed', false );
		$stored_version = get_option( 'blockmeister_version', false );

		if ( $is_installing ) {
			update_option( 'blockmeister_installed', time() );
		} else {
			if ( BlockMeister::VERSION !== $stored_version ) {
				update_option( 'blockmeister_updated', time() );
			}
		}

		update_option( 'blockmeister_version', BlockMeister::VERSION );

		return $is_installing;
	}

	private function install() {
		$this->add_capabilities();
	}

	/**
	 * Adds blockmeister specific capabilities to the administrator user roles needed
	 * to manage custom post types, meta and taxonomies.
	 */
	public function add_capabilities() {

		if ( $administrator_role = get_role( 'administrator' ) ) {
			// post type 'blockmeister_pattern' related:
			$this->add_post_type_capabilities( $administrator_role, 'blockmeister_pattern' );
			// taxonomies related:
			$administrator_role->add_cap( 'manage_blockmeister_pattern_category' );
			$administrator_role->add_cap( 'manage_blockmeister_pattern_keyword' );
		}

	}

	/**
	 * @param \WP_Role $role
	 * @param string $post_type
	 */
	private function add_post_type_capabilities( $role, $post_type ) {

		if ( $role ) {
			$post_type_capabilities = get_post_type_capabilities( (object) [
				'capability_type' => $post_type,
				'map_meta_cap'    => true,
				'capabilities'    => [],
			] );
			foreach ( $post_type_capabilities as $key => $capability ) {
				if ( ! isset( $role->capabilities[ $capability ] ) ) {
					$role->add_cap( $capability );
				}
			}
		}

	}

}