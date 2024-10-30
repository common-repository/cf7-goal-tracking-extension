<?php

/** Step 1. */
function cf7_submit_process_menu() {
	//add_options_page( 'CF7 Submit Process Options', 'CF7 Submit Process Options', 'manage_options', 'cf7-submit-process-page', 'cf7_submit_process_options_page' );
	add_menu_page( 'CF7 Goal Tracking Extension', 'CF7 Goal Tracking Extension Options', 'manage_options', 'cf7-submit-process-page.php', 'cf7_submit_process_options_page', 'dashicons-exerpt-view', 30  );
}

/** Step 2 (from text above). */
add_action( 'admin_menu', 'cf7_submit_process_menu' );


/** Step 3. */
function cf7_submit_process_options_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap cf7-goal">';
	include (CF7_SUBMIT_PLUGIN_PATH.'admin/text.php');
	echo '</div>';
}