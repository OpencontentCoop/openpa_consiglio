<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$settingsProvider = OpenPAConsiglioSettings::instance();
$settings = $settingsProvider->availableGlobalSettings();
//echo '<pre>';print_r($_POST);die();
if ($http->hasPostVariable('StoreGlobalSettings')) {
    foreach ($settings as $identifier => $setting) {
        if ($http->hasPostVariable('GlobalSettings_' . $identifier)) {
            $value = $http->postVariable('GlobalSettings_' . $identifier, null);
            if($setting['type'] == 'checkbox'){
                $value = 1;
            }
            $settingsProvider->storeGlobalSetting($identifier, $value);
        }elseif($setting['type'] == 'checkbox'){
            $settingsProvider->storeGlobalSetting($identifier, 0);
        }
    }
    eZCache::clearByTag('template');
    $module->redirectTo('consiglio/settings');
}

$tpl->setVariable('settings', $settingsProvider->listGlobalSettings());

$Result = array();
$Result['content'] = $tpl->fetch('design:consiglio/settings.tpl');
