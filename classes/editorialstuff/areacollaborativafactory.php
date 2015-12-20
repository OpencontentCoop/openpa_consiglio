<?php


class AreaCollaborativaFactory extends OpenPAConsiglioDefaultFactory
{
    protected static $container;

    public function __construct( $configuration )
    {
        parent::__construct( $configuration );
        $this->configuration['PersistentVariable'] = array(
            'top_menu' => true,
            'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
        );
        $this->configuration['CreationRepositoryNode'] = self::createCollaborationContainerIfNeeded()->attribute( 'main_node_id' );
        $this->configuration['RepositoryNodes'] = array( self::createCollaborationContainerIfNeeded()->attribute( 'main_node_id' ) );
    }

    public function instancePost( $data )
    {
        return new AreaCollaborativa( $data, $this );
    }

    /**
     * @param $id
     *
     * @return AreaCollaborativa
     */
    public static function fetchById( $id )
    {
        return OCEditorialStuffHandler::instance( 'areacollaborativa' )->fetchByObjectId( $id );
    }

    /**
     * @param eZUser $referente
     *
     * @return AreaCollaborativa[]
     * @throws Exception
     */
    public static function fetchByReferente( eZUser $referente )
    {
        $search = OCEditorialStuffHandler::instance( 'areacollaborativa' )->fetchItems( array(
            'limit' => 100,
            'offset' => 0,
            'filters' => array( 'submeta_politici___id_si:' . $referente->id() )
        ), array() );
        return $search;
    }

    /**
     * @return eZContentObject
     */
    protected static function createCollaborationContainerIfNeeded()
    {
        if ( self::$container === null )
        {
            $remoteId = 'openpa_consiglio_collaboration_container';
            self::$container = eZContentObject::fetchByRemoteID(
                'openpa_consiglio_collaboration_container'
            );
            if ( !self::$container instanceof eZContentObject )
            {
                $params = array(
                    'remote_id' => $remoteId,
                    'class_identifier' => 'folder',
                    'parent_node_id' => eZINI::instance( 'content.ini' )->variable(
                        'NodeSettings',
                        'MediaRootNode'
                    ),
                    'attributes' => array(
                        'name' => 'Aree collaborative'
                    )
                );
                self::$container = eZContentFunctions::createAndPublishObject( $params );
            }
        }
        return self::$container;
    }

}