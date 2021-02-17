<?php
require_once(dirname(__FILE__).'/../../config.php');

function get_and(){
	
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

	return $and;
}
