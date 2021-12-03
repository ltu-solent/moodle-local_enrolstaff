<?php
require_once(dirname(__FILE__).'/../../config.php');


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
