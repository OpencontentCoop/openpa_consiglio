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
        eZModule $module,
        $version = false
    )
    {
        $currentPost = $this->getModuleCurrentPost($parameters, $handler, $module);
        $dataMap = $currentPost->attribute('object')->dataMap();
        $instance = OCEditorialStuffHandler::instance( 'punto' );

        $user = eZContentObject::fetch($dataMap['user']->content()->ID);
        $userDataMap = $user->dataMap();

        /** @var Punto $punto */
        $punto = $instance->getFactory()->instancePost( array( 'object_id' => $dataMap['object']->content()->attribute( 'id' ) ) );
        $puntoDataMap = $punto->getObject()->dataMap();

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
                    'termine_osservazioni' => strftime(
                        '%d/%m/%Y  alle ore %H:%M',
                        $tempDataMap['termine_osservazioni']->toString()
                    )
                );
            }
        }




        $seduta = $punto->getSeduta()->getObject();
        $sedutaDataMAp = $seduta->dataMap();

        $listOrgano = $sedutaDataMAp['organo']->content();
        $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

        $variables = array(
            'line_height' => 20,
            'data'        => strftime( '%d %m %Y', time()), // Todo: chiedere se va bene data oggetto
            'invitato'    => $userDataMap['titolo']->content() . ' ' . $userDataMap['nome']->content() . ' ' . $userDataMap['cognome']->content(),
            'ruolo'       => $userDataMap['ruolo']->content(),
            //'indirizzo'   => $userDataMap['indirizzo']->content(),
            'organo'      => $organo->Name,
            'data_seduta' => strftime( '%A %d %B %Y, alle ore %H:%M', $punto->getSeduta( true )->dataOra()),
            'n_punto'     => $puntoDataMap['n_punto']->content(),
            'oggetto'     => $puntoDataMap['oggetto']->content(),

        );

        if ($sedutaDataMAp['firmatario']->hasContent())
        {
            $listFirmatario = $sedutaDataMAp['firmatario']->content();
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
        $content = $tpl->fetch( 'design:pdf/invito/invito.tpl' );

        OpenPAConsiglioPdf::create( 'Invito', $content );
        eZExecution::cleanExit();


    }
}