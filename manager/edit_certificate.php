<?php
function edit_event_certificate(){
	global $wpdb;
	// read our style dir and build an array of files
	if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "certificates/templates/index.html")) {
		$dhandle = opendir(EVENT_ESPRESSO_TEMPLATE_DIR . 'certificates/templates/');//If the template files have been moved to the uplaods folder
	} else {
		$dhandle = opendir(ESPRESSO_CERTIFICATE_FULL_PATH . 'templates/');
	}
	$files = array();
	
	if ($dhandle) { //if we managed to open the directory
		// loop through all of the files
		while (false !== ($fname = readdir($dhandle))) {
			// if the file is not this file, and does not start with a '.' or '..',
			// then store it for later display
			if ( ($fname != '.') && ($fname != '..') && ($fname != '.svn') && ($fname != basename($_SERVER['PHP_SELF'])) ) {
				// store the filename
				$files[] = $fname;
			}
		}
		// close the directory
		closedir($dhandle);
	}
	$id=$_REQUEST['id'];
	$results = $wpdb->get_results("SELECT * FROM ". EVENTS_CERTIFICATE_TEMPLATES ." WHERE id =".$id);
	foreach ($results as $result){
		$certificate_id= $result->id;
		$certificate_name=stripslashes_deep($result->certificate_name);
		$certificate_file=stripslashes_deep($result->certificate_file);
		$certificate_logo_url=stripslashes_deep($result->certificate_logo_url);
		$certificate_content=stripslashes_deep($result->certificate_content);
	}
	?>

<div class="metabox-holder">
  <div class="postbox">
    <h3>
      <?php _e('Edit Certificate Template:','event_espresso'); ?>
      <?php echo stripslashes($certificate_name) ?></h3>
    <div class="inside">
      <form id="add-edit-new-event-certificate" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
        <input type="hidden" name="certificate_id" value="<?php echo $certificate_id; ?>">
        <input type="hidden" name="action" value="update_certificate">
        <ul>
          <li>
            <label>
              <?php _e('Certificate Name:','event_espresso'); ?>
            </label>
            <input type="text" name="certificate_name" size="25" value="<?php echo stripslashes($certificate_name);?>" />
          </li>
          <li>
            <label for="base-certificate-select" <?php echo $styled ?>>
              <?php _e('Select Base Template', 'event_espresso');  ?>
            </label>
            <select id="base-certificate-select" class="wide" <?php echo $disabled ?> name="certificate_file">
              <option <?php espresso_certificate_is_selected($fname,$certificate_file) ?> value="basic.html">
              <?php _e('Default Template - Basic', 'event_espresso'); ?>
              </option>
              <?php foreach( $files as $fname ) { ?>
              <option <?php espresso_certificate_is_selected($fname,$certificate_file) ?> value="<?php echo $fname ?>"><?php echo $fname; ?></option>
              <?php } ?>
            </select>
          </li><li><div id="certificate-logo-image">
		  <?php
									 
		if(!empty($certificate_logo_url)){ 
			$certificate_logo = $certificate_logo_url;
		} else {
			$certificate_logo = '';
		}
		?>
		  <?php // var_dump($event_meta['event_thumbnail_url']); ?>
		  <label for="upload_image">
			<?php _e('Add a Logo', 'event_espresso'); ?>
		  </label>
		  <input id="upload_image" type="hidden" size="36" name="upload_image" value="<?php echo $certificate_logo ?>" />
		  <input id="upload_image_button" type="button" value="Upload Image" />
		  <?php if($certificate_logo){ ?>
		  <p class="certificate-logo-thumb"><img src="<?php echo $certificate_logo ?>" alt="" /></p>
		  <?php } ?>
		</div></li>
          <li>
            <div id="descriptiondivrich" class="postarea">
              <label for="certificate_content">
                <?php _e('Certificate Description/Instructions','event_espresso'); ?>
              </label>
              <div class="postbox">
                <?php the_editor(stripslashes_deep($certificate_content), $id = 'certificate_content', $prev_id = 'title', $media_buttons = true, $tab_index = 3);?>
                <table id="manage-event-certificate-form" cellspacing="0">
                  <tbody>
                    <tr>
                      <td class="aer-word-count"></td>
                      <td class="autosave-info"><span> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_certificate_info">
                        <?php _e('View Custom Certificate Tags', 'event_espresso'); ?>
                        </a> | <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_certificate_example">
                        <?php _e('Certificate Example','event_espresso'); ?></a> 
                        | <a class="thickbox" href="<?php echo ESPRESSO_CERTIFICATE_FULL_URL.'templates/'.$certificate_file; ?>?TB_iframe=true&height=200&width=630">
                        <?php _e('Preview','event_espresso'); ?>
                        </a> </span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </li>
          <li>
            <p>
              <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Certificate'); ?>" id="update_certificate" />
            </p>
          </li>
        </ul>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" charset="utf-8">
	//<![CDATA[
 	jQuery(document).ready(function() {    
		var header_clicked = false; 
		jQuery('#upload_image_button').click(function() {
		formfield = jQuery('#upload_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=1');
		jQuery('p.certificate-logo-thumb').addClass('old');
		header_clicked = true;
	    return false;
	   });
		window.original_send_to_editor = window.send_to_editor;
					 
		window.send_to_editor = function(html) {
			if(header_clicked) {
				imgurl = jQuery('img',html).attr('src');
				jQuery('#' + formfield).val(imgurl);
				jQuery('#certificate-logo-image').append("<p><img src='"+imgurl+"' alt='' /></p>");
				header_clicked = false;
				tb_remove();
				} else {
					window.original_send_to_editor(html);
				}
		}
	});

	//]]>
</script>
<?php 
 //espresso_tiny_mce();
}
