<?php

$FunctionList = array();
$FunctionList['notification_items'] = array(
    'name' => 'notification_items',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/openpa_consiglio/classes/openpaconsigliofunctioncollection.php',
        'class' => 'OpenPAConsiglioFunctionCollection',
        'method' => 'fetchNotificationItems' ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'limit',
            'type' =>'integer',
            'required' => false,
            'default' => 10
        ),
        array(
            'name' => 'offset',
            'type' =>'integer',
            'required' => false,
            'default' => 0
        ),
        array(
            'name' => 'conditions',
            'type' =>'array',
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
