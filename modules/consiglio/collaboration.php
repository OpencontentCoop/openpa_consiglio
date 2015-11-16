<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$referenteId = intval( $Params['ReferenteId'] );
$action = $Params['Action'];
$referente = eZUser::fetch( $referenteId );

if ( $referenteId == 0 && eZUser::currentUser()->contentObject()->attribute( 'class_identifier' ) == 'politico' )
{
    $referente = eZUser::currentUser();
}

$Offset = $Params['Offset'];
if ( isset( $Params['UserParameters'] ) )
    $UserParameters = $Params['UserParameters'];
else
    $UserParameters = array();

if ( $Offset ) $Offset = (int) $Offset;

$viewParameters = array( 'offset' => $Offset );
$viewParameters = array_merge( $viewParameters, $UserParameters );
$tpl->setVariable( 'view_parameters', $viewParameters );

$helper = new OpenPAConsiglioCollaborationHelper();
if ( !$referente instanceof eZUser )
{
    $listAreas = OpenPAConsiglioCollaborationHelper::listAccessAreas();
    if ( count( $listAreas ) == 1 )
    {
        $module->redirectTo( 'consiglio/collaboration/' . $listAreas[0]->attribute( 'object' )->attribute( 'owner_id' ) );
        return false;
    }
    else
    {
        $tpl->setVariable( 'areas', $listAreas );
        $Result['content'] = $tpl->fetch( 'design:consiglio/collaboration/select_referente.tpl' );
    }
}
elseif( $referente instanceof eZuser )
{
    $helper->setReferente( $referente );
    if ( $helper->canReadArea() )
    {
        $selectedTag = false;

        if( strpos( $action, 'tag-' ) === 0 )
        {
            $selectedTag = eZContentObjectTreeNode::fetch( str_replace( 'tag-', '', $action ) );
        }
        elseif ( is_string( $action ) && $helper->executeAction( $action ) )
        {
            $module->redirectTo( 'consiglio/collaboration/' . $referente->id() . $helper->redirectParams );
            return;
        }

        $tpl->setVariable( 'referente', $referente->contentObject() );
        $tpl->setVariable( 'area', $helper->getArea() );
        $tpl->setVariable( 'tag', $selectedTag );
        $tpl->setVariable( 'area_users', $helper->getAreaUsers() );
        $tpl->setVariable( 'area_tags', $helper->getAreaTags() );
        $Result['content'] = $tpl->fetch( 'design:consiglio/collaboration/area.tpl' );
    }
    else
    {
        $module->redirectTo( 'consiglio/collaboration/' );
        return false;
    }
}

$Result['node_id'] = 0;

$contentInfoArray = array(
    'url_alias' => 'consiglio/collaboration',
    'site_title' => 'Area collaborativa'
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true,
    'site_title' => 'Area collaborativa',
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
    array( 'text' => 'Area collaborativa', 'url' => $contentInfoArray['url_alias'] )
);
if ( $referente instanceof eZUser )
    $Result['path'][] = array( 'text' => $referente->contentObject()->attribute( 'name' ), 'url' => false );