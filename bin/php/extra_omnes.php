#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
        'description' => ( "Simula voto." ),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true
    )
);

$script->startup();

$options = $script->getOptions(
    '[seduta:][run]',
    '',
    array(
        'seduta' => 'Id seduta'        
    )
);

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
try
{
    $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $options['seduta'] );
    if ( !$seduta instanceof Seduta )
    {
        throw new Exception( "Post {$this->Id} is not a valid Votazione" );
    }

    $createdTime = $seduta->dataOraFine();

    $cli->warning($seduta->attribute('object')->attribute('name'));
    $cli->warning($seduta->dataOraFine(DATE_ISO8601));

    if ($options['run']){
        foreach ( $seduta->partecipanti(false) as $userId )
        {
            $presenza = new OpenPAConsiglioPresenza( array(
                'user_id' => $userId,
                'seduta_id' => intval( $seduta->id() ),
                'type' => 'checkin',
                'in_out' => 0,
                'created_time' => intval( $createdTime )
            ));
            $presenza->store();
            $presenza = new OpenPAConsiglioPresenza( array(
                'user_id' => $userId,
                'seduta_id' => intval( $seduta->id() ),
                'type' => 'beacons',
                'in_out' => 0,
                'created_time' => intval( $createdTime )
            ));
            $presenza->store();
            $presenza = new OpenPAConsiglioPresenza( array(
                'user_id' => $userId,
                'seduta_id' => intval( $seduta->id() ),
                'type' => 'manual',
                'in_out' => 0,
                'created_time' => intval( $createdTime )
            ));
            $presenza->store();
            $cli->warning('.', false);
        }
        $cli->warning();
    }
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();