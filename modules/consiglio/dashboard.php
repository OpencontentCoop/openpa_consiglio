<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$factoryIdentifier = is_string( $Params['FactoryIdentifier'] ) ? $Params['FactoryIdentifier'] : 'seduta';

$currentUser = eZUser::currentUser();
eZDebug::writeNotice( $currentUser->hasAccessTo( 'consiglio', 'admin' ) );

if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( '/' );
    return;
}
else
{
    $Result = array();
    $tpl->setVariable( 'site_title', false );
    $tpl->setVariable( 'is_admin', false );
    if ( $currentUser->hasAccessTo( 'consiglio', 'manage' ) )
    {
        $tpl->setVariable( 'is_admin', true );
    }

    $Result['content'] = $tpl->fetch( 'design:consiglio/dashboard.tpl' );
    $Result['node_id'] = 0;

    $contentInfoArray = array( 'url_alias' => 'consiglio/dashboard' );
    $contentInfoArray['persistent_variable'] = array(
        'show_path' => false,
        'site_title' => 'Dashboard',
        'top_menu' => true,
        'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
    );
    if ( is_array( $tpl->variable( 'persistent_variable' ) ) )
    {
        $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}