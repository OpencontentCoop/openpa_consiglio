#!/usr/bin/env php
<?php
/**
 *
 */

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Fix partecipanti." ),
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
        'state' => array( 'closed' ),
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'modified' => 'desc' )
    )
);


foreach( $sedute as $seduta )
{
    /** @var eZContentObjectAttribute[] $dataMap */
    $dataMap = $seduta->getObject()->attribute( 'data_map' );

    $cli->warning( $seduta->getObject()->attribute( 'name' ) );
    /** @var OCEditorialStuffHistory[] $history */
    $history = OCEditorialStuffHistory::fetchByHandler( $seduta->id(), 'history' );
    foreach( $history as $historyItem )
    {
        if( $historyItem->attribute( 'type' ) == 'updateobjectstate' )
        {
            $params = $historyItem->getParams();
            if ( $params['before_state_name'] == 'Convocata' && $params['after_state_name'] == 'In corso' )
            {
                $cli->notice( 'Inizio: ' . date( DATE_ISO8601, $historyItem->attribute( 'created_time' ) ) . ' ' .  $historyItem->attribute( 'created_time' ) );
                $cli->notice( 'Rilevato: ' . date( DATE_ISO8601, $seduta->dataOraEffettivaInizio() ) . ' ' .  $seduta->dataOraEffettivaInizio() );

                if ( $historyItem->attribute( 'created_time' ) !=  $seduta->dataOraEffettivaInizio() )
                {
                    $dataMap['orario_inizio_effettivo']->fromString( $historyItem->attribute( 'created_time' ) );
                    $dataMap['orario_inizio_effettivo']->store();
                    $cli->error( "Aggiornato" );
                }
            }
            if ( $params['before_state_name'] == 'In corso' && $params['after_state_name'] == 'Conclusa' )
            {
                $cli->notice( 'Fine: ' . date( DATE_ISO8601, $historyItem->attribute( 'created_time' ) ) . ' ' .  $historyItem->attribute( 'created_time' ) );
                $cli->notice( 'Rilevato: ' . date( DATE_ISO8601, $seduta->dataOraFine() ) . ' ' .  $seduta->dataOraFine() );
                if ( $historyItem->attribute( 'created_time' ) !=  $seduta->dataOraFine() )
                {
                    $dataMap['orario_conclusione_effettivo']->fromString( $historyItem->attribute( 'created_time' ) );
                    $dataMap['orario_conclusione_effettivo']->store();
                    $cli->error( "Aggiornato" );
                }
            }
        }
    }
}

$script->shutdown();