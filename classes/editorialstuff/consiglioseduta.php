<?php

class ConsiglioSeduta extends OCEditorialStuffPost
{
    protected static $classIdentifier   = 'consiglio_seduta';
    protected static $sedutaIdentifier  = 'seduta';
    protected static $odgIdentifier     = 'odg';
    protected static $dataOraIdentifier = 'data_ora';

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {

    }

    protected static function generateRemoteId( eZContentObject $seduta )
    {
        $values = array(
            self::$classIdentifier,
            $seduta->attribute( 'id' )
        );
        return implode( '_', $values );
    }

    /**
     * @param eZContentObject $seduta
     * @param eZContentObject $invitato
     *
     * @return eZContentObject
     * @throws Exception
     */
    public static function create( eZContentObject $seduta)
    {
        $remoteId = self::generateRemoteId( $seduta );
        $instance = OCEditorialStuffHandler::instance( 'seduta' );
        /** @var Seduta $postSeduta */
        $postSeduta = $instance->getFactory()->instancePost( array( 'object_id' => $seduta->ID ) );
        $consiglio = eZContentObject::fetchByRemoteID( $remoteId );

        if ( !$consiglio instanceof eZContentObject )
        {
            $consiglio = eZContentFunctions::createAndPublishObject( array(
                'class_identifier' => self::$classIdentifier,
                'parent_node_id' => $seduta->attribute( 'main_node_id' ),
                'remote_id' => $remoteId,
                'attributes' => array(
                    self::$sedutaIdentifier => $seduta->attribute( 'id' ),
                    self::$odgIdentifier => json_encode($postSeduta->odgSerialized()),
                    self::$dataOraIdentifier => $postSeduta->dataOra()
                )
            ));
        }
        else
        {
            eZContentFunctions::updateAndPublishObject(
                $consiglio,
                array(
                    'attributes' => array(
                        self::$sedutaIdentifier => $seduta->attribute( 'id' ),
                        self::$odgIdentifier => json_encode($postSeduta->odgSerialized()),
                        self::$dataOraIdentifier => $postSeduta->dataOra()
                    )
                )
            );
        }
        return $consiglio;
    }
}