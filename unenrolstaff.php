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
if(($USER->department == 'academic') || ($USER->department == 'management') || ($USER->department == 'support') || ($USER->department == 'external') || (is_siteadmin())){	
	//Course search
	echo"<h2>Staff unenrolment self-service</h2>";
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
		
		//$courses = explode(',', $_POST['courses']);
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

													
		//foreach($courses as $key=>$value){
	
			foreach($enrol_instances as $k=>$v){
//print_object($v->enrol);				
				if($v->enrol == 'manual'){
					$plugin_manual->unenrol_user($v, $USER->id);
				}elseif($v->enrol == 'flatfile'){
					$plugin_flat->unenrol_user($v, $USER->id);			
				}elseif($v->enrol == 'self'){
					$plugin_self->unenrol_user($v, $USER->id);
				}
			}	
//die();			
			
			// $instance_manual = $DB->get_record('enrol', array('courseid'=>$value, 'enrol'=>'manual', '*', MUST_EXIST));
			// $instance_flat = $DB->get_record('enrol', array('courseid'=>$value, 'enrol'=>'flatfile', '*', MUST_EXIST));
			
		//}		
						
		echo $OUTPUT->notification(get_string('unenrol-confirm', 'local_enrolstaff'), 'notifysuccess');		
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