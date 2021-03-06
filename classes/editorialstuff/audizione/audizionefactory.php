<?php

class AudizioneFactory extends OpenPAConsiglioNotifiableFactory
{
    use SolrFieldsTrait;

    public function instancePost( $data )
    {
        return new Audizione( $data, $this );
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/audizione';
    }

    /**
     * @return array[] array( 'type' => array( 'handler_method' => <methodName> ) )
     */
    public function notificationEventTypesConfiguration()
    {
        return array(
            'publish' => array(
                'name' => 'Pubblicazione di un nuovo punto',
                'handler_method' => 'handlePublishNotification'
            ),
            'update' => array(
                'name' => 'Aggiornamento di un punto pubblicato',
                'handler_method' => 'handleUpdateNotification'
            ),
            'add_file' => array(
                'name' => 'Inserimento di un allegato ad un punto pubblicato',
                'handler_method' => 'handleAddFileNotification'
            ),
            'update_file' => array(
                'name' => 'Aggiornamento di un allegato ad un punto pubblicato',
                'handler_method' => 'handleUpdateFileNotification'
            )
        );
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
}
