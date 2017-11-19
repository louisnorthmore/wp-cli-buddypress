<?php
/**
 * Manage BuddyPress components.
 *
 * @since 1.1.0
 */
class BPCLI_Core extends BPCLI_Component {

	/**
	 * Activate a component.
	 *
	 * ## OPTIONS
	 *
	 * <component>
	 * : Name of the component to activate.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bp core activate groups
	 *     Success: The Groups component has been activated.
	 */
	public function activate( $args, $assoc_args ) {
		$c = $args[0];

		if ( bp_is_active( $c ) ) {
			WP_CLI::error( sprintf( 'The %s component is already active.', ucfirst( $c ) ) );
		}

		$acs =& buddypress()->active_components;

		// Set for the rest of the page load.
		$acs[ $c ] = 1;

		// Save in the db.
		bp_update_option( 'bp-active-components', $acs );

		// Adds compatability with versions before 2.3, when the bp-core-schema.php
		// was renamed into bp-core-admin-schema.php.
		$admin = ( bp_get_version() >= 2.3 ) ? 'admin-' : '';

		// Ensure that dbDelta() is defined.
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		// Run the setup, in case tables have to be created.
		require_once( BP_PLUGIN_DIR . 'bp-core/admin/bp-core-' . $admin . 'schema.php' );
		bp_core_install( $acs );
		bp_core_add_page_mappings( $acs );

		WP_CLI::success( sprintf( 'The %s component has been activated.', ucfirst( $c ) ) );
	}

	/**
	 * Deactivate a component.
	 *
	 * ## OPTIONS
	 *
	 * <component>
	 * : Name of the component to deactivate.
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bp core deactivate groups
	 *     Success: The Groups component has been deactivated.
	 */
	public function deactivate( $args, $assoc_args ) {
		$c = $args[0];

		if ( ! bp_is_active( $c ) ) {
			WP_CLI::error( sprintf( 'The %s component is not active.', ucfirst( $c ) ) );
		}

		if ( array_key_exists( $c, bp_core_get_components( 'required' ) ) ) {
			WP_CLI::error( sprintf( 'You cannot deactivate a required component.' ) );
		}

		$acs =& buddypress()->active_components;

		// Set for the rest of the page load.
		unset( $acs[ $c ] );

		// Save in the db.
		bp_update_option( 'bp-active-components', $acs );

		WP_CLI::success( sprintf( 'The %s component has been deactivated.', ucfirst( $c ) ) );
	}
}

WP_CLI::add_command( 'bp core', 'BPCLI_Core' );
