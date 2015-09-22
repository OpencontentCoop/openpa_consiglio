<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$settings = OpenPAConsiglioDefaultFactory::availableGlobalSettings();

if ( $http->hasPostVariable( 'StoreGlobalSettings' ) )
{
    foreach( array_keys( $settings ) as $identifier )
    {
        OpenPAConsiglioDefaultFactory::storeGlobalSetting( $identifier, $http->postVariable( 'GlobalSettings_' . $identifier, null ) );
    }
    $module->redirectTo( 'consiglio/settings' );
}

$tpl->setVariable( 'settings', OpenPAConsiglioDefaultFactory::listGlobalSettings() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:consiglio/settings.tpl' );
