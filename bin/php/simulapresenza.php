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
    '[seduta:][partecipante:][inout:][type:][url:]',
    '',
    array(
        'seduta' => 'Id seduta',
        'partecipante' => 'Id partecipante',
        'inout' => '0 = assente 1 = presente 2 = random',
        'type' => 'manual|beacons|checkin',
        'url' => 'endpoint url'
    )
);

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
try
{

    $url = $options['url'] ? $options['url'] : 'cal';
    $selectPartecipante = intval( $options['partecipante'] );
    $inout = $options['inout'] !== null ? intval($options['inout']) : 1;
    $type = $options['type'] ? $options['type'] : 'checkin';

    /** @var Seduta $seduta */
    $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $options['seduta'] );
    if ( !$seduta instanceof Seduta )
    {
        throw new Exception( "Post {$this->Id} is not a valid Votazione" );
    }

    foreach ( $seduta->partecipanti() as $partecipante )
    {
        if ( $selectPartecipante > 0 )
        {
            if ($selectPartecipante  != $partecipante->id())
                continue;
        }

        if ( $inout == 2 )
            $inout = array_rand( array( 0, 1 ) );

        $url = "http://{$url}/api/consiglio/v1/seduta/{$seduta->id()}/presenza";
        $credentials = "admin:gabricecek";
        $headers = array( "Authorization: Basic " . base64_encode( $credentials ) );
        $postString = "in_out={$inout}&type={$type}&user_id={$partecipante->id()}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_POST, strlen( $postString ) );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postString );
        $data = json_decode( curl_exec( $ch ) );
        if ( isset($data->error_message))
        {
            $cli->error($data->error_message);
        }
        else
        {
            $cli->output( $partecipante->getObject()->attribute( 'name' ) . ' ', false );
            $cli->output( $data->presenza->id );
        }

    }

}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();
