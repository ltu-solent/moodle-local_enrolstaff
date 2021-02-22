<?php
require_once('../../config.php');
require_once('form.php');
	
require_login(true);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/enrolstaff/unenrolstaff.php');
$PAGE->set_title('Staff Unenrolment');
$PAGE->set_heading('Staff Unenrolment');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('enrol-selfservice', 'local_enrolstaff'), new moodle_url('/local/enrolstaff/enrolstaff.php'));
$PAGE->navbar->add(get_string('unenrol', 'local_enrolstaff'));
global $USER, $_POST;
$return = $CFG->wwwroot.'/local/enrolstaff/unenrolstaff.php';
if(ISSET($_POST['enrol_home'])){	
	redirect('/local/enrolstaff/enrolstaff.php');
}	
echo $OUTPUT->header();

echo "<div class='maindiv'>";
$emaildomain = substr($USER->email, strpos($USER->email, "@") + 1);
$jobshop = strpos($USER->email, 'jobshop');

if(preg_match("/\b(academic|management|support)\b/", $USER->department) && preg_match("/\b(solent.ac.uk|qa.com)\b/", $emaildomain) && $jobshop === false || is_siteadmin()){
	//Course search
	if(count($_POST) <= 1){								
		$uform = new unenrol_form(); 
		if ($uform->is_cancelled()) {		
			redirect('unenrolstaff.php');
		} else if ($frouform = $uform->get_data()) {
				  
		} else {	 
		  $uform->display();
		}		
	}
		
	if(ISSET($_POST['unenrol_select'])){			
		$cform = new unenrol_confirm(); 
		
		if($cform->is_cancelled()){
			redirect('unenrolstaff.php');
		}else if($frocform = $cform->get_data()){ 
	 
		}else{	
			$cform->display();
		} 
	}

	if(ISSET($_POST['unenrol_confirm'])){
		$plugin_manual = enrol_get_plugin('manual');		
		$plugin_flat = enrol_get_plugin('flatfile');		
		$plugin_self = enrol_get_plugin('self');		

		$courses = $_POST['courses'];
		$courses = $courses;

		$enrol_instances = $DB->get_records_sql("	SELECT e.*
													FROM {user_enrolments} ue
													JOIN {enrol} e ON e.id = ue.enrolid
													JOIN {course} c ON c.id = e.courseid
													JOIN {user} u ON u.id = ue.userid
													INNER JOIN {role_assignments} ra ON ra.userid = u.id
													INNER JOIN {context} ct ON (ct.id = ra.contextid AND c.id = ct.instanceid)
													WHERE ra.userid = ?
													AND c.id IN (" . $courses . ") 
													GROUP BY c.id", array($USER->id));													
	
		foreach($enrol_instances as $k=>$v){
			
			if($v->enrol == 'manual'){
				$plugin_manual->unenrol_user($v, $USER->id);
			}elseif($v->enrol == 'flatfile'){
				$plugin_flat->unenrol_user($v, $USER->id);			
			}elseif($v->enrol == 'self'){
				$plugin_self->unenrol_user($v, $USER->id);
			}
		}		
						
		echo $OUTPUT->notification(get_string('unenrolconfirm', 'local_enrolstaff'), 'notifysuccess');		
		$hform = new enrolment_home(); 
		if ($hform->is_cancelled()) {		
			
		} else if ($frohform = $hform->get_data()) {
		   	  	 
		} else {	 
		  $hform->display();
		}
	}	

}else{
	echo get_string('nopermission', 'local_enrolstaff');	
}
 echo "</div>";
 echo $OUTPUT->footer();
?>