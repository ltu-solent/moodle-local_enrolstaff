<?php
require_once('../../config.php');
require_once('form.php');
	
require_login(true);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/enrolstaff/enrolstaff.php');
$PAGE->set_title('Staff Enrolment');
$PAGE->set_heading('Staff Enrolment');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('enrol-selfservice', 'local_enrolstaff'), new moodle_url('/local/enrolstaff/enrolstaff.php'));
$PAGE->navbar->add('Enrol onto courses');
global $USER;
$return = $CFG->wwwroot.'/local/enrolstaff/enrolstaff.php';	
if(ISSET($_POST['enrol_home'])){	
	redirect('/local/enrolstaff/enrolstaff.php');
}
if(ISSET($_POST['unenrol'])){	
		redirect('/local/enrolstaff/unenrolstaff.php');
}
echo $OUTPUT->header();

echo "<div class='maindiv'>";
$emaildomain = substr($USER->email, strpos($USER->email, "@") + 1);
if((($USER->department == 'academic') || ($USER->department == 'management') || ($USER->department == 'support')) && $emaildomain == 'solent.ac.uk' || (is_siteadmin())){		
	
	//Course search
	echo"<h2>" . get_string('enrol-selfservice', 'local_enrolstaff') ."</h2>";
	if(count($_POST) <= 1){		
		echo get_string('intro', 'local_enrolstaff');
						
		$sform = new search_form(); 
		if ($sform->is_cancelled()) {		
			redirect('enrolstaff.php');
		} else if ($frosform = $sform->get_data()) {
		   	  	  
		} else {	 
		  $sform->display();
		}
		
		echo get_string('unenrol-header', 'local_enrolstaff');
		echo get_string('unenrol-intro', 'local_enrolstaff');		
		
		$uform = new unenrol_button(); 
		if ($uform->is_cancelled()) {		
			
		} else if ($frouform = $uform->get_data()) {
		   	  	 
		} else {	 
		  $uform->display();
		}		
	}
	
	

	//Course results list	
	if(ISSET($_POST['search_select'])){					
		
		if($_POST['coursesearch'] != ''){
		$courses = $DB->get_records_sql("	SELECT c.idnumber, c.id, c.shortname, c.fullname, DATE_FORMAT(FROM_UNIXTIME(c.startdate), '%d-%m-%Y') as startdate 
											FROM {course} c
											JOIN mdl_course_categories cc on c.category = cc.id
											WHERE (c.shortname LIKE ?
											OR c.fullname LIKE ?)
											AND ((c.shortname  NOT LIKE 'EDU117%' OR c.fullname  NOT LIKE '%EDU117%')
											AND (c.shortname  NOT LIKE 'EDU118%' OR c.fullname  NOT LIKE '%EDU118%')
											AND (c.shortname  NOT LIKE 'EDU120%' OR c.fullname  NOT LIKE '%EDU120%')
											AND (c.shortname  NOT LIKE 'PDU022%' OR c.fullname  NOT LIKE '%PDU022%')							
											AND (c.shortname  NOT LIKE 'HHS%' OR c.fullname  NOT LIKE '%HHS%')
											AND (c.shortname  NOT LIKE 'HSW%' OR c.fullname  NOT LIKE '%HSW%')
											AND (c.shortname  NOT LIKE 'PDU%' OR c.fullname  NOT LIKE '%PDU%')
											AND c.fullname  NOT LIKE '%counselling%'
											AND c.fullname  NOT LIKE '%social work%'
											AND c.id NOT IN (328, 22679, 6432)) 
											AND (cc.name = 'Unit Pages' OR cc.name = 'Course Pages')
											ORDER BY c.shortname, c.startdate DESC", 
											array('%' . $_POST['coursesearch'] . '%', '%' . $_POST['coursesearch'] . '%')); 
		}
		
		if(count($courses)>0){			
			echo get_string('unit-select', 'local_enrolstaff');
			$cform = new course_form(null, array($courses)); 
		
			if($cform->is_cancelled()){
				redirect('enrolstaff.php');
			}else if($frocform = $cform->get_data()){ 
		 
			}else{	
				$cform->display();
			} 
		}else{
			echo $OUTPUT->notification("No units match the term " . $_POST['coursesearch']);
			$hform = new enrolment_home(); 
			if ($hform->is_cancelled()) {		
				
			} else if ($frohform = $hform->get_data()) {
					 
			} else {	 
			  $hform->display();
			}
		}		
	}	
	
	//Role selection
	if(ISSET($_POST['unit_select'])){		
		$course =  $_POST['course'];
		echo "<br />";
		$rform = new role_form(null, array('course'=>$course));
		
		if ($rform->is_cancelled()) {
			redirect('enrolstaff.php');
		} else if ($frorform = $rform->get_data()) {		
		  $course = null;
		  $course = $DB->get_record('enrolstaff_ssu', array('course'=>$frorform->course, 'user'=>$USER->id, 'role'=>$frorform->role));		  
		} else {	 
		  $rform->display();
		}
	} 
	
	//Confirmation
	if((ISSET($_POST['role_select']))){					  
		$c = $DB->get_record('course', array('id'=> $_POST['course'])); //TODO combine these two calls to DB then loop through
		$r = $DB->get_record('role', array('id'=>$_POST['role']));
		
		echo "You are about to be enrolled on <strong>" . $c->fullname . "</strong> with the role of <strong>" . $r->name . "</strong><br /><br />";						

		$unitleader = $DB->get_records_sql("SELECT CONCAT(u.firstname , ' ' , u.lastname) unit_leader, u.email , r.name
											FROM {user} u
											INNER JOIN {role_assignments} ra ON ra.userid = u.id
											INNER JOIN {context} ct ON ct.id = ra.contextid
											INNER JOIN {course} c ON c.id = ct.instanceid
											INNER JOIN {role} r ON r.id = ra.roleid
											WHERE r.id IN (15)
											AND c.id = ?", array($_POST['course'])); 
											
		$unitleaders = '';
		$unitleader_emails = '';
		$multiple = false;
		if(count($unitleader) > 0){
			$multiple = true;
			foreach($unitleader as $key=>$value){
				$unitleaders .= $value->unit_leader . ", ";
				$unitleader_emails .= $value->email . ", ";
			}				
			echo "An email will be sent to the current Unit leader";if($multiple == false){echo "s ";} echo " " . $unitleaders ." alerting them of your enrolment<br /><br />";				
		}		
		//echo get_string('enrol-warning', 'local_enrolstaff') 	;
		echo $OUTPUT->notification(get_string('enrol-warning', 'local_enrolstaff'), 'notifymessage');
		$_POST['unitleaders'] = $unitleaders;
		$_POST['unitleader_emails'] = $unitleader_emails;
		$_POST['shortname'] = $c->shortname;
		$_POST['rolename'] = $r->name;
		
		$sform = new submit_form(null, $_POST); 
		if ($sform->is_cancelled()) {		
			redirect('enrolstaff.php');
		} else if ($frosform = $sform->get_data()) {
		   	  	  
		} else {	 
		  $sform->display();
		}
	}

	if((ISSET($_POST['confirm_select']))){				
		
		$plugin = enrol_get_plugin('manual');
		$instance = $DB->get_record('enrol', array('courseid'=>$_POST['course'], 'enrol'=>'manual'), '*');
		if(!$instance){
			$course = $DB->get_record('course', array('id' => $_POST['course'])); 
			$fields = array(
            'status'          => '0',
            'roleid'          => '5',
            'enrolperiod'     => '0',
            'expirynotify'    => '0',
            'notifyall'       => '0',
            'expirythreshold' => '86400');
			$instance = $plugin->add_instance($course, $fields);
		}
		
		$instance = $DB->get_record('enrol', array('courseid'=>$_POST['course'], 'enrol'=>'manual'), '*');
		$plugin->enrol_user($instance, $USER->id, $_POST['role'], time(), 0, null, null);		
		echo $OUTPUT->notification("You have been enrolled on " . $_POST['shortname'] . " as " . $_POST['rolename'] , 'notifysuccess');
		
		if(isset($_POST["unitleaders"]) && !empty($_POST["unitleaders"])){
			$to      =  substr($_POST['unitleader_emails'], 0, -2);	
			$subject = $USER->firstname .' ' . $USER->lastname . " added as " . $_POST['rolename'] . " to " . $_POST['shortname'] ;
			$message = $USER->firstname .' ' . $USER->lastname . " has been added to the " . $COURSE->fullname . " unit " . $_POST['shortname'] . " as ". $_POST['rolename'] . " for which you are listed as Unit Leader.\r\n\n";
			$message .= "If this is incorrect please contact them to discuss " . $USER->email;
			$headers = "From: selfservice.learn@solent.ac.uk\r\n";
			$headers .= "Bcc: sarah.cotton@solent.ac.uk\r\n";
			$headers .= "Reply-To: LTU@solent.ac.uk\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion();
			mail($to, $subject, $message, $headers);	
		
			echo " An email has been sent to the current Unit Leader(s) " . $_POST['unitleaders'] . " alerting them of the change.<br /><br />";		  
		}
		
		$hform = new enrolment_home(); 
		if ($hform->is_cancelled()) {		
			
		} else if ($frohform = $hform->get_data()) {
		   	  	 
		} else {	 
		  $hform->display();
		}		
	}	

}else{		
	echo get_string('no-permission', 'local_enrolstaff');	
}
 echo "</div>";
 echo $OUTPUT->footer();
?>