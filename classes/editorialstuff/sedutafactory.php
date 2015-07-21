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
}