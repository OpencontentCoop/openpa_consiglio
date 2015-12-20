#!/usr/bin/env php
<?php
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "List Checkin" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );


/** @var Seduta[] $sedute */
$sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems(
    array(
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'published' => 'asc' )
    )
);

$headers = array( 'Partecipante' );
$rows = array();
foreach( $sedute as $seduta )
{
    $headers[] = $seduta->getObject()->attribute( 'name' ) . ' - IN';
    $headers[] = $seduta->getObject()->attribute( 'name' ) . ' - OUT';

    $helper = new OpenPAConsiglioPresenzaHelper( $seduta );
    $data = $helper->getData();

    /** @var Politico $partecipante */
    foreach( $seduta->partecipanti() as $partecipante )
    {
        if ( !isset($rows[$partecipante->id()]) )
        {
            $rows[$partecipante->id()] = array( $partecipante->getObject()->attribute( 'name' ) );
        }
        $rows[$partecipante->id()][] = isset( $data[$partecipante->id()]['checkin'] ) && $data[$partecipante->id()]['checkin'] > 0 ? date( 'H:i', $data[$partecipante->id()]['checkin'] ) : '';
        $rows[$partecipante->id()][] = isset( $data[$partecipante->id()]['checkout'] ) && $data[$partecipante->id()]['checkin'] > 0 ? date( 'H:i', $data[$partecipante->id()]['checkout'] ) : '';
    }
}

$cache = eZSys::cacheDirectory();
$fp = fopen($cache.'/export.csv', 'w');
fputcsv($fp, $headers);
foreach ($rows as $row )
{
    fputcsv($fp, $row);
}
fclose($fp);

$script->shutdown();