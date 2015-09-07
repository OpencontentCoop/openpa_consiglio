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

$options = $script->getOptions( '[votazione:][par:][fav:][con:][ast:]',
    '',
    array(
        'votazione'  => 'Id votazione',
        'par'  => 'Numero di partecipanti votanti',
        'fav'  => 'Numero di favorevoli',
        'con'  => 'Numero di contrari',
        'ast'  => 'Numero di astenuti',
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
/** @var eZUser[] $presenti */
$presenti = $votazione->getResultHandler()->attribute( 'presenti' );
$p = empty( $options['par'] ) ? count( $partecipanti ) : $options['par'];
$f = empty( $options['fav'] ) ? 0 : $options['fav'];
$c = empty( $options['con'] ) ? 0 : $options['con'];
$a = empty( $options['ast'] ) ? 0 : $options['ast'];

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
            $values = array( '0', '1', '2' );
            $value = $values[mt_rand( 0, count( $values ) - 1 )];
        }
        $cli->warning(
            'Voto ' . $value . ' per ' . $presente->attribute( 'contentobject' )->attribute( 'name' )
        );
        $votazione->addVoto( $value, $presente->id() );
    }
}


$script->shutdown();
