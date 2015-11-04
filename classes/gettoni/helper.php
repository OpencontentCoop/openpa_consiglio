<?php

class OpenPAConsiglioGettoniHelper
{
    /**
     * @var Politico
     */
    protected $politico;

    /**
     * @var Politico[]
     */
    protected $politici;

    /**
     * @var Seduta[]
     */
    protected $sedute;

    public function __construct()
    {

    }

    public function setPolitico( Politico $politico )
    {
        $this->politico = $politico;
    }

    /**
     * @return Politico[]
     */
    public function getPolitici()
    {
        if ( $this->politici === null )
        {
            $this->politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
                array( 'limit' => 100, 'offset' => 0, 'sort' => array( 'name' => 'asc' ) )
            );
        }

        return $this->politici;
    }

    /**
     * @param OpenPAConsiglioGettoniInterval $interval
     *
     * @return Seduta[]
     */
    public function getSedute( OpenPAConsiglioGettoniInterval $interval )
    {
        if ( $this->sedute === null )
        {
            $this->sedute = array();

            $startDate = ezfSolrDocumentFieldBase::preProcessValue(
                $interval->startDateTime->getTimestamp(),
                'date'
            );
            $endDate = ezfSolrDocumentFieldBase::preProcessValue(
                $interval->endDateTime->getTimestamp(),
                'date'
            );
            $filters = array( 'meta_published_dt:[' . $startDate . ' TO ' . $endDate . ']' );

            if ( $this->politico instanceof Politico )
            {
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
            }

            $this->sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems(
                array(
                    'filters' => $filters,
                    'state' => array( 'closed' ),
                    'sort' => array( 'meta_published_dt' => 'asc' ),
                    'limit' => 1000,
                    'offset' => 0
                )
            );
        }

        return $this->sedute;
    }

    public function getGettoni()
    {
        $data = array();
        if ( $this->politico instanceof Politico )
        {
            $gettoni = OpenPAConsiglioGettone::fetchByUserID( $this->politico->id() );
            foreach ( $gettoni as $gettone )
            {
                $data[$gettone->attribute( 'seduta_id' )] = $gettone;
            }
        }

        return $data;
    }

}