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


        if (!$version)
        {
            $object = $currentPost->getObject();
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
                    $siteINI = eZINI::instance( 'site.ini' );
                    $siteUrl = $siteINI->variable( 'SiteSettings', 'SiteURL' );
                    $image = $firmatarioDataMAp['firma']->content()->attribute('original');
                    $variables['firma'] = $siteUrl . '/' . $image['url'];
                }
            }
        }
        else
        {
            /** @var eZContentObjectVersion $object */
            $object = $currentPost->getObject()->version($version);
            if ( !$object instanceof eZContentObjectVersion )
            {
                return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
            }

            $dataMap = $object->dataMap();

            /** @var eZContentObject $seduta */
            $seduta = $dataMap['seduta']->content();
            $sedutaDataMap = $seduta->dataMap();

            $odg = json_decode($dataMap['odg']->content(), true);
            $listOrgano = $sedutaDataMap['organo']->content();
            $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

            $variables = array(
                'line_height' => 20,
                'data'        => strftime( '%d/%m/%Y', $object->Created),
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
                    $siteINI = eZINI::instance( 'site.ini' );
                    $siteUrl = $siteINI->variable( 'SiteSettings', 'SiteURL' );
                    $image = $firmatarioDataMAp['firma']->content()->attribute('original');
                    $variables['firma'] = $siteUrl . '/' . $image['url'];
                }
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