<?php

/**
 * Snippet Name: 	List Entry Notes in Single Entry View
 * Description:		List non-WordPress entry notes in Single Entry View, using Custom Content Field. Modifies GravityView mod "List Entry Notes in View".
 			Entry notes list is filtered by when entry was last updated by user. Hides notes older than last update.
 * Version: 		1.0
 * Author: 		Cristopher Nix; GravityView
 */

//To add the entry notes to a view, add a Custom Content field with the content {entry_id}
add_filter( 'gravityview/fields/custom/content_after', 'render_entry_notes' );

function render_entry_notes ( $content ) {
	
	//Replace VIEW_ID with the view ID of the view where the entry notes will be displayed.
	if ( function_exists( 'gravityview_get_view_id' ) && VIEW_ID != gravityview_get_view_id( ) ) {
		
		return $content;
		
	}
	
	if ( class_exists( 'GravityView_Entry_Notes' ) ) {
		
		//Get the entry notes of the associated entry ID.
		$notes = GravityView_Entry_Notes::get_notes( $content );
		
		if( empty( $notes ) ) {
			
			return '';

		}
		
		/**
		 * Get entry data based off the entry ID.
		 * $owner_update_date is set to the value of a custom Last Updated value separate from the database Last Update value.
		 */
		
		$entry_id = $content;
		$entry = GFAPI::get_entry( $entry_id );
		
		//Get the timestamp of when the entry was last updated by its creator.
		$owner_update_date = rgar( $entry, '63' );
		
		//Clear $content
		$content = '';
		
		//Find the most-recent entry note; return most recent note.
		$newest_note = max( $notes );
		$note_content = array_search( $newest_note, $notes );
		
		/** 
		 * Loop through $notes array. If user_id == 0, remove from array and check next note.
		 * If there are no user-created notes, return empty $note_content string.
		 */
		
		while ( $notes[ $note_content ]->user_id == '0' ) {
			
			$note_content--;
			
			if( $note_content==-1 ) {
				
				empty($note_content);
				
				break;
			
			}
		
		}
		
		//Run if array contains data.
		if ($note_content >= '0' ) {
			
			//print each note in array as a separate segment of text.
			foreach ( $notes as $note ) {
				
				//if user_id == 0, skip note and continue.
				if ($note->user_id == '0') {
					
					continue;
				
				}
				
				//format note creation date into a readable format. Formatted to match $owner_update_date.
				$note_date = GFCommon::format_date( $note->date_created, false, 'Y-m-d H:i', false );
				
				//If note was created after last updated by user, display note. Otherwise skip entry note.
				if ($note_date > $owner_update_date) {
					
					$note_username = $note->user_name;
					$note_text = $note->value;
				
					echo "<p><b>Added on $note_date by $note_username</b><br>";
					echo $note_text . '</p>';
				
				} else {
					
					//return nothing if there are no recent notes.
					echo "";
				}
			}
		
		}
		
	}
	
	//Return modified field content in string $content.
	return $content;
	
}
