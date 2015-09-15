#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Forza presenza beacons." ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[seduta:][not:]',
    '',
    array(
        'seduta'  => 'Id seduta',
        'not'  => 'Escludi'
    )
);

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

$not = empty( $options['not'] ) ? null : explode( ',', $options['not'] );
/** @var Seduta $seduta */
$seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $options['seduta'] );
$index = 1;
if ( $seduta instanceof Seduta )
{
    foreach( $seduta->partecipanti() as $partecipante )
    {
        $do = false;        
        if ( is_array( $not ) )
        {
            if ( in_array( $partecipante->id(), $not ) )
            {
                $do = false;
            }
            else
            {
                $do = true;
            }
        }
        else
        {
            $do = true;
        }
        if ( $do )
        {
            $cli->warning( $index . ' ' . $partecipante->getObject()->attribute( 'name' ) );
            $this->addPresenza( 1, 'beacons', $partecipante->id() );
            $index++;
        }
    }
}



$script->shutdown();
