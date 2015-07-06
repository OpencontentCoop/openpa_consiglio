<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$factoryIdentifier = is_string( $Params['FactoryIdentifier'] ) ? $Params['FactoryIdentifier'] : 'seduta';

$currentUser = eZUser::currentUser();


if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( '/' );
    return;
}
else
{

    if ( $currentUser->hasAccessTo( 'consiglio', 'manage' ) )
    {
        $module->redirectTo( 'editorialstuff/dashboard/seduta' );
    }
    else
    {
        $Result = array();
        $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        $Result['content'] = $tpl->fetch( 'design:consiglio/dashboard/participant.tpl' );
        $Result['node_id'] = 0;

        $contentInfoArray = array( 'url_alias' => 'consiglio/dashboard' );
        $contentInfoArray['persistent_variable'] = false;
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
    }
}