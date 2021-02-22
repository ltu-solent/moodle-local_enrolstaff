<?php
require_once('../../config.php');
require_once('form.php');
require_once('lib.php');
require_login(true);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
$PAGE->set_title(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_heading(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('enrol-selfservice', 'local_enrolstaff'), new moodle_url($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php'));
$PAGE->navbar->add('Enrol onto courses');
global $USER;
$return = $CFG->wwwroot.'/local/enrolstaff/enrolstaff.php';
if(ISSET($_POST['enrol_home'])){
	redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
}
if(ISSET($_POST['unenrol'])){
		redirect($CFG->wwwroot. '/local/enrolstaff/unenrolstaff.php');
}
echo $OUTPUT->header();

echo "<div class='maindiv'>";
$emaildomain = substr($USER->email, strpos($USER->email, "@") + 1);
$jobshop = strpos($USER->email, 'jobshop');

if(preg_match("/\b(academic|management|support)\b/", $USER->department) && preg_match("/\b(solent.ac.uk|qa.com)\b/", $emaildomain) && $jobshop === false || is_siteadmin()){

	//Role selection
	if(count($_POST) <= 1){
		
		$rform = new role_form(null, array($emaildomain));
		echo $OUTPUT->notification(get_string('enrolintro', 'local_enrolstaff') , 'notifymessage');
		if ($rform->is_cancelled()) {
			redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
		} else if ($frorform = $rform->get_data()) {
			$course = null;
			$course = $DB->get_record('enrolstaff_ssu', array('course'=>$frorform->course, 'user'=>$USER->id, 'role'=>$frorform->role));
		} else {
			$rform->display();
		}

		echo get_string('unenrolheader', 'local_enrolstaff');
		echo get_string('unenrolintro', 'local_enrolstaff');

		$uform = new unenrol_button();
		if ($uform->is_cancelled()) {

		} else if ($frouform = $uform->get_data()) {

		} else {
		  $uform->display();
		}
	}
	
	//Course search
	if(isset($_POST['unit_select'])){
		echo get_string('intro', 'local_enrolstaff', ['excludeshortname'=>get_config('local_enrolstaff', 'excludeshortname'),'excludefullname'=>get_config('local_enrolstaff', 'excludefullname'),'qahecodes'=>get_config('local_enrolstaff', 'qahecodes')]);

		$sform = new search_form(null,array("role"=>$_POST['role'], $emaildomain));
		if ($sform->is_cancelled()) {
			redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
		} else if ($frosform = $sform->get_data()) {

		} else {
		  $sform->display();
		}
	}

	//Course results list
	if(isset($_POST['search_select'])){

	  if($_POST['coursesearch'] != ''){
			$courses = course_search($_POST['coursesearch'], $emaildomain);		
	  }

	  if(count($courses)>0){
	    echo get_string('unitselect', 'local_enrolstaff');

		$cform = new course_form(null, array($courses));

	    if($cform->is_cancelled()){
	      redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
	    }else if($frocform = $cform->get_data()){

	    }else{
	      $cform->display();
	    }
	  }else{
	    echo $OUTPUT->notification("No modules match the term " . $_POST['coursesearch']);
	    $hform = new enrolment_home();
	    if ($hform->is_cancelled()) {

	    } else if ($frohform = $hform->get_data()) {

	    } else {
	      $hform->display();
	    }
	  }
	}

	//Confirmation
	if((isset($_POST['role_select']))){
		$c = $DB->get_record('course', array('id'=> $_POST['course'])); // TODO combine these two calls to DB then loop through
		$r = $DB->get_record('role', array('id'=>$_POST['role']));

		if($_POST['role'] == get_config('local_enrolstaff', 'unitleaderid')){
			echo "You are about to send a request for enrolment on <strong>" . $c->fullname . "</strong> with the role of <strong>" . $r->name . "</strong><br /><br />";
		}else{
			echo "You are about to be enrolled on <strong>" . $c->fullname . "</strong> with the role of <strong>" . $r->name . "</strong><br /><br />";
		}

		echo $OUTPUT->notification(get_string('enrolwarning', 'local_enrolstaff'), 'notifymessage');
		$_POST['shortname'] = $c->shortname;
		$_POST['fullname'] = $c->fullname;
		$_POST['rolename'] = $r->name;
		
		$startdate = new DateTime();
		$startdate->setTimestamp($c->startdate);
		$startdate = userdate($startdate->getTimestamp(), '%d/%m/%Y');
		
		$enddate = new DateTime();
		$enddate->setTimestamp($c->enddate);
		$enddate = userdate($enddate->getTimestamp(), '%d/%m/%Y');
		
		$_POST['coursedate'] = $startdate . " - " . $enddate;

		$sform = new submit_form(null, $_POST);
		if ($sform->is_cancelled()) {
			redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
		} else if ($frosform = $sform->get_data()) {

		} else {
		  $sform->display();
		}
	}

	if((isset($_POST['confirm_select']))){
		// Inform TAR of unit leader enrolment
		if($_POST['role'] == get_config('local_enrolstaff', 'unitleaderid')){
			// Send to school admin - confirmation to studentregistery
			$sql = "SELECT cc1.id, c.shortname, cc1.*
							FROM {course} c
							JOIN {course_categories} cc ON c.category = cc.id
							JOIN {course_categories} cc1 ON cc.parent = cc1.id
							WHERE c.id = ?";
			$category = $DB->get_record_sql($sql,	array($_POST['course']));

			$to = get_config('local_enrolstaff', 'studentrecords') . "\r\n";
			$subject = get_string('requestemailsubject', 'local_enrolstaff', ['shortname'=>$_POST['shortname']]);
			$message = get_string('enrolrequestedschool', 'local_enrolstaff', ['fullname'=>$_POST['fullname'] . " " . $_POST['coursedate'],
																				'rolename'=>$_POST['rolename']]) . "\r\n\n";
			$headers = "From: " . $USER->email . "\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion();
			mail($to, $subject, $message, $headers);

			// Inform user of request
			echo $OUTPUT->notification(get_string('enrolrequestalert', 'local_enrolstaff', ['schoolemail'=>$to, 'shortname'=>$_POST['shortname'],
																	'rolename'=>$_POST['rolename']])  , 'notifysuccess');
			// Email receipt to user of requested
			$to      =  $USER->email;
			$subject = get_string('requestemailsubject', 'local_enrolstaff', ['shortname'=>$_POST['shortname']]);
			$message = get_string('enrolrequesteduser', 'local_enrolstaff', ['fullname'=>$_POST['fullname'],'rolename'=>$_POST['rolename']]) . "\r\n\n";
			$headers = "From: " . get_config('local_enrolstaff', 'studentrecords') . "\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			mail($to, $subject, $message, $headers);

		}else{
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
			echo $OUTPUT->notification(get_string('enrolconfirmation', 'local_enrolstaff') . $_POST['shortname'] . " as " . $_POST['rolename'], 'notifysuccess');
		}

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
