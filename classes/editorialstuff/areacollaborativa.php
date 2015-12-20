<?php


class AreaCollaborativa extends OCEditorialStuffPost
{

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'group';
        $attributes[] = 'main_node';
        $attributes[] = 'politici_id_list';
        $attributes[] = 'referenti_id_list';

        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'group' )
            return $this->group();

        if ( $property == 'main_node' )
            return $this->mainNode();

        if ( $property == 'politici_id_list' )
            return $this->politiciIdList();

        if ( $property == 'referenti_id_list' )
            return $this->referentiIdList();

        return parent::attribute( $property );
    }

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {

    }

    /**
     * @return eZContentObjectTreeNode
     */
    public function mainNode()
    {
        return $this->object->attribute( 'main_node' );
    }

    /**
     * @return eZContentObject
     */
    public function group()
    {
        return self::createCollaborationGroup( $this->getObject() );
    }

    public function politiciIdList()
    {
        $data = array();
        if ( isset( $this->dataMap['politici'] ) )
        {
            $data = explode( '-', $this->dataMap['politici']->toString() );
        }
        return $data;
    }

    public function referentiIdList()
    {
        $data = array();
        $referenti = OCEditorialStuffHandler::instance( 'referentelocale' )->fetchItems( array(
            'limit' => 100,
            'offset' => 0,
            'filters' => array( 'meta_path_si:' . $this->group()->attribute( 'main_node_id' ) )
        ), array() );

        foreach( $referenti as $referente )
            $data[] = $referente->id();

        return $data;
    }

    public function rooms( $showHidden = false )
    {
        $hidden = array();
        if ( $showHidden )
            $hidden = array( 'IgnoreVisibility' => true );
        return $this->mainNode()->subTree(
            array_merge( array(
                'Depth' => 1,
                'DepthOperator' => 'eq',
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'openpa_consiglio_collaboration_room' ),
                'SortBy' => array( 'published', false )
            ), $hidden )
        );
    }

    public function onCreate()
    {
        self::createCollaborationGroup( $this->getObject() );
        foreach( $this->politiciIdList() as $id )
        {
            self::createPoliticoRoleIfNeeded( $id, $this->mainNode()->attribute( 'node_id' ) );
        }

    }

    public function onUpdate()
    {
        self::createCollaborationGroup( $this->getObject() );
        foreach( $this->politiciIdList() as $id )
        {
            self::createPoliticoRoleIfNeeded( $id, $this->mainNode()->attribute( 'node_id' ) );
        }
    }

    protected static function createCollaborationGroup( eZContentObject $area )
    {
        $parentNodeId = $area->attribute( 'main_node_id' );
        $remoteId = 'openpa_consiglio_collaboration_group_' . $area->attribute( 'id' );
        $object = eZContentObject::fetchByRemoteID( $remoteId );
        if ( !$object instanceof eZContentObject )
        {
            $params = array(
                'remote_id' => $remoteId,
                'class_identifier' => 'user_group',
                'parent_node_id' => $parentNodeId,
                'attributes' => array(
                    'name' => 'Utenti ' . $area->attribute( 'name' )
                )
            );
            /** @var eZContentObject $object */
            $object = eZContentFunctions::createAndPublishObject( $params );
        }
        self::assignCollaborationRole( $object->attribute( 'id' ), $parentNodeId );
        return $object;
    }

    /**
     * @return eZRole
     */
    protected static function createRoleIfNeeded()
    {
        $roleName = 'Gestione sedute consiglio - Area Collaborativa';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array(
                array(
                    'ModuleName' => 'consiglio',
                    'FunctionName' => 'collaboration',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'editorialstuff',
                    'FunctionName' => 'full_dashboard',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_area' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_comment' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_file' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_room' ),
                            eZContentClass::classIDByIdentifier( 'user' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_comment' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_file' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_room' )
                        )
                    )
                ),
            ); //@todo
            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }

        }
        return $role;
    }

    protected static function createPoliticoRoleIfNeeded( $userId = null, $subTreeLimitationNodeId = null )
    {
        $roleName = 'Gestione sedute consiglio - Area Collaborativa - Politico';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array(
                array(
                    'ModuleName' => 'consiglio',
                    'FunctionName' => 'collaboration',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'editorialstuff',
                    'FunctionName' => 'full_dashboard',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_area' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_comment' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_room' ),
                            eZContentClass::classIDByIdentifier( 'user' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_comment' ),
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_file' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_room' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_room' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'openpa_consiglio_collaboration_area' )
                        )
                    )
                )
            ); //@todo
            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }
        }
        if ( $userId && $subTreeLimitationNodeId )
            $role->assignToUser( $userId, 'subtree', $subTreeLimitationNodeId );
        return $role;
    }

    protected static function assignCollaborationRole( $groupId, $subTreeLimitationNodeId )
    {
        $role = self::createRoleIfNeeded();
        $role->assignToUser( $groupId, 'subtree', $subTreeLimitationNodeId );
        $role = eZRole::fetchByName( 'Anonymous' );
        if ( $role instanceof eZRole )
        {
            $role->assignToUser( $groupId );
        }
        $role = eZRole::fetchByName( 'Members' );
        if ( $role instanceof eZRole )
        {
            $role->assignToUser( $groupId );
        }
    }

}