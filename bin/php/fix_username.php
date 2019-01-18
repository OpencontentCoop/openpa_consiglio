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

    $users = $db->arrayQuery('SELECT * FROM ezuser');        
    foreach ($users as $user) {

        $id = (int)$user['contentobject_id'];
        
        // $login = $user['login'];
        // $login = trim($login);
        // $email = $user['email'];
        // $email = trim($email);
        // if ($user['login'] !== $login || $user['email'] !== $email){            
        //     $cli->warning("UPDATE ezuser SET login = '$login', email = '$email' WHERE contentobject_id = $id");            
        // }

        $object = eZContentObject::fetch((int)$user['contentobject_id']);
        if (!$object){
            $cli->error($user);
        }else{
            $cli->output($object->attribute('name'));
            $dataMap = $object->dataMap();
            foreach ($dataMap as $identifier => $attribute) {
                switch ($identifier) {
                    case 'cognome':
                    case 'nome':
                        $value = $attribute->toString();
                        if (trim($value) != $value){
                            $cli->warning("  - Fix $identifier");
                            $attribute->fromString(trim($value));
                            $attribute->store();
                        }
                        break;
                    case 'user_account':                        
                        $dataText = json_decode($attribute->attribute('data_text'));
                        $fixedDataText = array();
                        $isModified = false;
                        foreach ($dataText as $key => $value) {
                            if (!is_integer($value) && $value !== trim($value)){
                                $isModified = true;
                            }                                                  
                            $fixedDataText[$key] = is_integer($value) ? $value : trim($value);      
                        }
                        if ($isModified){
                            $cli->warning("  - Fix $identifier");                            
                            $attribute->setAttribute('data_text', json_encode($fixedDataText));                        
                            $attribute->store();                            
                        }                        
                        break;

                    default:
                        # code...
                        break;
                }                
            }

            $class = $object->contentClass();
            $object->setName($class->contentObjectName($object));

            eZSearch::addObject($object, 1);
            eZContentCacheManager::clearObjectViewCacheIfNeeded($object->attribute( 'id' ));
        }
    }
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();