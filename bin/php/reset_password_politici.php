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
$db = eZDB::instance();
$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );

$ignoreIdlist = array(
    163, //ceschi
    201, //visetti
);

try
{
    $politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
        array(
            'limit' => 1000,
            'offset' => 0,
            'sort' => array( 'name' => 'asc' )
        )
    );

    $users = $db->arrayQuery('SELECT * FROM ezuser');        
    foreach ($politici as $politico) {        
        if (!in_array($politico->id(), $ignoreIdlist)){
            $username = eZUser::fetch($politico->id())->attribute('login');
            $fakePassword = $username . '1';

            $cli->output($politico->getObject()->attribute('name'));
            $cli->output("    Username: $username");
            $cli->output("    Password: $fakePassword");
            $cli->output("    Link per cambiare la password: https://riuniamoci.cooperazionetrentina.it/user/password");
            $cli->output();            

            eZUserOperationCollection::password($politico->id(), $fakePassword);
        }
    }
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();