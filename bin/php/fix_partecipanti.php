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
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'name' => 'asc' )
    )
);


foreach( $sedute as $seduta )
{
    $seduta->setPartecipanti();
}

$script->shutdown();