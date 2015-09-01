#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Sottoscrizione di tutti i politici a tutte le materie." ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

$materie = OCEditorialStuffHandler::instance( 'materia' )->fetchItems(
    array(
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'name' => 'asc' )
    )
);

$politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
    array(
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'name' => 'asc' )
    )
);

$type = 'materia/like';
foreach( $politici as $politico )
{
    foreach( $materie as $materia )
    {
        $rule = OCEditorialStuffNotificationRule::create( $type, $materia->id(), $politico->id() );
        $rule->store();
    }
}

$script->shutdown();
