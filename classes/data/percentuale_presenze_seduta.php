<?php

class DataHandlerPercentualePresenzeSeduta
{
    use SolrFieldsTrait;

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

    /**
     * @return string|array|object
     */
    public function getData()
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
                    $organoFilters[] = self::generateSolrSubMetaField('organo','main_node_id') . ':' . $nodeId;
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
                    'filters' => array_merge( array( self::generateSolrSubMetaField('presenti','id') . ':' . $this->politico->id() ), $filters ),
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
