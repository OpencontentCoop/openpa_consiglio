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

//    public function editModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
//    {
//        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
//        if ( $currentPost instanceof Seduta )
//        {
//            $currentPost->addPresenza( true, 'test', 1848 );
//        }
//        return parent::editModuleResult( $parameters, $handler, $module );
//    }

    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module,
        $version = false
    )
    {
        /** @var Seduta $currentPost */
        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
        $dataMap = $currentPost->attribute('object')->dataMap();

        $odg = $currentPost->odg();

        $punti = array();
        if (!empty($odg))
        {
            /** @var Punto $o */
            foreach ($odg as $o)
            {
                $tempDataMap = $o->getObject()->dataMap();
                $punti [$tempDataMap['n_punto']->content()] = array(
                    'data_doc' => strftime('%d/%m/%Y  alle ore %H:%M',time()), // Todo: verificcare che data si deve inserire
                    'oggetto'  => $tempDataMap['oggetto']->content(),
                    'politico' => '',
                    'tecnico'  => '',
                    'osservazioni' => $tempDataMap['consenti_osservazioni']->content(),
                    'termine_oss' => strftime(
                        '%d/%m/%Y  alle ore %H:%M',
                        $tempDataMap['termine_osservazioni']->toString()
                        )
                );
            }
        }

        $listOrgano = $dataMap['organo']->content();
        $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);


        $listOrgano = $dataMap['organo']->content();
        $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

        $variables = array(
            'line_height' => 20,
            'data'        => strftime( '%d %m %Y', time()), // Todo: chiedere se va bene data oggetto
            //'indirizzo'   => $userDataMap['indirizzo']->content(),
            'organo'      => $organo->Name,
            'data_seduta' => strftime( '%A %d %B %Y, alle ore %H:%M', $currentPost->dataOra()),
            'punti'       => $punti

        );

        if ($dataMap['firmatario']->hasContent())
        {
            $listFirmatario = $dataMap['firmatario']->content();
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

        OpenPAConsiglioPdf::create( 'Seduta', $content );
        eZExecution::cleanExit();
    }
}