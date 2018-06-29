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

    public function attribute($property)
    {
        if ($property == 'group') {
            return $this->group();
        }

        if ($property == 'main_node') {
            return $this->mainNode();
        }

        if ($property == 'politici_id_list') {
            return $this->politiciIdList();
        }

        if ($property == 'referenti_id_list') {
            return $this->referentiIdList();
        }

        return parent::attribute($property);
    }

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    ) {

    }

    /**
     * @return eZContentObjectTreeNode
     */
    public function mainNode()
    {
        return $this->object->attribute('main_node');
    }

    /**
     * @return eZContentObject
     */
    public function group()
    {
        return self::createCollaborationGroup($this->getObject());
    }

    public function politiciIdList()
    {
        $data = array();
        if (isset( $this->dataMap['politici'] )) {
            $data = explode('-', $this->dataMap['politici']->toString());
        }

        return $data;
    }

    public function referentiIdList()
    {
        $data = array();
        $referenti = OCEditorialStuffHandler::instance('referentelocale')->fetchItems(array(
            'limit' => 100,
            'offset' => 0,
            'filters' => array('meta_path_si:' . $this->group()->attribute('main_node_id'))
        ), array());

        foreach ($referenti as $referente) {
            $data[] = $referente->id();
        }

        return $data;
    }

    public function rooms($showHidden = false)
    {
        $hidden = array();
        if ($showHidden) {
            $hidden = array('IgnoreVisibility' => true);
        }

        return $this->mainNode()->subTree(
            array_merge(array(
                'Depth' => 1,
                'DepthOperator' => 'eq',
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array('openpa_consiglio_collaboration_room'),
                'SortBy' => array('published', false)
            ), $hidden)
        );
    }

    /**
     * @param $relationId
     *
     * @return eZFindResultNode[]
     */
    public function fetchRoomsByRelation($relationId)
    {
        $solr = new eZSolr();
        $search = $solr->search(null, array(
            'ContentClassID' => array('openpa_consiglio_collaboration_room'),
            'SubTreeArray' => array($this->object->attribute('main_node_id')),
            'Filter' => array('submeta_relation___id_si:' . $relationId)
        ));

        return $search['SearchResult'];
    }


    /**
     * @param $relationId
     *
     * @return int
     */
    public function fetchCountRoomsByRelation($relationId)
    {
        $solr = new eZSolr();
        $search = $solr->search(null, array(
            'SearchLimit' => 1,
            'ContentClassID' => array('openpa_consiglio_collaboration_room'),
            'SubTreeArray' => array($this->object->attribute('main_node_id')),
            'Filter' => array('submeta_relation___id_si:' . $relationId),
            'Limitation' => array()
        ));

        return $search['SearchCount'];
    }


    /**
     * @param $relationId
     *
     * @return eZFindResultNode[]
     */
    public function fetchFilesByRelation($relationId)
    {
        $solr = new eZSolr();
        $search = $solr->search(null, array(
            'ContentClassID' => array('openpa_consiglio_collaboration_file'),
            'SubTreeArray' => array($this->object->attribute('main_node_id')),
            'Filter' => array('submeta_relation___id_si:' . $relationId)
        ));

        return $search['SearchResult'];
    }

    /**
     * @param $relationId
     *
     * @return int
     */
    public function fetchCountFilesByRelation($relationId)
    {
        $solr = new eZSolr();
        $search = $solr->search(null, array(
            'SearchLimit' => 1,
            'ContentClassID' => array('openpa_consiglio_collaboration_file'),
            'SubTreeArray' => array($this->object->attribute('main_node_id')),
            'Filter' => array('submeta_relation___id_si:' . $relationId),
            'Limitation' => array()
        ));

        return $search['SearchCount'];
    }

    public function onCreate()
    {
        self::createCollaborationGroup($this->getObject());
        foreach ($this->politiciIdList() as $id) {
            self::createPoliticoRoleIfNeeded($id, $this->mainNode()->attribute('node_id'));
        }

    }

    public function onUpdate()
    {
        self::createCollaborationGroup($this->getObject());
        foreach ($this->politiciIdList() as $id) {
            self::createPoliticoRoleIfNeeded($id, $this->mainNode()->attribute('node_id'));
        }
    }

    protected static function createCollaborationGroup(eZContentObject $area)
    {
        $parentNodeId = $area->attribute('main_node_id');
        $remoteId = 'openpa_consiglio_collaboration_group_' . $area->attribute('id');
        $object = eZContentObject::fetchByRemoteID($remoteId);
        if (!$object instanceof eZContentObject) {
            $params = array(
                'remote_id' => $remoteId,
                'class_identifier' => 'user_group',
                'parent_node_id' => $parentNodeId,
                'attributes' => array(
                    'name' => 'Utenti ' . $area->attribute('name')
                )
            );
            /** @var eZContentObject $object */
            $object = eZContentFunctions::createAndPublishObject($params);
        }
        self::assignCollaborationRole($object->attribute('id'), $parentNodeId);

        return $object;
    }

    /**
     * @return eZRole
     */
    protected static function createRoleIfNeeded()
    {
        $roleHelper = new OpenPAConsiglioRoles();

        return $roleHelper->createRoleIfNeeded(OpenPAConsiglioRoles::AREA_COLLABORATIVA);
    }

    protected static function createPoliticoRoleIfNeeded($userId = null, $subTreeLimitationNodeId = null)
    {
        $roleHelper = new OpenPAConsiglioRoles();
        $role = $roleHelper->createRoleIfNeeded(OpenPAConsiglioRoles::AREA_COLLABORATIVA_POLITICO);
        if ($userId && $subTreeLimitationNodeId) {
            $role->assignToUser($userId, 'subtree', $subTreeLimitationNodeId);
        }

        return $role;
    }

    protected static function assignCollaborationRole($groupId, $subTreeLimitationNodeId)
    {
        $role = self::createRoleIfNeeded();
        $role->assignToUser($groupId, 'subtree', $subTreeLimitationNodeId);
        $role = eZRole::fetchByName('Anonymous');
        if ($role instanceof eZRole) {
            $role->assignToUser($groupId);
        }
        $role = eZRole::fetchByName('Members');
        if ($role instanceof eZRole) {
            $role->assignToUser($groupId);
        }
    }

}
