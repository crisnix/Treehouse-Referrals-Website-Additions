<?php
/**
* Snippet Name:   Reassign Trashed Entries to Specific User
* Description:    When "Delete Entry" is clicked using GravityView. Assign entry to a specific user instead. Modifies the GravityView "'Delete Entry' Trash Mode" Mod.
* Version:        1.0
* Author:         Cristopher Nix; GravityView
*/

//Call the trash entry functionality in GravityView
add_filter( 'gravityview/delete-entry/mode', '_gravityview_delete_entry_mode_trash' );

/**
 * Return trash
 *
 * @param string $mode "delete" by default
 *
 * @return string "trash"
 */

function _gravityview_delete_entry_mode_trash( $mode = 'delete' ) {
	
	return 'trash';
}

//Change the actions that happen when an entry is trashed.
add_filter( 'gravityview/delete-entry/trashed', 'change_entry_creator_trashed' );

function change_entry_creator_trashed ( $entry_id ) {
  
	//Call the WordPress database
	global $wpdb;
	
	$table = GFFormsModel::get_entry_table_name();
	
  	//Get user meta of the user removing entry from their list.
	$user = wp_get_current_user();
	
	$old_creator_id = $wpdb->get_var( $wpdb->prepare( "SELECT created_by from $table where id = %d", $entry_id ) );
	
	$old_creator = get_userdata( $old_creator_id );
  
 	 //Add a note to the entry that the entry was removed from their list.
	GFFormsModel::add_note( $entry_id, $user->ID, $user->user_login, $old_creator->display_name . ' removed entry from their list of referrals.');
  
  	//Change USER_ID to the ID of the WordPress user you want the entries to be assigned to.
	GFAPI::update_entry_property( $entry_id, 'created_by', USER_ID );
  
  	//Untrash the entry and set its status to 'Active'.
	$trashed = GFAPI::update_entry_property( $entry_id, 'status', 'active', false, true );
  
  	//return $trashed to run 'Trashed' GravityView delete entry filter.
	return $trashed;
}
