<?php


class PropostaFactory extends OpenPAConsiglioDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function instancePost($data)
    {
        return new Proposta($data, $this);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/proposta';
    }

    protected function editModuleResultTemplate($currentPost, $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module)
    {
    	$tpl = parent::editModuleResultTemplate($currentPost, $parameters, $handler, $module);
    	$tpl->setVariable('view_parameters', $module->UserParameters);

    	return $tpl;
    }
}