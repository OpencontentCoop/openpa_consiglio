<?php


class PoliticoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost($data)
    {
        return new Politico($data, $this);
    }

	public function dashboardModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        if (isset($this->configuration['BrowseStartNode']) && isset($this->configuration['BrowseButtonText']) && isset($_GET['embed'])){
        	eZContentBrowse::browse(
                array(
                    'action_name' => 'BrowseFactory',
                    'selection' => 'multiple',
                    'return_type' => 'NodeID',
                    'ignore_nodes_select' => array(),
                    'from_page' => '/editorialstuff/dashboard/' . $this->identifier(),
                    'class_array' => array( $this->classIdentifier() ),
                    'start_node' => $this->configuration['BrowseStartNode'],
                    'cancel_page' => '/editorialstuff/dashboard/' . $this->identifier()
                ),
                $module
            );

            return;
        }
		
		$http = eZHTTPTool::instance();
        if ( $http->hasPostVariable( 'BrowseActionName' ) && $http->postVariable( 'BrowseActionName' ) == 'BrowseFactory' ){
            $selectedArray = $http->postVariable( 'SelectedNodeIDArray' );
            foreach ($selectedArray as $nodeId) {
            	$node = eZContentObjectTreeNode::fetch((int)$nodeId);
            	if ($node instanceof eZContentObjectTreeNode){
            		eZContentOperationCollection::addAssignment( $node->attribute('node_id'), $node->attribute('contentobject_id'), array($this->creationRepositoryNode()) );
            	}
            }
        }

        $tpl = $this->dashboardModuleResultTemplate( $parameters, $handler, $module );
        $Result = array();
        $Result['content'] = $tpl->fetch( "design:{$this->getTemplateDirectory()}/dashboard.tpl" );
        $contentInfoArray = array(
            'node_id' => null,
            'class_identifier' => null
        );
        $contentInfoArray['persistent_variable'] = array(
            'show_path' => true,
            'site_title' => 'Dashboard ' . $this->classIdentifier()
        );
        if ( is_array( $tpl->variable( 'persistent_variable' ) ) )
        {
            $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $tpl->variable( 'persistent_variable' ) );
        }
        if ( isset( $this->configuration['PersistentVariable'] ) && is_array( $this->configuration['PersistentVariable'] ) )
        {
            $contentInfoArray['persistent_variable'] = array_merge( $contentInfoArray['persistent_variable'], $this->configuration['PersistentVariable'] );
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array( array( 'url' => false, 'text' => isset( $this->configuration['Name'] ) ? $this->configuration['Name'] : 'Dashboard' ) );
        return $Result;
    }
}
