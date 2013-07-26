<?php

function espresso_certificate_is_selected($name, $selected='') {
	   $input_item = $name;
		$option_selections = array($selected);
	   if (!in_array( $input_item, $option_selections )  )
	   return false;
	   else
	   echo  'selected="selected"';
	   return; 
	}
function espresso_certificate_content($id) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM " . EVENTS_CERTIFICATE_TEMPLATES . " WHERE id =" . $id);
    foreach ($results as $result) {
        $certificate_id = $result->id;
        $certificate_name = stripslashes_deep($result->certificate_name);
        $certificate_content = stripslashes_deep($result->certificate_content);
    }
    $certificate_data = array('id' => $id, 'certificate_name' => $certificate_name,'certificate_content' => $certificate_content);
    return $certificate_data;
}

//Creates the certificate pdf
function espresso_certificate_launch($attendee_id=0, $registration_id=0){
	global $wpdb, $org_options, $certificate_options;
	$data = new stdClass;
	
	//Make sure we have attendee data
	if ($attendee_id==0 || $registration_id==0)
		return;
	
	//Get the event record
    $sql = "SELECT ed.*, ec.certificate_file, ec.certificate_content, ec.certificate_logo_url ";
    isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $sql .= ", v.id venue_id, v.name venue_name, v.address venue_address, v.city venue_city, v.state venue_state, v.zip venue_zip, v.country venue_country, v.meta venue_meta " : '';
    $sql .= " FROM " . EVENTS_DETAIL_TABLE . " ed ";
    isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = ed.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id " : '';
    $sql .= " JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON ea.event_id=ed.id ";
	$sql .= " LEFT JOIN " . EVENTS_CERTIFICATE_TEMPLATES . " ec ON ec.id=ed.certificate_id ";
    $sql .= " WHERE ea.id = '" . $attendee_id . "' AND ea.registration_id = '" . $registration_id . "' ";
	//echo $sql;
    $data->event = $wpdb->get_row($sql, OBJECT);
	
	//Get the attendee record
    $sql = "SELECT ea.* FROM " . EVENTS_ATTENDEE_TABLE . " ea WHERE ea.id = '" . $attendee_id . "' ";
    $data->attendee = $wpdb->get_row($sql, OBJECT);
	
	//Get the primary/first attendee
	$data->primary_attendee = espresso_is_primary_attendee($data->attendee->id) == true ? true : false;
	
	//unserialize the event meta
	$data->event->event_meta = unserialize($data->event->event_meta);
	
	//Get the registration date
	$data->attendee->registration_date = $data->attendee->date;
	
	//Get the HTML file
	$data->event->certificate_file = (!empty($data->event->certificate_file) && $data->event->certificate_file > '0') ? $data->event->certificate_file : 'basic.html';
	//echo $data->event->certificate_file;
	
	//Venue information
    if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
		$data->event->venue_id = !empty($data->event->venue_id)?$data->event->venue_id:'';
		$data->event->venue_name = !empty($data->event->venue_name)?$data->event->venue_name:'';
		$data->event->address = !empty($data->event->venue_address)?$data->event->venue_address:'';
		$data->event->address2 = !empty($data->event->venue_address2)?$data->event->venue_address2:'';
		$data->event->city = !empty($data->event->venue_city)?$data->event->venue_city:'';
		$data->event->state = !empty($data->event->venue_state)?$data->event->venue_state:'';
		$data->event->zip = !empty($data->event->venue_zip)?$data->event->venue_zip:'';
		$data->event->country = !empty($data->event->venue_country)?$data->event->venue_country:'';
		$data->event->venue_meta = !empty($data->event->venue_meta)?unserialize($data->event->venue_meta):'';
    } else {
        $data->event->venue_name = !empty($data->event->venue_title)?$data->event->venue_title:'';
    }
		
	//Create the logo
	$data->event->certificate_logo_url = empty($data->event->certificate_logo_url) ? $org_options['default_logo_url']: $data->event->certificate_logo_url;
	$image_size = getimagesize($data->event->certificate_logo_url);
	$data->event->certificate_logo_image = '<img src="'.$data->event->certificate_logo_url.'" '.$image_size[3].' alt="logo" /> ';
	
	//Build the certificate name
	$certificate_name = sanitize_title_with_dashes($data->attendee->id.' '.$data->attendee->fname.' '.$data->attendee->lname);
	//Get the HTML as an object
    ob_start();
	if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "certificates/templates/index.html")) {
		require_once(EVENT_ESPRESSO_TEMPLATE_DIR . 'certificates/templates/'.$data->event->certificate_file);
	} else {
		require_once('templates/'.$data->event->certificate_file);
	}
	$content = ob_get_clean();
	$content = espresso_replace_certificate_shortcodes($content, $data);
	
	//Check if debugging or mobile is set
	if ( (isset($_REQUEST['debug']) && $_REQUEST['debug']==true) || stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false ){
		echo $content; 
		exit(0);
	}
	
	//Create the PDF
	define('DOMPDF_ENABLE_REMOTE',true);
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'tpc/dompdf/dompdf_config.inc.php');
	$dompdf = new DOMPDF();
	$dompdf->load_html($content);
	//$dompdf->set_paper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream($certificate_name.".pdf", array("Attachment" => false));
	exit(0);
	
}

function espresso_replace_certificate_shortcodes($content, $data) {
	global $wpdb, $org_options;
    $SearchValues = array(
		//Attendee/Event Information
        "[att_id]",
		"[qr_code]",
		"[gravatar]",
		"[event_id]",
        "[event_identifier]",
        "[registration_id]",
		"[registration_date]",
        "[fname]",
        "[lname]",
        "[event_name]",
        "[description]",
        "[event_link]",
        "[event_url]",
        
        //Payment details
        "[cost]",
        "[certificate_type]",
        
		//Organization details
        "[company]",
        "[co_add1]",
        "[co_add2]",
        "[co_city]",
        "[co_state]",
        "[co_zip]",

		//Dates
        "[start_date]",
        "[start_time]",
        "[end_date]",
        "[end_time]",
		
		//Certificate data
		"[certificate_content]",
		
		//Logo
		"[certificate_logo_url]",
		"[certificate_logo_image]",
		
		//Venue information
		"[venue_title]",
		"[venue_address]",
		"[venue_address2]",
		"[venue_city]",
		"[venue_state]",
		"[venue_zip]",
		"[venue_country]",
		"[venue_phone]",
		"[venue_description]",
		
        "[venue_website]",
        "[venue_image]",
        
		"[google_map_image]",
        "[google_map_link]",
    );

    $ReplaceValues = array(
		//Attendee/Event Information
		$data->attendee->id,
		$data->qr_code,
		$data->gravatar,
        $data->attendee->event_id,
        $data->event->event_identifier,
        $data->attendee->registration_id,
		event_date_display($data->attendee->registration_date),
        stripslashes_deep($data->attendee->fname),
        stripslashes_deep($data->attendee->lname),
        stripslashes_deep($data->event->event_name),
        stripslashes_deep($data->event->event_desc),
       	$data->event_link,
        $data->event_url,
        
		//Payment details
        $org_options['currency_symbol'] .' '. espresso_attendee_price(array('registration_id' => $data->attendee->registration_id, 'session_total' => true)),
        $data->attendee->price_option,
        
		//Organization details
        stripslashes_deep($org_options['organization']),
        $org_options['organization_street1'],
        $org_options['organization_street2'],
        $org_options['organization_city'],
        $org_options['organization_state'],
        $org_options['organization_zip'],
        
		//Dates
        event_date_display($data->attendee->start_date),
        event_date_display($data->attendee->event_time, get_option('time_format')),
        event_date_display($data->attendee->end_date),
        event_date_display($data->attendee->end_time, get_option('time_format')),
		
		//Certificate data
		wpautop(stripslashes_deep(html_entity_decode($data->event->certificate_content, ENT_QUOTES))),
		
		//Logo
        $data->event->certificate_logo_url,
		$data->event->certificate_logo_image, //Returns the logo wrapped in an image tag
		
		//Venue information
		$data->event->venue_name,		
		$data->event->address,
		$data->event->address2,
		$data->event->city,
		$data->event->state,
		$data->event->zip,
		$data->event->country,
		$data->event->venue_meta['phone'],
		wpautop(stripslashes_deep(html_entity_decode($data->event->venue_meta['description'], ENT_QUOTES))),
		
		$data->event->venue_meta['website'],
        $data->event->venue_meta['image'],        
		
		$data->event->google_map_image,
        $data->event->google_map_link,
    );
	
	//Get the questions and answers
	$questions = $wpdb->get_results("select qst.question as question, ans.answer as answer from ".EVENTS_ANSWER_TABLE." ans inner join ".EVENTS_QUESTION_TABLE." qst on ans.question_id = qst.id where ans.attendee_id = ".$data->attendee->id, ARRAY_A);
	//echo '<p>'.print_r($questions).'</p>';
	if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->question != NULL) {
		foreach($questions as $q){
			$k = $q['question'];
			$v = $q['answer'];
			
			//Output the question
			array_push($SearchValues,"[".'question_'.$k."]");
			array_push($ReplaceValues,$k);
			
			//Output the answer
			array_push($SearchValues,"[".'answer_'.$k."]");
			array_push($ReplaceValues,$v);
		}
	}
	
	//Get the event meta
	//echo '<p>'.print_r($data->event->event_meta).'</p>';
	if (!empty($data->event->event_meta)){
		foreach($data->event->event_meta as $k=>$v){
			array_push($SearchValues,"[".$k."]");
			array_push($ReplaceValues,stripslashes_deep($v));
		}
	}
	
	//Might use this later
	//Get the venue meta
	/*echo '<p>'.print_r($data->event->venue_meta).'</p>';
	foreach($data->event->venue_meta as $k=>$v){
		array_push($SearchValues,"[".'venue_'.strtolower($k)."]");
		array_push($ReplaceValues,stripslashes_deep($v));
	}*/
	
    return str_replace($SearchValues, $ReplaceValues, $content);
}

if ( !function_exists( 'espresso_certificate_dd' ) ){
	function espresso_certificate_dd($current_value = 0){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
		$sql = "SELECT id, certificate_name FROM " .EVENTS_CERTIFICATE_TEMPLATES;
		$sql .= " WHERE certificate_name != '' ORDER BY certificate_name ";
		//echo $sql;
		$certificates = $wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;
		//return print_r( $certificates );
		if ($num_rows > 0) {
			$field = '<select name="certificate_id" id="certificate_id">\n';
			$field .= '<option value="0">'.__('Select a Certificate', 'event_espresso').'</option>';

			foreach ($certificates as $certificate){
				$selected = $certificate->id == $current_value ? 'selected="selected"' : '';
				$field .= '<option '. $selected .' value="' . $certificate->id .'">' . $certificate->certificate_name. '</option>\n';
			}
			$field .= "</select>";
			$html = '<p>' .__('Custom Certificate:','event_espresso') . $field .'</p>';
			return $html;
		}
	}
}

function espresso_certificate_links($registration_id, $attendee_id) {
    global $wpdb;
    $sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;
    if (espresso_is_primary_attendee($attendee_id) != true) {
        $sql .= " WHERE id = '" . $attendee_id . "' ";
    } else {
        $sql .= " WHERE registration_id = '" . $registration_id . "' ";
    }
    //echo $sql;
    $attendees = $wpdb->get_results($sql);
    $certificate_link = '';
    if ($wpdb->num_rows > 0) {
        $group = $wpdb->num_rows > 1 ? '<strong>' . sprintf(__('Certificates Received (%s):', 'event_espresso'), $wpdb->num_rows) . '</strong><br />' : '';
        $break = '<br />';
        foreach ($attendees as $attendee) {
			$certificate_url = get_option('siteurl') . "/?certificate_launch=true&amp;id=" . $attendee->id . "&amp;r_id=" . $attendee->registration_id;
            $certificate_link .= '<a href="' . $certificate_url . '">' . __('Download/Print Certificate') . ' (' . $attendee->fname . ' ' . $attendee->lname . ')' . '</a>' . $break;
        }
        return '<p>' . $group . $certificate_link . '</p>';
    }
}
