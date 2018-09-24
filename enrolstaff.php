<?php
require_once('../../config.php');
require_once('form.php');
require_login(true);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
$PAGE->set_title('Staff Enrolment');
$PAGE->set_heading('Staff Enrolment');
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

if((($USER->department == 'academic') || ($USER->department == 'management') || ($USER->department == 'support' && $jobshop === false)) && $emaildomain == 'solent.ac.uk' || (is_siteadmin())){
//if(is_siteadmin()){ //site admin only for testing

	//Course search
	echo"<h2>" . get_string('enrol-selfservice', 'local_enrolstaff') ."</h2>";
	//Role selection
	if(count($_POST) <= 1){
		$rform = new role_form(null, array());

		if ($rform->is_cancelled()) {
			redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
		} else if ($frorform = $rform->get_data()) {
			$course = null;
			$course = $DB->get_record('enrolstaff_ssu', array('course'=>$frorform->course, 'user'=>$USER->id, 'role'=>$frorform->role));
		} else {
			$rform->display();
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

	if(isset($_POST['unit_select'])){
		echo get_string('intro', 'local_enrolstaff');

		$sform = new search_form(null,array("role"=>$_POST['role']));
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
			// Check for strings to exclude here
			$excludeshortname = strtolower('x'.get_config('local_enrolstaff', 'excludeshortname'));
			$excludefullname = strtolower('x'.get_config('local_enrolstaff', 'excludefullname'));
			$searchterm = strtolower($_POST['coursesearch']);

			if(strpos($excludeshortname, $searchterm) !== false || strpos($excludefullname, $searchterm) !== false){
				echo $OUTPUT->notification("No units match the term " . $_POST['coursesearch']);
				$hform = new enrolment_home();
				if ($hform->is_cancelled()) {
				} else if ($frohform = $hform->get_data()) {
				} else {
				  $hform->display();
				}
			}else{
				$excludeid = get_config('local_enrolstaff', 'excludeid');
				$excludecategory = get_config('local_enrolstaff', 'excludecategory');
				$andcategory = 'AND';

				$categories = explode(",", $excludecategory);
				foreach ($categories as $k => $v) {
					$andcategory .= " cc.name  LIKE '%" . $v . "%' OR ";
				}
				$exclude = "AND (c.id NOT IN (" . $excludeid . ")";
				$andcategory = substr($andcategory, 0, -3).")";

				$sql = "	SELECT c.idnumber, c.id, c.shortname, c.fullname, DATE_FORMAT(FROM_UNIXTIME(c.startdate), '%d-%m-%Y') as startunix
									FROM {course} c
									JOIN {course_categories} cc on c.category = cc.id
									WHERE (c.shortname LIKE ?
									OR c.fullname LIKE ?)
									$exclude
									$andcategory
									ORDER BY c.shortname DESC";

				$courses = $DB->get_records_sql($sql,	array('%' . $_POST['coursesearch'] . '%', '%' . $_POST['coursesearch'] . '%'));
				if(count($courses)>0){
					echo get_string('unit-select', 'local_enrolstaff');
					if($_POST['role'] == get_config('local_enrolstaff', 'unitleaderid')){
							$course = array_shift($courses);
							$cform = new course_form(null, array(array($course)));
					}else{
							$cform = new course_form(null, array($courses));
					}

					if($cform->is_cancelled()){
						redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
					}else if($frocform = $cform->get_data()){

					}else{
						$cform->display();
					}
				}
			}
		}
	}

	//Confirmation
	if((isset($_POST['role_select']))){
		$c = $DB->get_record('course', array('id'=> $_POST['course'])); // TODO combine these two calls to DB then loop through
		$r = $DB->get_record('role', array('id'=>$_POST['role']));

		if($_POST['role'] == get_config('local_enrolstaff', 'unitleaderid')){
			echo "You are about to send a request for enrolment on <strong>" . $c->fullname . "</strong> with the role of <strong>" . str_replace(" Temp", "", $r->name) . "</strong><br /><br />";
		}else{
			echo "You are about to be enrolled on <strong>" . $c->fullname . "</strong> with the role of <strong>" . str_replace(" Temp", "", $r->name) . "</strong><br /><br />";
		}

		echo $OUTPUT->notification(get_string('enrol-warning', 'local_enrolstaff'), 'notifymessage');
		$_POST['shortname'] = $c->shortname;
		$_POST['fullname'] = $c->fullname;
		$_POST['rolename'] = $r->name;

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
			$toschool = '';
			switch ($category->id){
				case get_config('local_enrolstaff', 'sadfid'):
					$toschool = get_config('local_enrolstaff', 'sadf');
					break;
				case get_config('local_enrolstaff', 'sblcid'):
					$toschool = get_config('local_enrolstaff', 'sblc');
					break;
				case get_config('local_enrolstaff', 'smseid'):
					$toschool = get_config('local_enrolstaff', 'smse');
					break;
				case get_config('local_enrolstaff', 'smatid'):
					$toschool = get_config('local_enrolstaff', 'smat');
					break;
				case get_config('local_enrolstaff', 'sshssid'):
					$toschool = get_config('local_enrolstaff', 'sshss');
					break;
				}

			$to      =  $toschool;
			$subject = get_string('request-email-subject', 'local_enrolstaff', ['shortname'=>$_POST['shortname']]);
			$message = get_string('enrol-requested-school', 'local_enrolstaff', ['firstname'=>$USER->firstname, 'lastname'=>$USER->lastname,
			 											'fullname'=>$_POST['fullname'],'rolename'=>str_replace(" Temp", "", $_POST['rolename'])]) . "\r\n\n";
			$headers = "From: " . get_config('local_enrolstaff', 'emailfrom') . "\r\n";
			$headers .= "Bcc: " . get_config('local_enrolstaff', 'bcc') . "\r\n";
			$headers .= "Bcc: " . get_config('local_enrolstaff', 'studentrecords') . "\r\n";
			$headers .= "Reply-To: " . get_config('local_enrolstaff', 'studentrecords') . "\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion();
			mail($to, $subject, $message, $headers);

			// Inform user of request
			echo $OUTPUT->notification(get_string('enrol-request-alert', 'local_enrolstaff', ['schoolemail'=>$toschool, 'shortname'=>$_POST['shortname'],
																	'rolename'=>str_replace(" Temp", "", $_POST['rolename'])]) , 'notifysuccess');
			// Email reciept to user of requested
			$to      =  $USER->email;
			$subject = get_string('request-email-subject', 'local_enrolstaff', ['shortname'=>$_POST['shortname']]);
			$message = get_string('enrol-requested-user', 'local_enrolstaff', ['firstname'=>$USER->firstname, 'lastname'=>$USER->lastname,
			 											'fullname'=>$_POST['fullname'],'rolename'=>str_replace(" Temp", "", $_POST['rolename'])]) . "\r\n\n";
			$headers = "From: " . get_config('local_enrolstaff', 'emailfrom') . "\r\n";
			$headers .= "Bcc: " . get_config('local_enrolstaff', 'bcc') . "\r\n";
			$headers .= "Reply-To: " . $toschool . "\r\n";
			//$headers .= "X-Mailer: PHP/" . phpversion();
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			mail($to, $subject, $message, $headers);
			
			//Enrol user with temp role until full change overload
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
			$plugin->enrol_user($instance, $USER->id, 64, time(), 0, null, null);

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
			echo $OUTPUT->notification(get_string('enrol-confirmation', 'local_enrolstaff') . $_POST['shortname'] . " as " . str_replace(" Temp", "", $_POST['rolename']) , 'notifysuccess');
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
