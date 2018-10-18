<?php
/**
 * Adds custom roles to Sermon Manager.
 *
 * @since   2.13.0
 *
 * @package SermonManager
 */

/**
 * Define SM_Roles.
 */
class SM_Roles {
	/**
	 * Add sermon managing capabilities to administrator, editor, author.
	 */
	public static function init() {
		$role_list = array( 'administrator', 'editor', 'author' );
		foreach ( $role_list as $role_name ) {
			$role = get_role( $role_name );
			if ( null === $role || ! ( $role instanceof WP_Role ) ) {
				continue;
			}
			// Read sermons.
			$role->add_cap( 'read_wpfc_sermon' );
			// Edit sermons.
			$role->add_cap( 'edit_wpfc_sermon' );
			$role->add_cap( 'edit_wpfc_sermons' );
			$role->add_cap( 'edit_private_wpfc_sermons' );
			$role->add_cap( 'edit_published_wpfc_sermons' );
			// Delete sermons.
			$role->add_cap( 'delete_wpfc_sermon' );
			$role->add_cap( 'delete_wpfc_sermons' );
			$role->add_cap( 'delete_published_wpfc_sermons' );
			$role->add_cap( 'delete_private_wpfc_sermons' );
			// Publish sermons.
			$role->add_cap( 'publish_wpfc_sermons' );
			// Read private sermons.
			$role->add_cap( 'read_private_wpfc_sermons' );
			// Manage categories & tags.
			$role->add_cap( 'manage_wpfc_categories' );
			// Add additional roles for administrator.
			if ( 'administrator' === $role_name ) {
				// Access to Sermon Manager Settings.
				$role->add_cap( 'manage_wpfc_sm_settings' );
			}
			// Add additional roles for administrator and editor.
			if ( 'author' !== $role_name ) {
				$role->add_cap( 'edit_others_wpfc_sermons' );
				$role->add_cap( 'delete_others_wpfc_sermons' );
			}
		}
	}
}

SM_Roles::init();
