<?php

class ConsiglioSedutaFactory extends OCEditorialStuffPostDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    public function instancePost( $data )
    {
        return new ConsiglioSeduta( $data, $this );
    }


    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module,
        $version = false
    )
    {

        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
        $object = $version ? $currentPost->getObject()->version($version) : $currentPost->getObject();
        $dataMap = $object->dataMap();

        /** @var eZContentObject $seduta */
        $seduta = $dataMap['seduta']->content();
        $sedutaDataMap = $seduta->dataMap();

        $odg = json_decode($dataMap['odg']->content(), true);
        $listOrgano = $sedutaDataMap['organo']->content();
        $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

        $variables = array(
            'line_height' => 20,
            'data'        => strftime( '%d/%m/%Y', $object->Published),
            'luogo'       => $sedutaDataMap['luogo']->content(),
            'organo'      => $organo->Name,
            'data_seduta' => strftime( '%A %d %B %Y, alle ore %H:%M', $dataMap['data_ora']->toString()),
            'odg'         => $odg
        );

        if ($sedutaDataMap['firmatario']->hasContent())
        {
            $listFirmatario = $sedutaDataMap['firmatario']->content();
            $firmatario = eZContentObject::fetch($listFirmatario['relation_list'][0]['contentobject_id']);
            $firmatarioDataMAp = $firmatario->dataMap();

            $variables['firmatario'] = $firmatario->Name;
            if ($firmatarioDataMAp['firma']->hasContent())
            {
                // todo: cambiare firma nella classe in img
                $variables['firma'] = 'firma.png';
            }
        }

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach( $variables as $name => $value )
        {
            $tpl->setVariable( $name, $value );
        }
        $content = $tpl->fetch( 'design:pdf/seduta/seduta.tpl' );
        OpenPAConsiglioPdf::create( 'Seduta', $content, false, 'seduta');
        eZExecution::cleanExit();

    }
}