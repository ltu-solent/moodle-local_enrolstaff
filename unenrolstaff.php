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
global $USER;
$return = $CFG->wwwroot.'/local/enrolstaff/unenrolstaff.php';

$action = optional_param('action', 'unenrol', PARAM_ALPHANUMEXT);

if($action == 'enrol_home'){	
    redirect('/local/enrolstaff/enrolstaff.php');
}	
echo $OUTPUT->header();

echo "<div class='maindiv'>";

$activeuser = new \local_enrolstaff\local\user($USER);
if (!$activeuser->user_can_enrolself()) {
    throw new moodle_exception('cannotenrolself', 'local_enrolstaff');
}

$enrolments = $activeuser->user_courses();
if (count($enrolments) == 0) {
    echo $OUTPUT->notification(get_string('nocourses', 'local_enrolstaff'));
    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'), get_string('enrolmenthome', 'local_enrolstaff'));
    $action = 'none';
}

if ($action == 'unenrol') {
    $uform = new unenrol_form(null, ['enrolments' => $enrolments]); 
    if ($uform->is_cancelled()) {		
        redirect('unenrolstaff.php');
    } else if ($frouform = $uform->get_data()) {
                
    } else {	 
        $uform->display();
    }
}
        
if ($action == 'unenrol_select') {
    $courses = required_param_array('courses', PARAM_INT);
    foreach ($courses as $key => $courseid) {
        // What checks should there be that someone can unenrol themselves?
        $validcourse = $activeuser->is_enrolled_on($courseid);
        if (!$validcourse) {
            unset($courses[$key]);
        }
    }

    $cform = new unenrol_confirm(null, ['courses' => $courses]);
    
    if($cform->is_cancelled()){
        redirect('unenrolstaff.php');
    }else if($frocform = $cform->get_data()){ 
    
    }else{	
        $cform->display();
    } 
}

if($action == 'unenrol_confirm') {
    $pluginmanual = enrol_get_plugin('manual');		
    $pluginflat = enrol_get_plugin('flatfile');		
    $pluginself = enrol_get_plugin('self');		
    $courses = required_param('courses', PARAM_SEQUENCE);
    $courses = explode(',', $courses);
    $enrolmentscourseids = array_column($enrolments, 'course_id');
    $listed = array_filter($courses, function($course) use ($enrolmentscourseids) {
        return in_array($course, $enrolmentscourseids);
    });
    list($insql, $inparams) = $DB->get_in_or_equal($listed, SQL_PARAMS_NAMED);
    $params = ['userid' => $USER->id] + $inparams;
    $enrolinstances = $DB->get_records_sql("SELECT e.*
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                JOIN {user} u ON u.id = ue.userid
                INNER JOIN {role_assignments} ra ON ra.userid = u.id
                INNER JOIN {context} ct ON (ct.id = ra.contextid AND c.id = ct.instanceid)
                WHERE ra.userid = :userid
                AND c.id {$insql} 
                GROUP BY c.id", $params);													

    foreach($enrolinstances as $k=>$v){
        if($v->enrol == 'manual'){
            $pluginmanual->unenrol_user($v, $USER->id);
        }elseif($v->enrol == 'flatfile'){
            $pluginflat->unenrol_user($v, $USER->id);			
        }elseif($v->enrol == 'self'){
            $pluginself->unenrol_user($v, $USER->id);
        }
    }		
                    
    echo $OUTPUT->notification(get_string('unenrolconfirm', 'local_enrolstaff'), 'notifysuccess');		
    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'), get_string('enrolmenthome', 'local_enrolstaff'));
}

 echo "</div>";
 echo $OUTPUT->footer();