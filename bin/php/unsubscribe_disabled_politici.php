#!/usr/bin/env php
<?php
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Unsubscribe" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

/** @var Politico[] $politici */
$politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
    array(
        'limit' => 1000,
        'offset' => 0,
        'sort' => array( 'name' => 'asc' )
    )
);

$remove = array();

$i = 1;
foreach ($politici as $politico) {
    $cli->output($i. ' ' . $politico->getObject()->attribute('name') . ' ', false);
    $is_in = $politico->attribute('is_in');
    $doRemove = true;
    foreach ($is_in as $name => $value) {
        if ($value){
            $cli->warning($name . ' ', false);
            $doRemove = false;
        }
    }
    $cli->output();
    $i++;

    if ($doRemove){
        $remove[] = $politico;
    }
}

foreach ($remove as $politico) {
    $hasNotificationRule = OCEditorialStuffNotificationRule::fetchListCount(null, $politico->getObject()->attribute('id'), null);
    if ($hasNotificationRule > 0){
        $cli->error('Rimuovo sottoscrizioni per ' . $politico->getObject()->attribute('name'));
        OCEditorialStuffNotificationRule::removeByUserID($politico->getObject()->attribute('id'));
    }
}

$script->shutdown();