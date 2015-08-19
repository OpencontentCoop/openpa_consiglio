#!/usr/bin/env php
<?php
/**
 *
 */

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Crea il tracciato dei premi assegnati in data odierna." ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );


$ini = eZINI::instance();
// Get user's ID who can remove subtrees. (Admin by default with userID = 14)
$userCreatorID = $ini->variable( "UserSettings", "UserCreatorID" );
$user = eZUser::fetch( $userCreatorID );
if ( !$user )
{
    $cli->error( "Script Error!\nCannot get user object by userID = '$userCreatorID'.\n(See site.ini[UserSettings].UserCreatorID)" );
    $script->shutdown( 1 );
}
eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );

$parentID = 2;
$limit = 5;

$params = array(
    'ClassFilterType'  => 'include',
    'ClassFilterArray' => array( 'punto' )
);

$count  = eZContentObjectTreeNode::subTreeCountByNodeID( $params, $parentID );

//$cli->output( 'Premi assegnati in data '.$date.': ' .  $count);
$offset = 0;
$i = 1;
$params['Limit'] = $limit;

while( $offset <= $count )
{
    $params[ 'Offset' ] = $offset;
    $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentID );
    foreach( $nodes as $node )
    {
        $cli->output( $i . ': ' .$node->attribute( 'name' ) );
        $dataMap = $node->dataMap();
        $dataMap['oggetto']->fromString($dataMap['oggetto1']->toString());
        $dataMap['oggetto']->store();

        $i++;
    }
    // Increment the offset until we've gone through every user
    $offset += $limit;
}


$script->shutdown();

?>
