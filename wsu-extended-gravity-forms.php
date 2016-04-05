<?php
/*
Plugin Name: WSU Extended Gravity Forms
Version: 0.2.3
Plugin URI: https://web.wsu.edu/
Description: Extends and modifies default functionality in Gravity Forms.
Author: washingtonstateuniversity, jeremyfelt
Author URI: http://web.wsu.edu
*/

/**
 * Class WSU_Extended_Gravity_Forms
 */
class WSU_Extended_Gravity_Forms {
	/**
	 * Setup hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'modify_roles' ) );

		remove_action( 'after_plugin_row_gwlimitchoices/gwlimitchoices.php', 'after_perk_plugin_row', 10, 2 );
		remove_action( 'after_plugin_row_gwlimitcheckboxes/gwlimitcheckboxes.php', 'after_perk_plugin_row', 10, 2 );
		remove_action( 'after_plugin_row_gwwordcount/gwwordcount.php', 'after_perk_plugin_row', 10, 2 );
	}

	/**
	 * Modify the editor role so that users can create and modify forms without
	 * needing to be an administrator. Do not allow editors to modify settings.
	 */
	public function modify_roles() {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		$editor = get_role( 'editor' );

		// Provide access to most basic gravityforms functionality.
		$editor->add_cap( 'gravityforms_edit_forms' );
		$editor->add_cap( 'gravityforms_delete_forms' );
		$editor->add_cap( 'gravityforms_create_form' );
		$editor->add_cap( 'gravityforms_view_entries' );
		$editor->add_cap( 'gravityforms_edit_entries' );
		$editor->add_cap( 'gravityforms_delete_entries' );
		$editor->add_cap( 'gravityforms_export_entries' );
		$editor->add_cap( 'gravityforms_view_entry_notes' );
		$editor->add_cap( 'gravityforms_edit_entry_notes' );

		// Do not allow settings to be changed or the plugin to be uninstalled.
		$editor->remove_cap( 'gravityforms_view_settings' );
		$editor->remove_cap( 'gravityforms_edit_settings' );
		$editor->remove_cap( 'gravityforms_uninstall' );
	}
}
$wsu_extended_gravity_forms = new WSU_Extended_Gravity_Forms();
