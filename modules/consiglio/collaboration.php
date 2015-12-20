<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$areaId = intval( $Params['AreaId'] );
$action = $Params['Action'];

$Offset = $Params['Offset'];
if ( isset( $Params['UserParameters'] ) )
    $UserParameters = $Params['UserParameters'];
else
    $UserParameters = array();

if ( $Offset )
    $Offset = (int)$Offset;

$viewParameters = array( 'offset' => $Offset );
$viewParameters = array_merge( $viewParameters, $UserParameters );
$tpl->setVariable( 'view_parameters', $viewParameters );

$area = null;

try
{
    $listAreas = array();
    if ( $areaId == 0 )
    {
        $listAreas = OCEditorialStuffHandler::instance( 'areacollaborativa' )->fetchItems(
            array( 'limit' => 100, 'offset' => 0, 'sort' => array( 'name' => 'asc' ) )
        );
        if ( count( $listAreas ) == 1 )
        {
            $module->redirectTo( 'consiglio/collaboration/' . $listAreas[0]->id() );
            return;
        }
        else
        {
            $tpl->setVariable( 'areas', $listAreas );
            $Result['content'] = $tpl->fetch( 'design:consiglio/collaboration/select_area.tpl' );
        }
    }
    elseif ( $areaId > 0 )
    {
        $area = AreaCollaborativaFactory::fetchById( $areaId );
        if ( $area instanceof AreaCollaborativa )
        {
            $helper = new OpenPAConsiglioCollaborationHelper( $area );
            if ( $helper->canReadArea() )
            {
                $selectedRoom = false;
                $error = false;

                if ( strpos( $action, 'room-' ) === 0 )
                {
                    $selectedRoom = eZContentObjectTreeNode::fetch(
                        str_replace( 'room-', '', $action )
                    );
                    if ( !$selectedRoom instanceof eZContentObjectTreeNode )
                    {
                        throw new Exception( "La tematica non esiste" );
                    }
                    if ( $selectedRoom->attribute( 'parent_node_id' ) != $area->mainNode()->attribute( 'main_node_id' ) )
                    {
                        throw new Exception( "Errore" );
                    }
                    if ( $selectedRoom->attribute( 'is_hidden' ) )
                    {
                        throw new Exception( "Area {$selectedRoom->attribute( 'name' )} non accessibile" );
                    }
                }
                elseif ( is_string( $action ) )
                {
                    $helper->executeAction( $action );
                    $module->redirectTo( 'consiglio/collaboration/' . $areaId . $helper->redirectParams );
                    return;
                }

                $tpl->setVariable( 'error', $error );
                $tpl->setVariable( 'can_participate', $helper->canParticipate() );
                $tpl->setVariable( 'area_node', $helper->getArea() );
                $tpl->setVariable( 'area', $area );
                $tpl->setVariable( 'room', $selectedRoom );
                $tpl->setVariable( 'area_users', $helper->getAreaUsers() );
                $tpl->setVariable( 'area_rooms', $helper->getAreaRooms() );
                $Result['content'] = $tpl->fetch( 'design:consiglio/collaboration/area.tpl' );
            }
            else
            {
                throw new Exception( "Non hai permessi per poter leggere questo contenuto" );
            }
        }
        else
        {
            throw new Exception( "Area non trovata" );
        }
    }

}
catch ( Exception $e )
{
    $error = $e->getMessage();
    $listAreas = OCEditorialStuffHandler::instance( 'areacollaborativa' )->fetchItems(
        array( 'limit' => 100, 'offset' => 0, 'sort' => array( 'name' => 'asc' ) )
    );
    $tpl->setVariable( 'areas', $listAreas );
    $tpl->setVariable( 'error', $error );
    $Result['content'] = $tpl->fetch( 'design:consiglio/collaboration/select_area.tpl' );
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
    $contentInfoArray['persistent_variable'] = array_merge(
        $contentInfoArray['persistent_variable'],
        $tpl->variable( 'persistent_variable' )
    );
}
$tpl->setVariable( 'site_title', $contentInfoArray['persistent_variable']['site_title'] );
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array(
    array( 'text' => 'Area collaborativa', 'url' => $contentInfoArray['url_alias'] )
);
if ( $area instanceof AreaCollaborativa )
{
    $Result['path'][] = array(
        'text' => $area->getObject()->attribute( 'name' ),
        'url' => false
    );
}