<?php 
function update_event_certificate(){
	global $wpdb;
$certificate_id= $_REQUEST['certificate_id'];
		$certificate_name= $_REQUEST['certificate_name'];
		$certificate_file= $_REQUEST['certificate_file'];
		$certificate_logo_url= $_REQUEST['upload_image'];
		$certificate_content= $_REQUEST['certificate_content'];
		$sql=array('certificate_name'=>$certificate_name, 'certificate_content'=>$certificate_content, 'certificate_file'=>$certificate_file, 'certificate_logo_url'=>$certificate_logo_url); 
		
		$update_id = array('id'=> $certificate_id);
		
		$sql_data = array('%s','%s','%s','%s');
	
	if ($wpdb->update( EVENTS_CERTIFICATE_TEMPLATES, $sql, $update_id, $sql_data, array( '%d' ) )){?>
	<div id="message" class="updated fade"><p><strong><?php _e('The certificate', 'event_espresso'); ?> <?php echo stripslashes(htmlentities2($_REQUEST['certificate_name']));?> <?php _e('has been updated', 'event_espresso'); ?>.</strong></p></div>
<?php }else { ?>
	<div id="message" class="error"><p><strong><?php _e('The certificate', 'event_espresso'); ?> <?php echo stripslashes(htmlentities2($_REQUEST['certificate_name']));?> <?php _e('was not updated', 'event_espresso'); ?>. <?php print mysql_error() ?>.</strong></p></div>

<?php
	}
}