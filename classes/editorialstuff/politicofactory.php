<?php


class PoliticoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost( $data )
    {
        return new Politico( $data, $this );
    }

    public static function reindex()
    {
        /** @var Politico[] $sedute */
        $politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
            array(
                'state' => array( 'closed' ),
                'limit' => 1000,
                'offset' => 0,
                'sort' => array( 'modified' => 'desc' )
            ), array()
        );
        foreach( $politici as $politico )
        {
            $objectID = $politico->id();
            eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '$objectID' )" );
        }

    }
}