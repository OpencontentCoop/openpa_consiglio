<?php

$FunctionList = array();
$FunctionList['notification_items'] = array(
    'name' => 'notification_items',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchNotificationItems'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'limit',
            'type' => 'integer',
            'required' => false,
            'default' => 10
        ),
        array(
            'name' => 'offset',
            'type' => 'integer',
            'required' => false,
            'default' => 0
        ),
        array(
            'name' => 'conditions',
            'type' => 'array',
            'required' => false,
            'default' => null
        ),
        array(
            'name' => 'sort',
            'type' => 'array',
            'required' => false,
            'default' => null
        )
    )
);

$FunctionList['tipi_votazione'] = array(
    'name' => 'tipi_votazione',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchTipiVotazione'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['next_items'] = array(
    'name' => 'next_items',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchNextItems'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['latest_osservazioni'] = array(
    'name' => 'next_items',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchLatestOsservazioni'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['alerts'] = array(
    'name' => 'alerts',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchAlerts'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['active_dashboards'] = array(
    'name' => 'active_dashboards',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchActiveDashboards'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['socket_info'] = array(
    'name' => 'socket_info',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchSocketInfo'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['post'] = array(
    'name' => 'post',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchPost'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'object',
            'type' => 'mixed',
            'required' => false,
            'default' => null
        ),
        array(
            'name' => 'node',
            'type' => 'mixed',
            'required' => false,
            'default' => null
        )
    )
);

$FunctionList['editorial_url'] = array(
    'name' => 'editorial_url',
    'operation_types' => array('read'),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchEditorialUrl'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'object',
            'type' => 'object',
            'required' => true
        ),
        array(
            'name' => 'do_redirect',
            'type' => 'boolean',
            'required' => false
        ),        
    )
);