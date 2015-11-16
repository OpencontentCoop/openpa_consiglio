<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$sedutaId = intval( $Params['SedutaId'] );
$userId = intval( $Params['UserId'] );
$votazioneId = intval( $Params['VotazioneId'] );

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

    $customEvents = null;
    if ( $votazioneId > 0 )
    {
        try
        {
            $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $votazioneId );
            if ( $votazione instanceof Votazione )
            {
                $customEvents = array();
                $start = $votazione->stringAttribute( Votazione::$startDateIdentifier, 'intval' );
                $customEvents[] = new OpenPAConsiglioCustomDetection(
                    $start,
                    'Inizio ' . $votazione->getObject()->attribute( 'name' )
                );
                $end = $votazione->stringAttribute( Votazione::$endDateIdentifier, 'intval' );
                $customEvents[] = new OpenPAConsiglioCustomDetection(
                    $end,
                    'Fine ' . $votazione->getObject()->attribute( 'name' )
                );
                $userVoted = OpenPAConsiglioVoto::userAlreadyVoted( $votazione, $userId, false );
                if ( $userVoted instanceof  OpenPAConsiglioVoto )
                {
                    $customEvents[] = new OpenPAConsiglioCustomDetection(
                        $userVoted->attribute( 'created_time' ),
                        'Voto',
                        'fa-times'
                    );
                }
            }
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage() );
        }
    }

    $helper = new OpenPAConsiglioPresenzaHelper( $seduta, $customEvents, $politico->id() );
    $data = $helper->getData();

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
    $tpl->setVariable( 'in_percent', $data['in_percent'] );
    $tpl->setVariable( 'out_percent', $data['out_percent'] );
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
