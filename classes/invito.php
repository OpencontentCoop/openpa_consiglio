<?php


class Invito extends OCEditorialStuffPost
{
    protected static $classIdentifier = 'invito';

    protected static $objectIdentifier = 'object';
    protected static $userIdentifier = 'user';
    protected static $protocolloIdentifier = 'protocollo';

    protected static function generateRemoteId( eZContentObject $puntoOdg, eZContentObject $invitato )
    {
        $values = array(
            self::$classIdentifier,
            $puntoOdg->attribute( 'id' ),
            $invitato->attribute( 'id' ),
        );
        return implode( '_', $values );
    }

    /**
     * @param eZContentObject $puntoOdg
     * @param eZContentObject $invitato
     *
     * @return eZContentObject
     * @throws Exception
     */
    public static function create( eZContentObject $puntoOdg, eZContentObject $invitato )
    {
        $remoteId = self::generateRemoteId( $puntoOdg, $invitato );
        $invito = eZContentObject::fetchByRemoteID( $remoteId );
        if ( !$invito instanceof eZContentObject )
        {
            $invito = eZContentFunctions::createAndPublishObject( array(
                'class_identifier' => self::$classIdentifier,
                'parent_node_id' => $puntoOdg->attribute( 'main_node_id' ),
                'remote_id' => $remoteId,
                'attributes' => array(
                    self::$objectIdentifier => $puntoOdg->attribute( 'id' ),
                    self::$userIdentifier => $invitato->attribute( 'id' )
                )
            ));
        }
        return $invito;
    }
}