<?php


class OpenPAConsiglioDefaultFactory extends OCEditorialStuffPostDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function __construct($configuration)
    {
        parent::__construct($configuration);
        $this->overrideConfiguration($this->configuration);
    }

    public function instancePost($data)
    {
        return new OpenPAConsiglioDefaultPost($data, $this);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/consiglio_default';
    }


    protected function dashboardModuleResultTemplate(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module
    ) {
//        $parameters['heuristic'] = true;

        return parent::dashboardModuleResultTemplate($parameters, $handler, $module);
    }
}
