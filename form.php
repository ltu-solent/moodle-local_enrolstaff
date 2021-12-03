<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class role_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
             
        $options = $customdata['activeuser']->get_roles_menu();
        $options = array('' => get_string('selectarole', 'local_enrolstaff')) + $options;		
        $result = count($options);

        if ($result > 0) {
            $mform->addElement('select', 'role', get_string('role'), $options, 'required');
            $mform->addRule('role', get_string('required'), 'required');
            $mform->addElement('hidden', 'action', 'unit_select');
            $mform->setType('action', PARAM_ALPHANUMEXT);
            $this->add_action_buttons(false, get_string('role', 'local_enrolstaff'));
        }
    }
}

class search_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('text', 'coursesearch', get_string('coursesearch', 'local_enrolstaff'), 'required');
        $mform->addRule('coursesearch', get_string('required'), 'required');
        $mform->setType('coursesearch', PARAM_RAW);

        $mform->addElement('hidden', 'action', 'search_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'role', $customdata['role']);
        $mform->setType('role', PARAM_ALPHANUMEXT);
        $this->add_action_buttons($cancel = false, $submitlabel='Search');
    }
}

class course_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $courses = $customdata['courses'];
        $activeuser = $customdata['activeuser'];
        $role = $customdata['role'];

        $enrolledon =  $DB->get_records_sql("	SELECT FLOOR(RAND() * 401) + 100 as id, r.id role_id, c.id course_id, r.name
                                                FROM {course} AS c
                                                JOIN {context} AS ctx ON c.id = ctx.instanceid
                                                JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
                                                JOIN {role} AS r ON ra.roleid = r.id
                                                JOIN {user} AS u ON u.id = ra.userid
                                                WHERE u.id = :userid", array('userid' => $activeuser->user->id));

        //loop through and add role names string and id to array id=>2 roles=>student, teacher etc.
        $course_array = array();
        
        // Initialise the arrays to avoid offsets.
        for ($x=0; $x<count($enrolledon); $x++) {
            foreach ($enrolledon as $evalue){
                if (!array_key_exists($evalue->course_id, $course_array)){
                    $course_array[$evalue->course_id] = '';
                }
            }
        }

        // Fill arrays.
        if(!empty($enrolledon)){
            foreach($enrolledon as $evalue){
                $course_array[$evalue->course_id] .= $evalue->name . ', ';
            }
        }

        // Then loop through that array and check for roles.
        $radioarray = array();
        foreach ($courses as $course) {
            $fullname = explode('(Start', $course->fullname);
            $courselabel = get_string('courselabel', 'local_enrolstaff', [
                'idnumber' => $course->idnumber,
                'fullname' => $fullname[0],
                'startunix' => $course->startunix]);
            if (array_key_exists($course->id, $course_array)) {
                $radioarray[] =& $mform->createElement('radio', 'course', '',
                    $courselabel . get_string('existingroles', 'local_enrolstaff', rtrim($course_array[$course->id], ", ")),
                    $course->id, 'disabled');
               } else {
                $radioarray[] =& $mform->createElement('radio', 'course', '',
                    $courselabel, $course->id, 'required');
               }
        }

        $mform->addGroup($radioarray, 'radioar', get_string('selectamodule', 'local_enrolstaff'), array('<br /><br />', '<br /><br />'), false);
        $mform->addGroupRule('radioar', get_string('required'), 'required');
        $mform->addElement('hidden', 'action', 'role_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'role', $role);
        $mform->setType('role', PARAM_INT);
        $this->add_action_buttons(false, get_string('selectmodule', 'local_enrolstaff'));
    }
}

class submit_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'course', $data['course']);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'role', $data['role']);
        $mform->setType('role', PARAM_INT);
        $mform->addElement('hidden', 'action', 'confirm_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(false, get_string('confirm'));
    }
}

class unenrol_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $enrolments = $customdata['enrolments'];
       
        echo get_string('unenrolselect', 'local_enrolstaff');

        foreach ($enrolments as $course) {
            $label = get_string('courselabel', 'local_enrolstaff', [
                'fullname' => $course->fullname,
                'idnumber' => $course->idnumber,
                'startunix' => $course->startdate]);
            $label .= get_string('existingroles', 'local_enrolstaff', $course->roles);
            
            $mform->addElement("html", "
                <div id='fitem_id_courses' class='fitem fitem_fcheckbox femptylabel'>
                    <div class='felement fcheckbox'>
                        <span>
                            <input name='courses[]' type='checkbox' value='" . $course->course_id . "' id='id_courses'>
                            <label for='id_courses'>" . $label . "</label>
                        </span>
                    </div>
                </div>");
        }

        $mform->addElement('hidden', 'action', 'unenrol_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(false, get_string('unenrol', 'local_enrolstaff'));
    }
}

class unenrol_confirm extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $courses = $customdata['courses'];

        list($wheresql, $whereparams) = $DB->get_in_or_equal($courses);
        $enroled_courses = $DB->get_records_sql("	SELECT id, fullname, shortname
                                                    FROM {course}
                                                    WHERE id {$wheresql}", $whereparams);

        $mform->addElement("html", get_string('unenrolwarning', 'local_enrolstaff'));
        $courselist = '';
        foreach($enroled_courses as $course){
            $courselist .= html_writer::tag('li', $course->shortname . ' - ' . $course->fullname);
        }
        $courselist = html_writer::tag('ul', $courselist);
        $mform->addElement("html", $courselist);

        $mform->addElement("html", "<br />");
        $mform->addElement('hidden', 'action', 'unenrol_confirm');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'courses', join(',', $courses));
        $mform->setType('courses', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('confirm'));
    }
}

