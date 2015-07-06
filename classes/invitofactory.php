<?php

class InvitoFactory extends OCEditorialStuffPostDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    public function instancePost( $data )
    {
        return new Invito( $data, $this );
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