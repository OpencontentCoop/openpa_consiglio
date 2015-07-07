<?php

class PuntoFactory extends OCEditorialStuffPostNotifiableFactory
{

    /**
     * @var Seduta
     */
    protected $seduta;

    public function __construct( $configuration )
    {
        $this->configuration = $configuration;
        $this->configuration['CreationRepositoryNode'] = 0;
        $this->configuration['RepositoryNodes'] = array();
        if( isset( $this->configuration['RuntimeParameters']['seduta'] ) )
        {
            $sedutaID = $this->configuration['RuntimeParameters']['seduta'];
            $this->setSeduta( $sedutaID );
        }
    }

    public function setSeduta( $sedutaID )
    {
        if ( $sedutaID instanceof Seduta )
        {
            $this->seduta = $sedutaID;
        }
        else
        {
            try
            {
                $this->seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId(
                    $sedutaID
                );
            }
            catch ( Exception $e )
            {
                eZDebug::writeError( $e->getMessage(), __METHOD__ );
            }
        }
        if ( $this->seduta instanceof Seduta )
        {
            $sedutaNodeId = $this->seduta->getObject()->attribute( 'main_node_id' );
            $this->configuration['CreationRepositoryNode'] = $sedutaNodeId;
            $this->configuration['RepositoryNodes'] = array( $sedutaNodeId );
        }
    }

    /**
     * @return array[]
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = array(
            'solr_identifier' => "extra_orario_i",
            'object_property' => 'orario',
            'attribute_identifier' => 'orario',
            'index_extra' => true,
            'index_plugin_call_function' => 'indexOrario'
        );
        return $fields;
    }


    public function getSeduta()
    {
        return $this->seduta;
    }

    /**
     * @param OCEditorialStuffPostInterface $post
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState(
        OCEditorialStuffPostInterface $post,
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        if ( $post instanceof Punto )
        {
            if ( $beforeState->attribute( 'identifier' ) == 'draft'
                 && $afterState->attribute( 'identifier' ) == 'published'
            )
            {
                if ( $post->is( '_public' ) )
                {
                    $post->createNotificationEvent( 'create' );
                }
            }
        }
    }

    public function instancePost( $data )
    {
        return new Punto( $data, $this );
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/punto';
    }

    public function editModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
        $tpl = $this->editModuleResultTemplate( $currentPost, $parameters, $handler, $module );

        $Result = array();
        $contentInfoArray = array( 'url_alias' => 'editorialstuff/dashboard' );
        $contentInfoArray['persistent_variable'] = array( 'show_path' => true, 'site_title' => 'Dashboard' );
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        }
        $tpl->setVariable( 'persistent_variable', false );
        $Result['content_info'] = $contentInfoArray;
        $Result['content'] = $tpl->fetch( "design:{$this->getTemplateDirectory()}/edit.tpl" );
        $Result['path']  = array();
        if ( $currentPost instanceof Punto )
        {
            $seduta = $currentPost->attribute( 'seduta' );
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
                }
            }
            $Result['path'][] = array( 'url' => false, 'text' => $currentPost->getObject()->attribute( 'name' ) );
        }
        return $Result;
    }

    /**
     * @return array[] array( 'type' => array( 'handler_method' => <methodName> ) )
     */
    public function notificationEventTypesConfiguration()
    {
        return array(
            'create' => array( 'handler_method' => 'handleCreateNotification' ),
            'update' => array( 'handler_method' => 'handleUpdateNotification' ),
            'add_file' => array( 'handler_method' => 'handleAddFileNotification' ),
            'update_file' => array( 'handler_method' => 'handleUpdateFileNotification' )
        );
    }
}