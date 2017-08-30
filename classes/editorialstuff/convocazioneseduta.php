<?php

class ConvocazioneSeduta extends OCEditorialStuffPost
{
    protected static $classIdentifier = 'convocazione_seduta';
    protected static $sedutaIdentifier = 'seduta';
    protected static $protocolloIdentifier = 'protocollo';
    protected static $odgIdentifier = 'odg';
    protected static $dataOraIdentifier = 'data_ora';

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    ) {

    }

    protected static function generateRemoteId(eZContentObject $seduta)
    {
        $values = array(
            self::$classIdentifier,
            $seduta->attribute('id')
        );

        return implode('_', $values);
    }

    public static function get(eZContentObject $seduta)
    {
        $remoteId = self::generateRemoteId($seduta);

        return eZContentObject::fetchByRemoteID($remoteId);
    }

    /**
     * @param eZContentObject $seduta
     *
     * @return eZContentObject
     * @throws Exception
     */
    public static function create(eZContentObject $seduta)
    {
        $remoteId = self::generateRemoteId($seduta);
        $instance = OCEditorialStuffHandler::instance('seduta');
        /** @var Seduta $postSeduta */
        $postSeduta = $instance->getFactory()->instancePost(array('object_id' => $seduta->attribute('id')));
        $convocazione = eZContentObject::fetchByRemoteID($remoteId);

        if (!$convocazione instanceof eZContentObject) {
            $convocazione = eZContentFunctions::createAndPublishObject(
                array(
                    'class_identifier' => self::$classIdentifier,
                    'parent_node_id' => $seduta->attribute('main_node_id'),
                    'remote_id' => $remoteId,
                    'attributes' => array(
                        self::$sedutaIdentifier => $seduta->attribute('id'),
                        self::$odgIdentifier => json_encode($postSeduta->odgSerialized()),
                        self::$dataOraIdentifier => $postSeduta->dataOra(),
                        self::$protocolloIdentifier => $postSeduta->protocollo()
                    )
                )
            );
        } else {
            eZContentFunctions::updateAndPublishObject(
                $convocazione,
                array(
                    'attributes' => array(
                        self::$sedutaIdentifier => $seduta->attribute('id'),
                        self::$odgIdentifier => json_encode($postSeduta->odgSerialized()),
                        self::$dataOraIdentifier => $postSeduta->dataOra(),
                        self::$protocolloIdentifier => $postSeduta->protocollo()
                    )
                )
            );
        }

        return $convocazione;
    }
}
