#!/usr/bin/env php
<?php
/**
 *
 */

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Fix presenze." ),
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
    $cli->warning( $seduta->getObject()->attribute( 'name' ) );
    $seduta->storePresenti();
}

$script->shutdown();