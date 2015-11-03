<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$userId = intval( $Params['UserId'] );

$access = eZUser::currentUser()->hasAccessTo( 'consiglio', 'admin' );
$isAdmin = $access['accessWord'] == 'yes';
$currentUser = $isAdmin ? eZUser::fetch( $userId ) : eZUser::currentUser();

$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'consiglio/gettoni/' . $userId );
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
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();

if ( !$currentUser instanceof eZUser )
{
    $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/select_user.tpl' );
}
else
{
    $tpl->setVariable( 'selected_user', $currentUser );
    $Result['content'] = $tpl->fetch( 'design:consiglio/gettoni/report.tpl' );
}
