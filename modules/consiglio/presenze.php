<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$sedutaId = intval( $Params['SedutaId'] );
$userId = intval( $Params['UserId'] );
$action = $Params['Action'];
$actionParameter = $Params['ActionParameter'];

try
{
    /** @var Seduta $seduta */
    $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
    /** @var Politico $politico */
    $politico = OCEditorialStuffHandler::instance( 'politico' )->fetchByObjectId( $userId );

    $detections = array();
    $events = array();
    $totalTime = 0;
    $totalPercent = 0;


    $helper = new OpenPAConsiglioPresenzaHelper( $seduta, null, $politico->id() );
    $data = $helper->getEventsAndIntervals();

//    echo '<pre>';
//    print_r($detections);
//    print_r($current->intervals );
//    print_r($events);
//    die();

    $tpl->setVariable( 'seduta', $seduta );
    $tpl->setVariable( 'politico', $politico );
    $tpl->setVariable( 'time_total', $data['time'] );
    $tpl->setVariable( 'events', $data['events'] );
    $tpl->setVariable( 'detections', $data['detections'] );
    $tpl->setVariable( 'percent', $data['percent'] );
    $Result['content'] = $tpl->fetch( 'design:consiglio/presenze.tpl' );
}
catch ( Exception $e )
{
    return $module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$Result['node_id'] = 0;

$contentInfoArray = array(
    'url_alias' => 'consiglio/presenze/' . $sedutaId . '/' .  $userId,
    'site_title' => 'Dettaglio presenze'
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true,
    'site_title' => 'Dettaglio presenze',
    'top_menu' => true,
    'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
);
if ( is_array( $tpl->variable( 'persistent_variable' ) ) )
{
    $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
}
$tpl->setVariable( 'site_title', $contentInfoArray['persistent_variable']['site_title'] );
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array(
    array( 'text' => 'Dettaglio presenze', 'url' => $contentInfoArray['url_alias'] )
);
