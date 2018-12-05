#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
        'description' => ( "Trim username" ),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true
    )
);

$script->startup();

$options = $script->getOptions();

$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
try
{
    
    // eZDB::instance()->begin();

    // $query = 'DELETE FROM ezuser ' .
    //          'WHERE contentobject_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject );';
    // eZDB::instance()->query($query);

    // $query = 'DELETE FROM ezuser_accountkey ' .
    //          'WHERE user_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject );';
    // eZDB::instance()->query($query);

    // $query = 'DELETE FROM ezuser_setting ' .
    //          'WHERE user_id NOT IN ( SELECT DISTINCT ezcontentobject.id FROM ezcontentobject );';
    // eZDB::instance()->query($query);

    // eZDB::instance()->commit();


    // $db = eZDB::instance();
    // $users = $db->arrayQuery('SELECT * FROM ezuser');        
    // foreach ($users as $user) {
        
    //     $id = $user['contentobject_id'];

    //     $login = $user['login'];
    //     $login = trim($login);
    //     $email = $user['email'];
    //     $email = trim($email);
        
    //     $cli->output($login . ' ' .  $email);
    //     $db->query("UPDATE ezuser SET login = '$login', email = '$email' WHERE contentobject_id = $id");
        
    // }
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();