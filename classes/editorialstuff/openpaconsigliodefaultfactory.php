<?php


class OpenPAConsiglioDefaultFactory extends OCEditorialStuffPostDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function __construct($configuration)
    {
        parent::__construct($configuration);
        $this->overrideConfiguration($this->configuration);
    }

    public function instancePost($data)
    {
        return new OpenPAConsiglioDefaultPost($data, $this);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/consiglio_default';
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

        return parent::dashboardModuleResult( $parameters, $handler, $module );
    }

    protected function dashboardModuleResultTemplate(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module
    ) {
//        $parameters['heuristic'] = true;

        return parent::dashboardModuleResultTemplate($parameters, $handler, $module);
    }
}
