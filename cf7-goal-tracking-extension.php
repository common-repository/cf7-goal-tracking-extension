<?php
/**
 * Plugin Name: Contact Form 7 Goal Tracking Extension
 * Description: The Contact Form 7 Goal Tracking Extension enable users to add redirects or on-submit goals when the form is submitted.
 * Version: 1.2.1
 * Author: Shounak Gupte
 * Author URI: http://www.shounakgupte.com
 * License: GPLv3
 */

define( 'CF7_SUBMIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CF7_SUBMIT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once (CF7_SUBMIT_PLUGIN_PATH . '/admin/admin-page.php');

/**
 * Verify CF7 dependencies.
 */
function cf7_submit_process_admin_notice() {
    // Verify that CF7 is active and updated to the required version (currently 3.9.0)
    if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
        $wpcf7_path = plugin_dir_path( dirname(__FILE__) ) . 'contact-form-7/wp-contact-form-7.php';
        $wpcf7_plugin_data = get_plugin_data( $wpcf7_path, false, false);
        $wpcf7_version = (int)preg_replace('/[.]/', '', $wpcf7_plugin_data['Version']);
        // CF7 drops the ending ".0" for new major releases (e.g. Version 4.0 instead of 4.0.0...which would make the above version "40")
        // We need to make sure this value has a digit in the 100s place.
        if ( $wpcf7_version < 100 ) {
            $wpcf7_version = $wpcf7_version * 10;
        }
        // If CF7 version is < 4.2.0
        if ( $wpcf7_version < 420 ) {
            echo '<div class="error"><p><strong>Warning: </strong>Contact Form 7 Goal Tracking Extension requires that you have the latest version of Contact Form 7 installed. Please upgrade now.</p></div>';
        }
    }
    // If it's not installed and activated, throw an error
    else {
        echo '<div class="error"><p>Contact Form 7 is not activated. The Contact Form 7 Plugin must be installed and activated before you can use Contact Form 7 Goal Tracking Extension.</p></div>';
    }
}
add_action( 'admin_notices', 'cf7_submit_process_admin_notice' );

/**
 * Adds a tab to the editor on the form edit page. 
 *
 * CF7 >= 4.2
 */
function cf7_success_add_page_panels($panels) {
    $panels['submit_process'] = array( 'title' => 'On Submit Options', 'callback' => 'cf7_submit_process_panel_meta' );
    return $panels;
}
add_action( 'wpcf7_editor_panels', 'cf7_success_add_page_panels' );


// Create the panel inputs (CF7 >= 4.2)
function cf7_submit_process_panel_meta( $post ) {
    wp_nonce_field( 'cf7_submit_process_metaboxes', 'cf7_submit_process_metaboxes_nonce' );

    $cf7_submit_process_redirect = get_post_meta( $post->id(), '_cf7_submit_redirect_page_id', true );
	$cf7_submit_enable = get_post_meta( $post->id(), '_cf7_submit_enable', true );

	$cf7_eventCategory = get_post_meta( $post->id(), '_cf7_submit_eventCategory', true );
	$cf7_eventAction = get_post_meta( $post->id(), '_cf7_submit_eventAction', true );
	$cf7_eventLabel = get_post_meta( $post->id(), '_cf7_submit_eventLabel', true );
	$cf7_eventValue = get_post_meta( $post->id(), '_cf7_submit_eventValue', true );

	if ($cf7_submit_enable=='enable-tracking') {
		if (empty($cf7_eventCategory) || empty($cf7_eventAction)){
		    ?>
            <?php if (empty($cf7_eventCategory) && empty($cf7_eventAction)):?>
                <div class="config-error"><span class="dashicons dashicons-warning"></span> 2 configurations error detected in this tab panel</div>
			<?php else:?>
                <div class="config-error"><span class="dashicons dashicons-warning"></span> 1 configuration error detected in this tab panel</div>
			<?php endif;?>

            <?php
        }
	}

    // The meta box content
    $dropdown_options = array (
            'echo' => 0,
            'name' => 'cf7-redirect-page-id', 
            'show_option_none' => '--', 
            'option_none_value' => '0',
            'selected' => $cf7_submit_process_redirect
        );
	?>
    <style>
        #submit_process .event-redirect,#submit_process .event-tracking{
            display: none;
        }
        #submit_process .event-redirect.active,#submit_process .event-tracking.active{
            display: block;
        }
    </style>
	<p>Click <a href="<?php menu_page_url('cf7-submit-process-page.php'); ?>">here</a> for our Goal creation tutorial</p>
	
	<input id="redirect" type="radio" name="cf7-submit-enable" value="enable-redirect" <?php if ($cf7_submit_enable=='enable-redirect') echo 'checked';?> /> <label for="redirect">Enable Redirect.</label><br>
	<input id="on-submit" type="radio" name="cf7-submit-enable" value="enable-tracking" <?php if ($cf7_submit_enable=='enable-tracking') echo 'checked';?> /> <label for="on-submit">Enable Tracking.</label><br>
    <fieldset class="event-redirect <?php if ($cf7_submit_enable=='enable-redirect') echo 'active'?>">
    <?php
    echo '<h3>Redirect Settings</h3>
          <fieldset>
            <legend>Select a page to redirect to on successful form submission.</legend>' .
            wp_dropdown_pages( $dropdown_options ) .
         '</fieldset>';
   ?>
    </fieldset>
    <fieldset class="event-tracking <?php if ($cf7_submit_enable=='enable-tracking') echo 'active'?>">
        <h3>Event Tracking Settings</h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="eventCategory">Event Category(Required) </label>
                </th>

                <td>
                    <input  id='eventCategory' name="cf7-event-category" placeholder='Example: Form' type='text' value="<?php echo $cf7_eventCategory;?>" />
                <?php
                if ($cf7_submit_enable=='enable-tracking') {
		            if (empty($cf7_eventCategory)){
		                ?>
                        <ul role="alert" class="config-error"><li>This field is required</li></ul>
                        <?php
	                }
                }
                ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventAction">Event Action(Required)</label>
                </th>

                <td>
                    <input class="large-text code" size="70" id='eventAction' name="cf7-event-action" placeholder='Example: Submit' type='text' value="<?php echo $cf7_eventAction;?>" />
	                <?php
	                if ($cf7_submit_enable=='enable-tracking') {
		                if (empty($cf7_eventAction)){
			                ?>
                            <ul role="alert" class="config-error"><li>This field is required</li></ul>
			                <?php
		                }
	                }
	                ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventLabel">Event Label</label>
                </th>
                <td>
                    <input class="large-text code" size="70" id='eventLabel' name="cf7-event-label" placeholder='Example: Contact' type='text' value="<?php echo $cf7_eventLabel;?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventValue">Event Value</label>
                </th>
                <td>
                    <input class="large-text code" size="70" id='eventValue' name="cf7-event-value" placeholder='Example: 10' type='number' value="<?php echo $cf7_eventValue;?>"/>
                </td>
            </tr>
        </table>

    </fieldset>
    <script>
        var value_submit = null;
        jQuery("input[name='cf7-submit-enable']").click(function() {
            value_submit = this.value;
            if (value_submit==='enable-redirect'){
                jQuery('.event-redirect').addClass('active')
                jQuery('.event-tracking').removeClass('active')
            }
            if (value_submit==='enable-tracking'){
                jQuery('.event-tracking').addClass('active')
                jQuery('.event-redirect').removeClass('active')
            }
        });

    </script>

<?php
}

// Store Success Page Info
function cf7_submit_process_save_contact_form( $contact_form ) {
    $contact_form_id = $contact_form->id();

    if ( !isset( $_POST ) || empty( $_POST )) {
        return;
    }
    else {
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['cf7_submit_process_metaboxes_nonce'], 'cf7_submit_process_metaboxes' ) ) {
            return;
        }
        // Update the stored value
        update_post_meta( $contact_form_id, '_cf7_submit_redirect_page_id', $_POST['cf7-redirect-page-id'] );
	    update_post_meta( $contact_form_id, '_cf7_submit_enable', $_POST['cf7-submit-enable'] );
	    update_post_meta( $contact_form_id, '_cf7_submit_eventCategory', $_POST['cf7-event-category'] );
	    update_post_meta( $contact_form_id, '_cf7_submit_eventAction', $_POST['cf7-event-action'] );
	    update_post_meta( $contact_form_id, '_cf7_submit_eventLabel', $_POST['cf7-event-label'] );
	    update_post_meta( $contact_form_id, '_cf7_submit_eventValue', $_POST['cf7-event-value'] );
    }
}
add_action( 'wpcf7_after_save', 'cf7_submit_process_save_contact_form' );


/**
 * Copy Redirect page key and assign it to duplicate form
 */
function cf7_submit_process_after_form_create( $contact_form ){
    $contact_form_id = $contact_form->id();

    // Get the old form ID
    if ( !empty( $_REQUEST['post'] ) && !empty( $_REQUEST['_wpnonce'] ) ) {
        $old_form_id = get_post_meta( $_REQUEST['post'], '_cf7_submit_redirect_page_id', true );
	    $old_eventCategory = get_post_meta( $_REQUEST['post'], '_cf7_submit_eventCategory', true );
	    $old_eventAction = get_post_meta( $_REQUEST['post'], '_cf7_submit_eventAction', true );
	    $old_eventLabel = get_post_meta( $_REQUEST['post'], '_cf7_submit_eventLabel', true );
	    $old_eventValue = get_post_meta( $_REQUEST['post'], '_cf7_submit_eventValue', true );

    }
    // Update the duplicated form
    update_post_meta( $contact_form_id, '_cf7_submit_process_key', $old_form_id );
	update_post_meta( $contact_form_id, '_cf7_submit_eventCategory', $old_eventCategory );
	update_post_meta( $contact_form_id, '_cf7_submit_eventAction', $old_eventAction );
	update_post_meta( $contact_form_id, '_cf7_submit_eventLabel', $old_eventLabel );
	update_post_meta( $contact_form_id, '_cf7_submit_eventValue', $old_eventValue );

}
add_action( 'wpcf7_after_create', 'cf7_submit_process_after_form_create' );


/**
 * Redirect the user, after a successful email is sent
 */

function cf7_submit_process_form_submitted(){
	$args = array( 'post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1 );
	$loop = new WP_Query( $args );?>
    <script>
        document.addEventListener( 'wpcf7submit', function( event ) {
    <?php
	while ( $loop->have_posts() ) : $loop->the_post();
		$contact_form_id  = get_the_ID();
		$success_page = get_post_meta( $contact_form_id, '_cf7_submit_redirect_page_id', true );
		$cf7_submit_enable = get_post_meta( $contact_form_id, '_cf7_submit_enable', true );
    	$cf7_eventCategory = get_post_meta( $contact_form_id, '_cf7_submit_eventCategory', true );
    	$cf7_eventAction = get_post_meta( $contact_form_id, '_cf7_submit_eventAction', true );
    	$cf7_eventLabel = get_post_meta($contact_form_id, '_cf7_submit_eventLabel', true );
    	$cf7_eventValue = get_post_meta( $contact_form_id, '_cf7_submit_eventValue', true );
    	?>
            <?php if (!empty($cf7_submit_enable)):?>
            if ( <?php echo $contact_form_id;?> == event.detail.contactFormId ) {
                <?php if($cf7_submit_enable=='enable-tracking'):?>
		            <?php if (!empty($cf7_eventCategory) && !empty($cf7_eventAction)):?>
                ga('send', 'event', '<?php echo $cf7_eventCategory;?>', '<?php echo $cf7_eventAction;?>'<?php if(!empty($cf7_eventLabel)) echo ", '".$cf7_eventLabel."'"?><?php if(!empty($cf7_eventValue)) echo ", '".$cf7_eventValue."'"?>);
                    <?php endif;?>
		        <?php endif;?>
                <?php if($cf7_submit_enable=='enable-redirect'):?>
                location = '<?php echo get_permalink( $success_page ); ?>';
                <?php endif;?>
            }
            <?php endif;?>
	<?php endwhile; ?>
        }, false );
    </script>
<?php
}
add_action( 'wp_footer', 'cf7_submit_process_form_submitted' );