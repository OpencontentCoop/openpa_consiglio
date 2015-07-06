<?php

class SedutaFactory extends OCEditorialStuffPostFactory implements OCEditorialStuffPostDownloadableFactoryInterface
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
        // TODO: Implement onChangeState() method.
    }

    public function instancePost( $data )
    {
        return new Seduta( $data, $this );
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/seduta';
    }

    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module
    )
    {
        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
        echo '<pre>';
        echo '<h1>TODO</h1>';
        print_r($currentPost);
        die();
    }
}