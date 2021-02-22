<?php
require_once(dirname(__FILE__).'/../../config.php');

function get_and($emaildomain){
	$excludename = get_config('local_enrolstaff', 'excludeshortname');
	$excludeterm = get_config('local_enrolstaff', 'excludefullname');
	$excludename = explode(',', $excludename);
	$excludeterm = explode(',', $excludeterm);
	
	$and = null;
	
	foreach($excludename as $key=>$value){

		$and .= "AND (c.shortname NOT LIKE '$value%' OR c.fullname NOT LIKE '%$value%') ";
	}
	
	foreach($excludeterm as $key=>$value){

		$and .= "AND c.fullname NOT LIKE '%$value%' ";
	}
	
	if($emaildomain == 'qa.com'){
		$validcodes = get_config('local_enrolstaff', 'qahecodes');				
	}
	// elseif(($emaildomain == 'solent.ac.uk') && ($USER->institution == 'External Partner')){
		// $validcodes = get_config('local_enrolstaff', 'bcascodes');
	// }
	
	if(isset($validcodes)){
		$validcodes = explode(',', $validcodes);	
		foreach($validcodes as $key=>$value){
			$and .= "AND c.shortname LIKE '%$value%' ";
		}
	}

	return $and;
}

function course_search($coursesearch, $emaildomain){
	
	global $DB;
	
	$excludecourses = get_config('local_enrolstaff', 'excludeid');	
	$excludecourses = explode(',', $excludecourses);	

	list($inorequalsql, $inparams) = $DB->get_in_or_equal($excludecourses, SQL_PARAMS_NAMED, '', false);
			
	$params = [
		'coursesearch1' => '%'.$coursesearch.'%',
		'coursesearch2' => '%'.$coursesearch.'%'
	 ];
	$params += $inparams;
	
	$and = get_and($emaildomain);

	$sql = "SELECT c.idnumber, c.id, c.shortname, c.fullname, DATE_FORMAT(FROM_UNIXTIME(c.startdate), '%d-%m-%Y') as startunix
			FROM {course} c
			JOIN mdl_course_categories cc on c.category = cc.id
			WHERE (c.shortname LIKE :coursesearch1
			OR c.fullname LIKE :coursesearch2)
			$and
			AND c.id {$inorequalsql}
			AND (cc.idnumber LIKE 'modules_%' OR cc.idnumber LIKE 'courses_%')
			AND c.visible = 1
			ORDER BY c.shortname DESC";

	$courses = $DB->get_records_sql($sql, $params);
	
	return $courses;
	
}

function get_roles($emaildomain){
	global $DB;
	
	if($emaildomain == 'qa.com'){
		$getroles = get_config('local_enrolstaff', 'qaheroleids');
	// }elseif(($emaildomain == 'solent.ac.uk') && ($USER->institution == 'External Partner')){
		// $getroles = get_config('local_enrolstaff', 'bcasroleids');
	}else{
		$getroles = get_config('local_enrolstaff', 'roleids');	
	}
	
	$roles = explode(',', $getroles);
	$params = array();
	
	list($inorequalsql, $params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, '', true);							

	$sql = "SELECT id, name
			FROM {role}
			WHERE id {$inorequalsql}
			ORDER BY name";

	$roles = $DB->get_records_sql_menu($sql, $params);


	return $roles;
}
