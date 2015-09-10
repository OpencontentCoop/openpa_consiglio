<?php

class DataHandlerPercentualePresenzeSeduta implements OpenPADataHandlerInterface
{

    public function __construct( array $Params )
    {
    }

    /**
     * @return string|array|object
     */
    public function getData()
    {        
        $uid = eZHTTPTool::instance()->hasGetVariable( 'uid' ) ? eZHTTPTool::instance()->getVariable( 'uid' ) : 0;
        $totaleSeduteConcluse = OCEditorialStuffHandler::instance( 'seduta' )->fetchItemsCount( array( 'state' => 'closed' ) );        
        $searchSedutePresenti = OpenPaFunctionCollection::search(
            array(
                'SearchLimit' => 1,
                'Filter' => array( 'submeta_presenti___id_si:' . $uid ),
                'SearchContentClassID' => array( 'seduta' )
            )
        );
        $totaleSedutePresenti = $searchSedutePresenti['SearchCount'];
        $presente = floor( 100 * $totaleSedutePresenti / $totaleSeduteConcluse );
        $assente = 100 - $presente;
        return array(
            array( 'Presente', $presente ),
            array( 'Assente', $assente )
        );
    }
}