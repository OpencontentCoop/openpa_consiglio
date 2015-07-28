<?php
/** @var eZModule $module */
$module = $Params['Module'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ID'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$Result = array();
try
{
    /** @var Punto $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( $post->getObject()->attribute( 'can_read' ) && $post instanceof Punto )
    {
        if ( $http->hasPostVariable( 'BrowseActionName' ) && $http->postVariable( 'BrowseActionName' ) == 'SelectSeduta' )
        {
            $selectedArray = $http->postVariable( 'SelectedObjectIDArray' );
            $newSeduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $selectedArray[0] );
            $post->moveIn( $newSeduta );
            //$module->redirectTo( '/editorialstuff/edit/punto/' . $post->id() );
            $module->redirectTo( '/editorialstuff/edit/seduta/' . $newSeduta->id() );
        }
        elseif( $http->hasPostVariable( 'BrowseCancelButton' ) )
        {
            $module->redirectTo( '/editorialstuff/edit/seduta/' . $post->attribute( 'seduta_id' ) );
            return;
        }
        else
        {
            eZContentBrowse::browse(
                array(
                    'action_name' => 'SelectSeduta',
                    'selection' => 'single',
                    'return_type' => 'ObjectID',
                    'ignore_nodes_select' => array( $post->getSeduta()->getObject()->attribute( 'main_node_id' ) ),
                    'from_page' => '/consiglio/move/' . $factoryIdentifier . '/' . $id,
                    'class_array' => array( 'seduta' ),
                    'start_node' => OCEditorialStuffHandler::instance( 'seduta' )->getFactory()->creationRepositoryNode(),
                    'cancel_page' => '/editorialstuff/edit/punto/' . $id
                ),
                $module
            );

            return;
        }
    }
}
catch ( Exception $e )
{
    $configuration = $post->getFactory()->getConfiguration();
    if ( isset( $configuration['UiContext'] ) && is_string( $configuration['UiContext'] ) )
        $module->setUIContextName( $configuration['UiContext'] );
    $contentInfoArray = array( 'url_alias' => 'editorialstuff/dashboard' );
    $contentInfoArray['persistent_variable'] = array( 'show_path' => true, 'site_title' => 'Dashboard' );
    if ( is_array( $tpl->variable( 'persistent_variable' ) ) )
    {
        $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
    }
    if ( isset( $configuration['PersistentVariable'] ) && is_array( $configuration['PersistentVariable'] ) )
    {
        $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $configuration['PersistentVariable'] );
    }
    $tpl->setVariable( 'persistent_variable', false );
    $Result['content_info'] = $contentInfoArray;
    $Result['path']  = array();
    if ( $post instanceof Punto )
    {
        $seduta = $post->attribute( 'seduta' );
        $sedutaFactoryConfiguration = OCEditorialStuffHandler::instance( 'seduta' )->getFactory()->getConfiguration();
        if ( $seduta instanceof Seduta )
        {
            $sedutaObject = $seduta->getObject();
            if ( $sedutaObject instanceof eZContentObject )
            {
                $Result['path'][] =  array(
                    'text' => isset( $sedutaFactoryConfiguration['Name'] ) ? $sedutaFactoryConfiguration['Name'] : 'Dashboard',
                    'url' => 'editorialstuff/dashboard/seduta/'
                );
                $Result['path'][] =  array(
                    'text' => $sedutaObject->attribute( 'name' ),
                    'url' => 'editorialstuff/edit/seduta/' . $seduta->id()
                );
                $Result['path'][] =  array(
                    'text' => $post->getObject()->attribute( 'name' ),
                    'url' => 'editorialstuff/edit/punto/' . $post->id()
                );
            }
        }
    }
    $Result['content'] = $e->getMessage();
}