<?php

class ReferenteLocale extends OCEditorialStuffPost implements OCEditorialStuffPostInputActionInterface
{

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'areas';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'areas' )
        {
            return $this->getAreas();
        }
        return parent::attribute( $property );
    }

    protected function getAreas()
    {
        return OCEditorialStuffHandler::instance( 'areacollaborativa' )->fetchItems( array( 'limit' => 100, 'offset' => 0, 'sort' => array( 'name' => 'asc' ) ) );
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
            'template_uri' => "design:{$templatePath}/parts/areas.tpl"
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