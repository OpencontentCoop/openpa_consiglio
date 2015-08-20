<?php


class Invito extends OCEditorialStuffPost
{
    protected static $classIdentifier = 'invito';

    protected static $objectIdentifier = 'object';
    protected static $userIdentifier = 'user';
    protected static $protocolloIdentifier = 'protocollo';

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'punti';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'punti' )
        {
            /** @return OCEditorialStuffPostInterface[] */
            return $this->getPunti();
        }
        return parent::attribute( $property );
    }

    public function getPunti()
    {
        $punti = array();
        $locale = eZLocale::instance();
        $puntoFactory = OCEditorialStuffHandler::instance( 'punto' )->getFactory();

        $dataMap = $this->object->dataMap();
        $listPunti = explode( '-', $dataMap['object']->toString() );
        foreach ( $listPunti as $puntoId )
        {
            try
            {
                /** @var Punto $punto */
                $punto = new Punto( array( 'object_id' => $puntoId ), $puntoFactory );
                /** @var eZContentObjectAttribute[] $puntoDataMap */
                $puntoDataMap = $punto->getObject()->dataMap();

                $punti [$puntoDataMap['n_punto']->content()] = array(
                    'object_id' => $punto->object->ID,
                    'n_punto' => $puntoDataMap['n_punto']->content(),
                    'ora' => $locale->formatShortTime($puntoDataMap['orario_trattazione']->content()->attribute( 'timestamp' )),
                    'oggetto' => $puntoDataMap['oggetto']->content()
                );
            }
            catch( Exception $e )
            {
                eZDebug::writeError( $e->getMessage() );
            }
        }
        ksort( $punti );
        return array_values($punti);
    }

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {

    }

    protected static function generateRemoteId( eZContentObject $puntoOdg, eZContentObject $invitato )
    {
        $punto = new Punto(
            array( 'object_id' => $puntoOdg->attribute( 'id' ) ),
            OCEditorialStuffPostFactory::instance( 'punto')
        );
        $values = array(
            self::$classIdentifier,
            $punto->getSeduta( false ),
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
    public static function create( eZContentObject $puntoOdg, eZContentObject $invitato, $ora = false )
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
        else
        {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $invito->attribute( 'data_map' );
            $objectIds =  explode( '-', $dataMap[self::$objectIdentifier]->toString() );
            $objectIds[] = $puntoOdg->attribute( 'id' );
            $objectIdsString = implode( '-', array_unique( $objectIds ) );
            $dataMap[self::$objectIdentifier]->fromString( $objectIdsString );
            $dataMap[self::$objectIdentifier]->store();
            eZSearch::addObject( $invito, true );
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $invito->attribute( 'id' ) );
        }
        return $invito;
    }
}