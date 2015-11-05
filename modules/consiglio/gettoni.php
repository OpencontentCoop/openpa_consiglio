<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$interval = new OpenPAConsiglioGettoniInterval( $Params['Interval'] );
$userId = intval( $Params['UserId'] );
$action = $Params['Action'];
$actionParameter = $Params['ActionParameter'];

$access = eZUser::currentUser()->hasAccessTo( 'consiglio', 'admin' );
$isAdmin = $access['accessWord'] == 'yes';
$currentSelectedUser = $isAdmin ? eZUser::fetch( $userId ) : eZUser::currentUser();

$helper = new OpenPAConsiglioGettoniHelper();

if ( !$interval->isValid )
{
    $tpl->setVariable( 'start', $interval->startYear );
    $tpl->setVariable( 'end', $interval->endYear );
    $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/select_interval.tpl' );
}
else
{
    $tpl->setVariable( 'interval', $interval->intervalString );
    $tpl->setVariable( 'interval_name', $interval->intervalName );

    if ( !$currentSelectedUser instanceof eZUser )
    {
        $tpl->setVariable( 'politici', $helper->getPolitici() );
        $tpl->setVariable( 'sedute', $helper->getSedute( $interval ) );
        $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report_all.tpl' );
    }
    else
    {
        $tpl->setVariable( 'selected_user', $currentSelectedUser );

        /** @var Politico $politico */
        $politico = OCEditorialStuffHandler::instance( 'politico' )->getFactory()->instancePost(
            array( 'object_id' => $currentSelectedUser->id() )
        );
        $helper->setPolitico( $politico );
        $tpl->setVariable( 'politico', $politico );

        $sedute = $helper->getSedute( $interval );
        $tpl->setVariable( 'sedute', $sedute );

        if ( $action )
        {
            $tpl->setVariable( 'action', $action );
            OpenPAConsiglioGettoniHelper::executeAction( $action, $actionParameter, $currentSelectedUser, $politico, $interval );
        }

        $tpl->setVariable( 'iban', eZPreferences::value( 'consiglio_gettoni_iban', $currentSelectedUser ) );
        $tpl->setVariable( 'trattenute', eZPreferences::value( 'consiglio_gettoni_trattenute', $currentSelectedUser ) );

            $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report.tpl' );
    }
}

$Result['node_id'] = 0;

$contentInfoArray = array(
    'url_alias' => 'consiglio/gettoni',
    'site_title' => 'Gettoni'
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true,
    'site_title' => 'Gettoni',
    'top_menu' => true,
    'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
);
if ( is_array( $tpl->variable( 'persistent_variable' ) ) )
{
    $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
}
$tpl->setVariable( 'site_title', $contentInfoArray['persistent_variable']['site_title'] );
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array(
    array( 'text' => 'Gettoni di presenza', 'url' => $contentInfoArray['url_alias'] )
);
if ( $interval->isValid )
    $Result['path'][] = array( 'text' => $interval->intervalName, 'url' => ( isset( $politico ) && $isAdmin ) ? $contentInfoArray['url_alias'] . '/' . $interval->intervalString : false );
if ( isset( $politico ) && $isAdmin )
    $Result['path'][] = array( 'text' => $politico->getObject()->attribute( 'name' ), 'url' => false );
