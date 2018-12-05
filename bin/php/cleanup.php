#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
        'description' => ( "Svuota i dati di presenza, voto, notifications" ),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true
    )
);

$script->startup();

$options = $script->getOptions();

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
try
{
    $db = eZDB::instance();
    $db->query('TRUNCATE openpa_consiglio_presenza');    
    $db->query('TRUNCATE openpaconsiglionotificationitem');
    $db->query('TRUNCATE openpa_consiglio_voto');
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();