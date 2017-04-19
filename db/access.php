<?php
$capabilities = array(
    'local/enrolstaff:managestaffenrolments' => array(
        'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
        'captype'      => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            //'student'        => CAP_PROHIBIT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'          => CAP_ALLOW
			)
			
    ),
	
);