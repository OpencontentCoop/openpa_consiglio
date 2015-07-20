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
        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );

        $dataMap = $currentPost->attribute('object')->dataMap();
        $instance = OCEditorialStuffHandler::instance( 'punto' );

        $user = eZContentObject::fetch($dataMap['user']->content()->ID);
        $userDataMap = $user->dataMap();

        $punti = array();
        $punto = false;
        $listPunti = $dataMap['object']->content();

        foreach ($listPunti['relation_list'] as $p)
        {
            $tempPunto = eZContentObject::fetch($p['contentobject_id']);
            $puntoDataMap = $tempPunto->dataMap();

            if (!$punto)
            {
                /** @var Punto $punto */
                $punto = $instance->getFactory()->instancePost( array( 'object_id' => $p['contentobject_id'] ) );
                $puntoDataMap = $punto->getObject()->dataMap();
            }

            $punti [$puntoDataMap['n_punto']->content()] = array(
                'n_punto' => $puntoDataMap['n_punto']->content(),
                'ora'     => $puntoDataMap['orario_trattazione']->toString(),
                'oggetto' => $puntoDataMap['oggetto']->content()
            );
        }
        ksort($punti);

        $first = array_shift(array_values($punti));
        $ora = $first['ora'];

        $seduta = $punto->getSeduta()->getObject();
        $sedutaDataMAp = $seduta->dataMap();

        $listOrgano = $sedutaDataMAp['organo']->content();
        $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

        $variables = array(
            'line_height' => 20,
            'data'        => strftime( '%d/%m/%Y', $currentPost->getObject()->Published),
            'invitato'    => $userDataMap['titolo']->content() . ' ' . $userDataMap['nome']->content() . ' ' . $userDataMap['cognome']->content(),
            'ruolo'       => $userDataMap['ruolo']->content(),
            'indirizzo'   => $userDataMap['indirizzo']->content(),
            'luogo'       => $sedutaDataMAp['luogo']->content(),
            'organo'      => $organo->Name,
            'data_seduta' => strftime( '%A %d %B %Y,', $punto->getSeduta()->dataOra()),
            'ora'         => $ora,
            'punti'       => $punti

        );

        if ($sedutaDataMAp['firmatario']->hasContent())
        {
            $listFirmatario = $sedutaDataMAp['firmatario']->content();
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