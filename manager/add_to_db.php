<?php
function add_certificate_to_db(){
	global $wpdb,$current_user;
	if ( $_REQUEST['action'] == 'add' ){
		$certificate_name= $_REQUEST['certificate_name'];
		$certificate_file= $_REQUEST['certificate_file'];
		$certificate_logo_url= $_REQUEST['upload_image'];
		$certificate_content= $_REQUEST['certificate_content']; 	
        
		if (!function_exists('espresso_member_data'))
			$current_user->ID = 1;

		$sql=array('certificate_name'=>$certificate_name, 'certificate_content'=>$certificate_content, 'certificate_file'=>$certificate_file, 'certificate_logo_url'=>$certificate_logo_url, 'wp_user'=>$current_user->ID);
		
		$sql_data = array('%s','%s','%s','%s');
	
		if ($wpdb->insert( EVENTS_CERTIFICATE_TEMPLATES, $sql, $sql_data )){?>
		<div id="message" class="updated fade"><p><strong><?php _e('The certificate', 'event_espresso'); ?> <?php echo htmlentities2($_REQUEST['certificate_name']);?> <?php _e('has been added.', 'event_espresso'); ?></strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong><?php _e('The certificate', 'event_espresso'); ?> <?php echo htmlentities2($_REQUEST['certificate_name']);?> <?php _e('was not saved.', 'event_espresso'); ?> <?php print mysql_error() ?>.</strong></p></div>

<?php
		}
	}
}