<?php

class Verbale extends OCEditorialStuffPost
{
    public static $classIdentifier = 'verbale';

    public static $sedutaIdentifier = 'seduta';

    public static $textIdentifier = 'testo';

    public static $titleIdentifier = 'titolo';

    /**
     * @var Seduta
     */
    protected $seduta;

    /**
     * @return OCEditorialStuffPostFactoryInterface|VerbaleFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'seduta_id';
        $attributes[] = 'seduta';

        return $attributes;
    }

    public function attribute($property)
    {
        if (( $property == 'seduta' || $property == 'seduta_id' )) {
            /** @return Seduta */
            return $this->getSeduta($property == 'seduta');
        }

        return parent::attribute($property);
    }

    public function getSeduta($asObject = true)
    {
        if ($this->seduta === null) {
            if (isset( $this->dataMap[self::$sedutaIdentifier] )) {
                $contentArray = explode('-', $this->dataMap[self::$sedutaIdentifier]->toString());
                $sedutaID = array_pop($contentArray);
                try {
                    if (is_numeric($sedutaID)) {
                        $this->seduta = OCEditorialStuffHandler::instance('seduta')
                            ->getFactory()
                            ->instancePost(array('object_id' => $sedutaID));
                    }
                } catch (Exception $e) {

                }
            }
        }
        if ($this->seduta instanceof Seduta) {
            if (!$asObject) {
                return $this->seduta->id();
            } else {
                return $this->seduta;
            }
        }

        return $this->seduta;
    }

    public function onCreate()
    {
        $this->getFactory()->generatePdf($this);
    }

    public function onUpdate()
    {
        $this->getFactory()->generatePdf($this);
    }

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

        $object = eZContentObject::fetchByRemoteID($remoteId);
        if ($object instanceof eZContentObject){
            return OCEditorialStuffHandler::instance('verbale')->getFactory()->instancePost(array(
                'object_id' => $object->attribute('id')
            ));
        }

        return null;
    }

    /**
     * @param eZContentObject $object
     *
     * @return eZContentObject
     * @throws Exception
     */
    public static function create(eZContentObject $object)
    {
        $remoteId = self::generateRemoteId($object);
        $verbale = eZContentObject::fetchByRemoteID($remoteId);

        $instance = OCEditorialStuffHandler::instance('seduta');
        /** @var Seduta $seduta */
        $seduta = $instance->getFactory()->instancePost(array('object_id' => $object->attribute('id')));
        $templatePath = $instance->getFactory()->getTemplateDirectory();

        $tpl = eZTemplate::factory();
        $tpl->setVariable('seduta', $seduta);
        $tpl->setVariable('verbale', $seduta->verbale());
        $tpl->setVariable('verbale_fields', $seduta->verbaleFields());
        $text = $tpl->fetch("design:{$templatePath}/parts/verbale/_full_text.tpl");
        $text = preg_replace( "/\r|\n/", "", $text );

        if (!$verbale instanceof eZContentObject) {
            $verbale = eZContentFunctions::createAndPublishObject(
                array(
                    'class_identifier' => self::$classIdentifier,
                    'parent_node_id' => $object->attribute('main_node_id'),
                    'remote_id' => $remoteId,
                    'attributes' => array(
                        self::$sedutaIdentifier => $object->attribute('id'),
                        self::$textIdentifier => SQLIContentUtils::getRichContent($text),
                        self::$titleIdentifier => "Verbale della " . $object->attribute('name')
                    )
                )
            );
        } else {
            eZContentFunctions::updateAndPublishObject(
                $verbale,
                array(
                    'attributes' => array(
                        self::$sedutaIdentifier => $object->attribute('id'),
                        self::$textIdentifier => SQLIContentUtils::getRichContent($text),
                        self::$titleIdentifier => "Verbale della " . $object->attribute('name')
                    )
                )
            );
        }

        $seduta->assignSection($verbale);

        return $verbale;
    }
}
