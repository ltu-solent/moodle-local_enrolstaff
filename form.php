<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class unenrol_button extends moodleform {
	public function definition() {
		global $CFG, $USER;

		$mform = $this->_form;
		$mform->addElement('hidden', 'unenrol', 'unenrol');
		$mform->setType('unenrol', PARAM_ACTION);
		$this->add_action_buttons($cancel = false, $submitlabel='Unenrol from modules');
	}
}

class role_form extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;
		$emaildomain = $this->_customdata;
		
		$options = get_roles($emaildomain[0]);
		$options = array('' =>'Select a role') + $options;		
		$result = count($options);

		if($result > 0){
			$select = $mform->addElement('select', 'role', get_string('role'), $options, 'required');
			$mform->addRule('role', get_string('required'), 'required');
			$mform->addElement('hidden', 'unit_select', 'unit_select');
			$mform->setType('unit_select', PARAM_ACTION);
			$this->add_action_buttons($cancel = false, $submitlabel='Select role');
		}
	}
}

class search_form extends moodleform {
	public function definition() {
		global $CFG, $USER;

		$mform = $this->_form;
		$mform->addElement('text', 'coursesearch', get_string('coursesearch', 'local_enrolstaff'), 'required');
		$mform->addElement('hidden', 'search_select', 'search_select');
		$mform->setType('search_select', PARAM_ACTION);
		$mform->addRule('coursesearch', get_string('required'), 'required');
		$mform->setType('coursesearch', PARAM_RAW);
		$mform->addElement('hidden', 'role', $_POST['role']);
		$mform->setType('role', PARAM_ACTION);
		$this->add_action_buttons($cancel = false, $submitlabel='Search');
	}
}

class course_form extends moodleform {
	public function definition() {
		global $USER, $DB;

		$mform = $this->_form;
		$courses = $this->_customdata;

		$enrolledon =  $DB->get_records_sql("	SELECT FLOOR(RAND() * 401) + 100 as id, r.id role_id, c.id course_id, r.name
												FROM {course} AS c
												JOIN {context} AS ctx ON c.id = ctx.instanceid
												JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
												JOIN {role} AS r ON ra.roleid = r.id
												JOIN {user} AS u ON u.id = ra.userid
												WHERE u.id = ?", array($USER->id));

		//loop through and add role names string and id to array id=>2 roles=>student, teacher etc.
		$course_array = array();

		//initialise the arrays to avoid offsets
		for($x=0;$x<count($enrolledon);$x++){
			foreach($enrolledon as $enrolment =>$evalue){
				if(!array_key_exists($evalue->course_id, $course_array)){
					$course_array[$evalue->course_id] = '';
				}
			}
		}

		//fill arrays
		if(!empty($enrolledon)){
			foreach($enrolledon as $enrolment =>$evalue){
					$course_array[$evalue->course_id] .= $evalue->name . ', ';
			}
		}

		//then loop through that array and check for roles
		$radioarray=array();
		foreach($courses as $course => $value){
			foreach($value as $c => $v){
				$fullname = explode('(Start',$v->fullname);
				if (array_key_exists($v->id, $course_array)){
					 $radioarray[] =& $mform->createElement('radio', 'course', '', $v->idnumber . " - " . $fullname[0] . " - Start date: " . $v->startunix ."<span class='enrolled'><strong> (already enrolled as " . rtrim($course_array[$v->id] , ", ") . ")</strong></span>", $v->id, 'disabled');
				}else{
					 $radioarray[] =& $mform->createElement('radio', 'course', '', $v->idnumber . " - " . $fullname[0] . " - Start date: " . $v->startunix , $v->id, 'required');
				}
			}
		}

		$mform->addGroup($radioarray, 'radioar', 'Select a module', array('<br /><br />', '<br /><br />'), false);
		$mform->addGroupRule('radioar', get_string('required'), 'required');
		$mform->addElement('hidden', 'role_select', 'role_select');
		$mform->setType('role_select', PARAM_ACTION);
		$mform->addElement('hidden', 'role', $_POST['role']);
		$mform->setType('role', PARAM_ACTION);
		$this->add_action_buttons($cancel = false, $submitlabel='Select module');
	}
}

class submit_form extends moodleform {
	public function definition() {
		global $USER;
		$mform = $this->_form;
		$data = $this->_customdata;

		$mform->addElement('hidden', 'course', $this->_customdata['course']);
		$mform->setType('course', PARAM_ACTION);
		$mform->addElement('hidden', 'shortname', $this->_customdata['shortname']);
		$mform->setType('shortname', PARAM_ACTION);
		$mform->addElement('hidden', 'fullname', $this->_customdata['fullname']);
		$mform->setType('fullname', PARAM_ACTION);
		$mform->addElement('hidden', 'role', $this->_customdata['role']);
		$mform->setType('role', PARAM_ACTION);
		$mform->addElement('hidden', 'rolename', $this->_customdata['rolename']);
		$mform->setType('rolename', PARAM_ACTION);
		$mform->addElement('hidden', 'coursedate', $this->_customdata['coursedate']);
		$mform->setType('coursedate', PARAM_ACTION);
		$mform->addElement('hidden', 'confirm_select', 'confirm_select');
		$mform->setType('confirm_select', PARAM_ACTION);
		$this->add_action_buttons($cancel = false, $submitlabel='Confirm');

	}
}

class unenrol_form extends moodleform {
	public function definition() {
		global $CFG, $USER, $DB, $_POST;

		$enroled_courses =  $DB->get_records_sql("	SELECT FLOOR(RAND() * 401) + 100 as id, c.id course_id, c.fullname, FROM_UNIXTIME(c.startdate, '%d-%m-%Y') startdate, r.id role_id, r.name,
													(SELECT GROUP_CONCAT(r.name SEPARATOR ', ')
													FROM mdl_user u1
													INNER JOIN {role_assignments} ra ON ra.userid = u1.id
													INNER JOIN {context} ct ON ct.id = ra.contextid
													INNER JOIN {course} c2 ON c2.id = ct.instanceid
													INNER JOIN {role} r ON r.id = ra.roleid
													WHERE c2.id = c.id
													AND u1.id = u.id
													) AS roles
													FROM {course} AS c
													JOIN {context} AS ctx ON c.id = ctx.instanceid
													JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
													JOIN {role} AS r ON ra.roleid = r.id
													JOIN {user} AS u ON u.id = ra.userid
													WHERE u.id = ?
													AND ra.component != 'enrol_cohort'
													AND ra.component != 'enrol_meta'
													GROUP BY c.id", array($USER->id));


		if(count($enroled_courses) < 1){
			global $OUTPUT;
			echo $OUTPUT->notification(get_string('nocourses', 'local_enrolstaff'));
			$hform = new enrolment_home();
			if ($hform->is_cancelled()) {

			} else if ($frohform = $hform->get_data()) {

			} else {
			  $hform->display();
			}
		}else{
			echo get_string('unenrolselect', 'local_enrolstaff');
			$mform = $this->_form;
			foreach($enroled_courses as $course => $value){
				$mform->addElement("html", "<div id='fitem_id_courses' class='fitem fitem_fcheckbox femptylabel'>
											<div class='fitemtitle'>
												<label for='id_courses'> </label>
											</div>

											<div class='felement fcheckbox'>
												<span>
													<input name='courses[]' type='checkbox' value='" .$value->course_id . "' id='id_courses'>
													<label for='id_courses'>" .  $value->fullname . " - Start date: " . $value->startdate ." - <strong>(Enrolled as " . $value->roles . ")</strong></label>
												</span>
											</div>
										</div>");

			}

			$mform->addElement('hidden', 'unenrol_select', 'unenrol_select');
			$mform->setType('unenrol_select', PARAM_ACTION);
			$this->add_action_buttons($cancel = false, $submitlabel=get_string('unenrol', 'local_enrolstaff'));
		}
	}
}

class unenrol_confirm extends moodleform {
	public function definition() {
		global $CFG, $USER, $DB;

		$courses = $_POST['courses'];
		$mform = $this->_form;

		$where = '';
		foreach($courses as $key=>$value){
				$where .=  $value . "," ;
		}
		$where = substr($where, 0, -1);
		$enroled_courses = $DB->get_records_sql("	SELECT id, fullname
													FROM {course}
													WHERE id IN (". $where .")");

		$mform->addElement("html", get_string('unenrolwarning', 'local_enrolstaff'));

		foreach($enroled_courses as $course => $value){
			$mform->addElement("html", $value->fullname ."<br />");
		}

		$mform->addElement("html", "<br />");
		$mform->addElement('hidden', 'unenrol_confirm', 'unenrol_confirm');
		$mform->setType('unenrol_confirm', PARAM_ACTION);
		$mform->addElement('hidden', 'courses', $where);
		$mform->setType('courses', PARAM_RAW);
		$this->add_action_buttons($cancel = true, $submitlabel='Confirm');
	}
}

class enrolment_home extends moodleform {
	public function definition() {
		global $CFG, $USER;

		$mform = $this->_form;
		$mform->addElement('hidden', 'enrol_home', 'enrol_home');
		$mform->setType('enrol_home', PARAM_ACTION);
		$this->add_action_buttons($cancel = false, $submitlabel='Enrolment home');
	}
}
?>
