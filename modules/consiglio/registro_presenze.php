<?php

$action = $Params['Action'];

$data = array();

try {
    header('HTTP/1.1 200 OK');
    if ($action == 'timeline_presenze_seduta') {
        $handler = new DataHandlerTimeLinePresenzeSeduta($Params);
        $data = $handler->getData();
    } elseif ($action == 'percentuale_presenze_seduta') {
        $handler = new DataHandlerPercentualePresenzeSeduta($Params);
        $data = $handler->getData();
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $data = array('error' => $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($data);
eZExecution::cleanExit();

