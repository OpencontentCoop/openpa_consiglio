<?php

use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Api\ContentSearch;

class DataHandlerPercentualePresenzeSeduta implements OpenPADataHandlerInterface
{

    /** @var Politico $politico */
    protected $politico;

    public function __construct( array $Params )
    {
        $uid = eZHTTPTool::instance()->hasGetVariable( 'uid' ) ? eZHTTPTool::instance()->getVariable( 'uid' ) : 0;
        /** @var Politico $politico */
        $this->politico = OCEditorialStuffHandler::instance( 'politico' )->getFactory()->instancePost(
            array( 'object_id' =>$uid )
        );
    }

    public function getData()
    {
        $data = array(
            'totale_sedute' => array(),
            'presenze' => array(),
            'assenze' => array(),
            'anni' => array(),
        );

        if ( $this->politico instanceof Politico )
        {
            
            $contentSearch = new ContentSearch();
            $currentEnvironment = EnvironmentLoader::loadPreset('content');                    
            $contentSearch->setEnvironment($currentEnvironment);
            $parser = new ezpRestHttpRequestParser();
            $request = $parser->createRequest();
            $currentEnvironment->__set('request', $request);

            $allQuery = 'raw[meta_object_states_si] = 18 facets [raw[subattr_data___year____dt]|alpha|50] limit 1';
            $allData = (array)$contentSearch->search($allQuery);
            foreach ($allData['facets'][0]['data'] as $date => $count) {
                $dateParts = explode('-', $date);
                $year = array_shift($dateParts);
                $data['totale_sedute'][$year] = $count;                
            }            

            $currentUserQuery = 'presenti.id = ' . $this->politico->id() . ' and raw[meta_object_states_si] = 18 facets [raw[subattr_data___year____dt]|alpha|50]  limit 1';
            $currentUserData = (array)$contentSearch->search($currentUserQuery);
            foreach ($currentUserData['facets'][0]['data'] as $date => $count) {
                $dateParts = explode('-', $date);
                $year = array_shift($dateParts);
                $data['anni'][] = $year;
                $data['presenze'][] = $count;
                $data['assenze'][] = $data['totale_sedute'][$year] - $count;
            }
        }

        return $data;
    }

    /**
     * @return string|array|object
     */
    public function _old_getData()
    {
        $presente = 0;
        $assente = 0;

        if ( $this->politico instanceof Politico )
        {
            $filters = array();
            $organoNodeIds = array();
            $currentLocations = $this->politico->attribute( 'is_in' );
            foreach ( $this->politico->attribute( 'locations' ) as $identifier => $node )
            {
                /** @var eZContentObjectTreeNode $node */
                if ( $currentLocations[$identifier] )
                {
                    $organoNodeIds[] = $node->attribute( 'node_id' );
                }
            }
            if ( !empty( $organoNodeIds ) )
            {
                $organoFilters = count( $organoNodeIds ) > 1 ? array( 'or' ) : array();
                foreach ( $organoNodeIds as $nodeId )
                {
                    $organoFilters[] = 'submeta_organo___main_node_id_si:' . $nodeId;
                }
                $filters[] = count( $organoFilters ) > 1 ? $organoFilters : $organoFilters[0];
            }

            $totaleSeduteConcluse = OCEditorialStuffHandler::instance( 'seduta' )->fetchItemsCount(
                array(
                    'filters' => $filters,
                    'state' => array( 'closed' )
                )
            );
            $totaleSedutePresenti = OCEditorialStuffHandler::instance( 'seduta' )->fetchItemsCount(
                array(
                    'filters' => array_merge( array( 'submeta_presenti___id_si:' . $this->politico->id() ), $filters ),
                    'state' => array( 'closed' )
                )
            );
            $presente = floor( 100 * $totaleSedutePresenti / $totaleSeduteConcluse );
            $assente = 100 - $presente;
        }
        return array(
            array( 'Presente', $presente ),
            array( 'Assente', $assente )
        );
    }
}