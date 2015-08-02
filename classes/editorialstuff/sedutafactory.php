<?php

class SedutaFactory extends OCEditorialStuffPostFactory
{

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
    )
    {
    }

    public function instancePost( $data )
    {
        return new Seduta( $data, $this );
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
            'solr_identifier' => "attr_from_time_dt",
            'object_property' => 'from_time',
            'attribute_identifier' => 'from_time',
            'index_extra' => true,
            'index_plugin_call_function' => 'indexFromTime'
        );
        return $fields;
    }
}