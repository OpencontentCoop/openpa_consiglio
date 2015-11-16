<?php

class ReferenteLocale extends OCEditorialStuffPost implements OCEditorialStuffPostInputActionInterface
{

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'politici';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'politici' )
        {
            return $this->getPoliticoMatchList();
        }
        return parent::attribute( $property );
    }

    protected function getPoliticoMatchList()
    {
        /** @var eZUser $user */
        $user = eZUser::fetch( $this->id() );
        $data = array();
        $politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems( array( 'limit' => 100, 'offset' => 0, 'sort' => array( 'politico/cognome' => 'asc' ) ) );
        foreach( $politici as $politico )
        {
            $area = OpenPAConsiglioCollaborationHelper::createCollaborationAreaIfNeeded( eZUser::fetch( $politico->id() ) );
            $areaGroup = OpenPAConsiglioCollaborationHelper::createCollaborationGroupIfNeeded( eZUser::fetch( $politico->id() ) );
            $data[] = array(
                'is_active' => in_array( $areaGroup->attribute( 'id' ), $user->groups() ),
                'politico' => $politico,
                'area' => $area,
                'area_group' => $areaGroup
            );
        }
        return $data;
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Contenuto',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            )
        );
        $tabs[] = array(
            'identifier' => 'referrer',
            'name' => 'Aree di riferimento',
            'template_uri' => "design:{$templatePath}/parts/referrer.tpl"
        );
        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );
        return $tabs;
    }

    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null )
    {
        if ( $actionIdentifier == 'AddToArea' )
        {
            $this->addToArea( $actionParameters['GroupNodeId'] );
        }
        elseif ( $actionIdentifier == 'RemoveFromArea' )
        {
            $this->removeFromArea( $actionParameters['GroupNodeId'] );
        }
    }

    public function addToArea( $groupNodeId )
    {
        eZContentOperationCollection::addAssignment(
            $this->getObject()->attribute( 'main_node_id' ),
            $this->getObject()->attribute( 'id' ),
            array( $groupNodeId )
        );
    }

    public function removeFromArea( $groupNodeId )
    {
        /** @var eZContentObjectTreeNode[] $nodes */
        $nodes = $this->getObject()->attribute( 'assigned_nodes' );
        $removeNodeIdList = array();
        if ( count( $nodes ) > 1 )
        {
            foreach ( $nodes as $node )
            {
                if ( $node->attribute( 'parent_node_id' ) == $groupNodeId )
                {
                    $removeNodeIdList[] = $node->attribute( 'node_id' );
                }
            }
        }
        if ( !empty( $removeNodeIdList ) )
        {
            eZContentOperationCollection::removeNodes( $removeNodeIdList );
        }
    }

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        // TODO: Implement onChangeState() method.
    }
}