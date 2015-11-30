#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Simula voto." ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[votazione:][par:][fav:][con:][ast:][url:]',
    '',
    array(
        'votazione'  => 'Id votazione',
        'par'  => 'Numero di partecipanti votanti',
        'fav'  => 'Numero di favorevoli',
        'con'  => 'Numero di contrari',
        'ast'  => 'Numero di astenuti',
        'url' => 'endpoint url'
    )
);

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

/** @var Votazione $votazione */
$votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $options['votazione'] );
if ( !$votazione instanceof Votazione )
{
    throw new Exception( "Post {$this->Id} is not a valid Votazione" );
}
$partecipanti = $votazione->getSeduta()->partecipanti();
/** @var eZUser[] $presenti */
$presenti = $votazione->getResultHandler()->attribute( 'presenti' );
$p = empty( $options['par'] ) ? count( $partecipanti ) : $options['par'];
$f = empty( $options['fav'] ) ? 0 : $options['fav'];
$c = empty( $options['con'] ) ? 0 : $options['con'];
$a = empty( $options['ast'] ) ? 0 : $options['ast'];
$report = array();
$index = 0;
foreach( $presenti as $presente )
{
    $index++;
    if ( $index <= $p )
    {
        if ( $f > 0 )
        {
            $value = '1';
            $f--;
        }
        elseif( $c > 0 )
        {
            $value = '0';
            $c--;
        }
        elseif( $a > 0 )
        {
            $value = '2';
            $a--;
        }
        else
        {
            $values = array();
            if ( empty( $options['fav'] ) ) $values[] = "1";
            if ( empty( $options['con'] ) ) $values[] = "0";
            if ( empty( $options['ast'] ) ) $values[] = "2";
            $key = array_rand( $values );
            $value = $values[$key];
        }
        $name = $presente->attribute( 'object' )->attribute( 'name' );
        $cli->warning(
            'Voto ' . $value . ' per ' . $name . ' ', false
        );
        $report[$value][] = $name;
//        try
//        {
//            $votazione->addVoto( $value, $presente->id() );
//        }
//        catch( Exception $e )
//        {
//            $cli->error( $e->getMessage() );
//        }

        $url = $options['url'] ? $options['url'] : 'cal';
        $url = "http://{$url}/api/consiglio/v1/votazione/{$votazione->id()}";
        $credentials = "admin:gabricecek";
        $headers = array( "Authorization: Basic " . base64_encode( $credentials ) );
        $postString = "value={$value}&user_id={$presente->id()}";
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
            $cli->output( $data->result );
        }
    }
}
print_r( $report );


$script->shutdown();
