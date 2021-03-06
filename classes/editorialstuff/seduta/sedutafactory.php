<?php

class SedutaFactory extends OpenPAConsiglioNotifiableFactory
{
    use OpenPAConsiglioConfigurableTrait;
    use SolrFieldsTrait;

    public function __construct($configuration)
    {
        parent::__construct($configuration);
        $this->overrideConfiguration($this->configuration);
    }

    /**
     * @param OCEditorialStuffPostInterface $post
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState(
        OCEditorialStuffPostInterface $post,
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    ) {
    }

    public function instancePost($data)
    {
        return new Seduta($data, $this);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/seduta';
    }

    /**
     * @return array[]
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = array(
            'solr_identifier' => self::generateSolrField("from_time", "date"),
            'object_property' => 'from_time',
            'attribute_identifier' => 'from_time',
            'index_extra' => true,
            'index_plugin_call_function' => 'indexFromTime'
        );

        return $fields;
    }

    public static function reindex()
    {
        /** @var Seduta[] $sedute */
        $sedute = OCEditorialStuffHandler::instance('seduta')->fetchItems(
            array(
                'state' => array('closed'),
                'limit' => 1000,
                'offset' => 0,
                'sort' => array('modified' => 'desc')
            ), array()
        );
        foreach ($sedute as $seduta) {
            $objectID = $seduta->id();
            eZDB::instance()->query("INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '$objectID' )");
        }

    }

    /**
     * @return array[] array( 'type' => array( 'handler_method' => <methodName> ) )
     */
    public function notificationEventTypesConfiguration()
    {
        return array(
            'update_data_ora' => array(
                'name' => 'Aggiornamento data e ora',
                'handler_method' => 'handleUpdateNotification',
            ),
            'send_convocation' => array(
                'name' => 'Invio convocazione',
                'handler_method' => 'handleSendConvocationNotification',
            )
        );
    }

}
