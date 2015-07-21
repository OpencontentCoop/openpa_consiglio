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

        $getParameters = array();

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $currentPost->getObject()->dataMap();
        $instance = OCEditorialStuffHandler::instance( 'punto' );

        $user = eZContentObject::fetch( $dataMap['user']->content()->ID );
        $userDataMap = $user->dataMap();

        $punti = array();
        $punto = false;
        $listPunti = $dataMap['object']->content();

        foreach ( $listPunti['relation_list'] as $p )
        {
            $tempPunto = eZContentObject::fetch( $p['contentobject_id'] );
            $puntoDataMap = $tempPunto->dataMap();

            if ( !$punto )
            {
                /** @var Punto $punto */
                $punto = $instance->getFactory()->instancePost(
                    array( 'object_id' => $p['contentobject_id'] )
                );
                /** @var eZContentObjectAttribute[] $puntoDataMap */
                $puntoDataMap = $punto->getObject()->dataMap();
            }

            $punti [$puntoDataMap['n_punto']->content()] = array(
                'n_punto' => $puntoDataMap['n_punto']->content(),
                'ora' => $puntoDataMap['orario_trattazione']->toString(),
                'oggetto' => $puntoDataMap['oggetto']->content()
            );
        }
        ksort( $punti );
        $punti = array_values( $punti );
        $first = array_shift( $punti );
        $ora = $first['ora'];

        $seduta = $punto->getSeduta()->getObject();
        /** @var eZContentObjectAttribute[] $sedutaDataMAp */
        $sedutaDataMAp = $seduta->dataMap();

        $listOrgano = $sedutaDataMAp['organo']->content();
        $organo = eZContentObject::fetch( $listOrgano['relation_list'][0]['contentobject_id'] );

        $variables = array(
            'line_height' => isset( $getParameters['line_height'] ) ? $getParameters['line_height'] : 1.2,
            'data' => $currentPost->getObject()->attribute( 'published' ),
            'invitato' => $userDataMap['titolo']->content() . ' ' . $userDataMap['nome']->content(
                ) . ' ' . $userDataMap['cognome']->content(),
            'ruolo' => $userDataMap['ruolo']->content(),
            'indirizzo' => isset( $userDataMap['indirizzo'] ) ? $userDataMap['indirizzo']->content(
            ) : '',
            'luogo' => isset( $sedutaDataMAp['luogo'] ) ? $sedutaDataMAp['luogo']->content() : '',
            'organo' => $organo->Name,
            'data_seduta' => $punto->getSeduta()->dataOra(),
            'punti' => $punti

        );

        if ( $sedutaDataMAp['firmatario']->hasContent() )
        {
            $listFirmatario = $sedutaDataMAp['firmatario']->content();
            $firmatario = eZContentObject::fetch(
                $listFirmatario['relation_list'][0]['contentobject_id']
            );
            /** @var eZContentObjectAttribute[] $firmatarioDataMAp */
            $firmatarioDataMAp = $firmatario->dataMap();

            $variables['firmatario'] = $firmatario->Name;
            if ( $firmatarioDataMAp['firma']->hasContent() )
            {
                $siteINI = eZINI::instance( 'site.ini' );
                $siteUrl = $siteINI->variable( 'SiteSettings', 'SiteURL' );
                $variables['firma'] = '';
                if ( isset( $firmatarioDataMAp['firma'] )
                     && $firmatarioDataMAp['firma']->attribute( 'data_type_string' ) == 'ezimage'
                     && $firmatarioDataMAp['firma']->hasContent()
                )
                {
                    $image = $firmatarioDataMAp['firma']->content()->attribute( 'original' );
                    $variables['firma'] = $siteUrl . '/' . $image['url'];
                }
            }
        }

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ( $variables as $name => $value )
        {
            $tpl->setVariable( $name, $value );
        }
        $content = $tpl->fetch( 'design:pdf/invito/invito.tpl' );

        $fileName = $currentPost->attribute( 'name' );
        /** @var eZContentClass $objectClass */
        $objectClass = $currentPost->getObject()->attribute( 'content_class' );
        $languageCode = eZContentObject::defaultLanguage();
        $fileName = $objectClass->urlAliasName( $currentPost->getObject(), false, $languageCode );
        $fileName = eZURLAliasML::convertToAlias( $fileName );
        $fileName .= '.pdf';
        OpenPAConsiglioPdf::create( $fileName, $content );

        eZExecution::cleanExit();

    }
}