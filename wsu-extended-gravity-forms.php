<?php
/*
Plugin Name: WSU Extended Gravity Forms
Version: 0.3.0
Plugin URI: https://web.wsu.edu/
Description: Extends and modifies default functionality in Gravity Forms.
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu
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
		add_action( 'gform_after_submission', array( $this, 'anonymous_submission' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'add_anonymous_submissions_tooltip') );
		add_filter( 'gform_form_settings', array( $this, 'anonymous_submissions_form_setting' ), 10, 2 );
		add_filter( 'gform_pre_form_settings_save', array( $this, 'save_anonymous_submissions_form_setting' ) );
		add_filter( 'gform_export_fields', array( $this, 'remove_export_fields' ) );

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

	/**
	 * Remove values from the 'ip', 'created_by', and 'user_agent' fields for
	 * forms with anonymous submissions enabled.
	 *
	 * @param object $entry The entry that was just created.
	 * @param object $form  The current form.
	 */
	public function anonymous_submission( $entry, $form ) {
		if ( array_key_exists( 'wsuwp_is_anonymous', $form ) && $form['wsuwp_is_anonymous'] ) {
			GFAPI::update_entry_property( $entry['id'], 'ip', 'Anonymous' );
			GFAPI::update_entry_property( $entry['id'], 'created_by', '0' );
			GFAPI::update_entry_property( $entry['id'], 'user_agent', 'Anonymous' );
		}
	}

	/**
	 * Add a tooltip for the anonymous submissions setting.
	 *
	 * @param array $tooltips An array of tooltips.
	 *
	 * @return array
	 */
	public function add_anonymous_submissions_tooltip( $tooltips ) {
		$tooltips['wsuwp_is_anonymous'] = "<h6>" . __( "Anonymous Submissions", "gravityforms" ) . "</h6>" . __( "Check this option to disable storing entry data that could potentially be used to identify people who submit this form.", "gravityforms" );
		return $tooltips;
	}

	/**
	 * Add a setting to allow anonmyous submissions.
	 *
	 * @param array  $settings An array of settings for the Form Settings UI.
	 * @param object $form     The current form object being displayed.
	 *
	 * @return array
	 */
	public function anonymous_submissions_form_setting( $settings, $form ) {
		$checked = ( rgar( $form, 'wsuwp_is_anonymous' ) ) ? 'checked="checked"' : "";
		$settings['Form Options']['wsuwp_is_anonymous'] = '
			<tr>
				<th>' . __( 'Anonymous', 'gravityforms' ) . ' ' . gform_tooltip( 'wsuwp_is_anonymous', '', true ) . '</th>
				<td><input type="checkbox" name="wsuwp_is_anonymous" id="wsuwp_is_anonymous" value="1" ' . $checked . ' /> <label for="wsuwp_is_anonymous">Enable anonymous submissions</label></td>
			</tr>';

		return $settings;
	}

	/**
	 * Save the anonymous submissions setting.
	 *
	 * @param object $form The current form object being edited.
	 *
	 * @return object
	 */
	public function save_anonymous_submissions_form_setting( $form ) {
		$form['wsuwp_is_anonymous'] = rgpost( 'wsuwp_is_anonymous' );

		return $form;
	}

	/**
	 * Remove 'ip', 'created_by', and 'user_agent' fields from the export options
	 * if the form is anonymous.
	 *
	 * @param object $form The current form.
	 *
	 * @return object
	 */
	public function remove_export_fields( $form ) {
		if ( array_key_exists( 'wsuwp_is_anonymous', $form ) && $form['wsuwp_is_anonymous'] ) {
			$fields_to_remove = array(
				'ip',
				'created_by',
				'user_agent',
			);

			foreach ( $form['fields'] as $key => $field ) {
				$field_id = is_object( $field ) ? $field->id : $field['id'];

				if ( in_array( $field_id, $fields_to_remove ) ) {
					unset ( $form['fields'][ $key ] );
				}
			}
		}

		return $form;
	}
}
$wsu_extended_gravity_forms = new WSU_Extended_Gravity_Forms();
