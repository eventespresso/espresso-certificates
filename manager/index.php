<?php

function espresso_certificate_config_mnu() {
    global $wpdb, $current_user, $espresso_premium;
    ?>


    <div class="wrap">
        <div id="icon-options-event" class="icon32"> </div>
        <h2><?php echo _e('Manage Certificate Templates', 'event_espresso') ?>
            <?php
            if (!isset($_REQUEST['action']) || ($_REQUEST['action'] != 'edit' && $_REQUEST['action'] != 'add_new_certificate')) {
                echo '<a href="admin.php?page=event_certificates&amp;action=add_new_certificate" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New', 'event_espresso') . '</a>';
            }
            ?>
        </h2>
        <div id="poststuff" class="metabox-holder has-right-sidebar">
            <?php event_espresso_display_right_column(); ?>
            <div id="post-body">
                <div id="post-body-content">


                    <?php
                    if (!empty($_POST['delete_certificate'])) {
                        if (is_array($_POST['checkbox'])) {
                            while (list($key, $value) = each($_POST['checkbox'])):
                                $del_id = $key;
                                //Delete certificate data
                                $sql = "DELETE FROM " . EVENTS_CERTIFICATE_TEMPLATES . " WHERE id='$del_id'";
                                $wpdb->query($sql);
                            endwhile;
                        }
                        ?>
                        <div id="message" class="updated fade">
                            <p><strong>
                                    <?php _e('Certificate Templates have been successfully deleted.', 'event_espresso'); ?>
                                </strong></p>
                        </div>
                    <?php } ?>
                    <?php
                    if (isset($_REQUEST['action'])) {
                        switch ($_REQUEST['action']) {
                            case 'update_certificate':
                                require_once("update_certificate.php");
								update_event_certificate();
                                break;
                            case 'add':
								require_once("add_to_db.php");
                                add_certificate_to_db();
                                break;
                            case 'add_new_certificate':
								require_once("add_new.php");
                                add_new_event_certificate();
                                break;
                            case 'edit_certificate':
								require_once("edit_certificate.php");
                                edit_event_certificate();
                                break;
                        }
                    }
                    ?>

                    <p><?php _e('Create customized certificate template for use in multiple events.', 'event_espresso'); ?></p>
                    <form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
                        <table id="table" class="widefat manage-certificates">
                            <thead>
                                <tr>
                                    <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:3.5%;"><input type="checkbox"></th>
                                    <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:3.5%;" scope="col" title="Click to Sort"><?php _e('ID', 'event_espresso'); ?></th>
                                    <th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:60%;"><?php _e('Name', 'event_espresso'); ?></th>
                                    <?php  if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { ?>
                                        <th class="manage-column column-creator" id="creator" scope="col" title="Click to Sort" style="width:10%;"><?php _e('Creator', 'event_espresso'); ?></th>
                                    <?php } ?>
                                    <th class="manage-column column-title" id="action" scope="col" title="Click to Sort" style="width:30%;"><?php _e('Action', 'event_espresso'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM " . EVENTS_CERTIFICATE_TEMPLATES . " e";
                                if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager' || espresso_member_data('role') == 'espresso_group_admin')) {
                                    $sql .= " JOIN $wpdb->users u on u.ID = e.wp_user WHERE e.wp_user = " . $current_user->ID;
                                }
                                $wpdb->query($sql);
                                if ($wpdb->num_rows > 0) {
                                    $results = $wpdb->get_results($sql . " ORDER BY e.id ASC");
                                    foreach ($results as $result) {
                                        $certificate_id = $result->id;
                                        $certificate_name = stripslashes($result->certificate_name);
                                        $certificate_content = stripslashes($result->certificate_content);
                                        $wp_user = $result->wp_user;
                                        ?>
                                        <tr>
<td><input name="checkbox[<?php echo $certificate_id ?>]" type="checkbox"  title="Delete <?php echo stripslashes($certificate_name) ?>"></td>
                                            <td><?php echo $certificate_id ?></td>
                                             <td class="post-title page-title column-title"><strong><a href="admin.php?page=event_certificates&action=edit_certificate&id=<?php echo $certificate_id ?>"><?php echo $certificate_name?></a></strong>
              <div class="row-actions"><span class="edit"><a href="admin.php?page=event_certificates&action=edit_certificate&id=<?php echo $certificate_id ?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete" href="admin.php?page=event_certificates&action=delete_certificate&id=<?php echo $certificate_id?>"><?php _e('Delete', 'event_espresso'); ?></a></span></div>
              </td>                                            <?php if (function_exists('espresso_user_meta') && espresso_is_admin() == true) { ?>
                                                <td><?php echo espresso_user_meta($wp_user, 'user_firstname') != '' ? espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname') : espresso_user_meta($wp_user, 'display_name'); ?></td>
                                            <?php } ?>
                                            <td><a href="admin.php?page=event_certificates&action=edit_certificate&id=<?php echo $certificate_id ?>">
                                                    <?php _e('Edit Certificate Template', 'event_espresso'); ?>
                                                </a></td>
                                        </tr>
                                    <?php }
                                } ?>
                            </tbody>
                        </table>
                        <p>

                            <input type="checkbox" name="sAll" onclick="selectAll(this)" />
                            <strong>
                                <?php _e('Check All', 'event_espresso'); ?>
                            </strong>
                            <input name="delete_certificate" type="submit" class="button-secondary" id="delete_certificate" value="<?php _e('Delete Selected', 'event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();"> <?php echo '<a href="admin.php?page=event_certificates&amp;action=add_new_certificate" style="margin-left:5px"class="button-primary">' . __('Add New', 'event_espresso') . '</a>';?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {

            /* show the table data */
            var mytable = $('#table').dataTable( {
                "bStateSave": true,
                "sPaginationType": "full_numbers",

                "oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
                    "sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" },
                "aoColumns": [
                    { "bSortable": false },
                    null,
                    null,
    				<?php echo function_exists('espresso_is_admin') && espresso_is_admin() == true ? 'null,' : ''; ?>
                    null

                ]

            } );

        } );
    </script>

    <?php
    //echo event_espresso_custom_certificate_info();
}
