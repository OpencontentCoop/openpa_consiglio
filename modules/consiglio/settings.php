<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$settingsProvider = OpenPAConsiglioSettings::instance();
$settings = $settingsProvider->availableGlobalSettings();

if ( $http->hasPostVariable( 'StoreGlobalSettings' ) )
{
    foreach( array_keys( $settings ) as $identifier )
    {
        $settingsProvider->storeGlobalSetting( $identifier, $http->postVariable( 'GlobalSettings_' . $identifier, null ) );
    }
    $module->redirectTo( 'consiglio/settings' );
}

$tpl->setVariable( 'settings', $settingsProvider->listGlobalSettings() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:consiglio/settings.tpl' );
