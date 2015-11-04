<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$interval = new OpenPAConsiglioGettoniInterval( $Params['Interval'] );
$userId = intval( $Params['UserId'] );

$access = eZUser::currentUser()->hasAccessTo( 'consiglio', 'admin' );
$isAdmin = $access['accessWord'] == 'yes';
$currentUser = $isAdmin ? eZUser::fetch( $userId ) : eZUser::currentUser();

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

    if ( !$currentUser instanceof eZUser )
    {
        $tpl->setVariable( 'politici', $helper->getPolitici() );
        $tpl->setVariable( 'sedute', $helper->getSedute( $interval ) );
        $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report_admin_all.tpl' );
    }
    else
    {
        $tpl->setVariable( 'selected_user', $currentUser );

        /** @var Politico $politico */
        $politico = OCEditorialStuffHandler::instance( 'politico' )->getFactory()->instancePost(
            array( 'object_id' => $currentUser->id() )
        );
        $helper->setPolitico( $politico );
        $tpl->setVariable( 'politico', $politico );

        $sedute = $helper->getSedute( $interval );
        $tpl->setVariable( 'sedute', $sedute );

//        $gettoni = $helper->getGettoni();
//        $tpl->setVariable( 'gettoni', $gettoni );

        if ( $isAdmin )
        {
            $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report_admin.tpl' );
        }
        else
        {
            $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report.tpl' );
        }
    }
}

$Result['node_id'] = 0;

$contentInfoArray = array(
    'url_alias' => 'consiglio/gettoni/' . $userId,
    'site_title' => 'Gettoni'
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => false,
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
$Result['path'] = array();