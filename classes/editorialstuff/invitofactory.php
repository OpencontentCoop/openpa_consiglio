<?php

class InvitoFactory extends OpenPAConsiglioDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    public function instancePost($data)
    {
        return new Invito($data, $this);
    }

    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module,
        $version = false
    ) {
        $currentPost = $this->getModuleCurrentPost($parameters, $handler, $module);

        $getParameters = $_GET;

        $locale = eZLocale::instance();

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $currentPost->getObject()->dataMap();
        $puntoFactory = OCEditorialStuffHandler::instance('punto')->getFactory();

        $user = eZContentObject::fetch($dataMap['user']->content()->ID);
        $userDataMap = $user->dataMap();

        /** @var Punto[] $punti */
        $punti = array();
        $listPunti = explode('-', $dataMap['object']->toString());
        $seduta = null;
        foreach ($listPunti as $puntoId) {
            try {
                /** @var Punto $punto */
                $punto = new Punto(array('object_id' => $puntoId), $puntoFactory);
                /** @var eZContentObjectAttribute[] $puntoDataMap */
                $puntoDataMap = $punto->getObject()->dataMap();

                $punti [$puntoDataMap['n_punto']->content()] = array(
                    'n_punto' => $puntoDataMap['n_punto']->content(),
                    'ora' => $locale->formatShortTime($puntoDataMap['orario_trattazione']->content()->attribute('timestamp')),
                    'oggetto' => $puntoDataMap['oggetto']->content()
                );

                if (!$seduta instanceof Seduta) {
                    $seduta = $punto->getSeduta();
                }

            } catch (Exception $e) {
                eZDebug::writeError($e->getMessage());
            }

        }
        ksort($punti);
        $variables = array();

        if (!empty( $punti ) && $seduta instanceof Seduta) {

            $punti = array_values($punti);

            $oraInvito = isset( $dataMap['ora'] ) && $dataMap['ora']->hasContent() ? $locale->formatShortTime($dataMap['ora']->content()->attribute('timestamp')) : $punti[0]['ora'];

            /** @var eZContentObjectAttribute[] $sedutaDataMap */
            $sedutaDataMap = $seduta->getObject()->dataMap();

            $listOrgano = $sedutaDataMap['organo']->content();
            $organo = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);

            $variables = array(
                'line_height' => isset( $getParameters['line_height'] ) ? $getParameters['line_height'] : 1.2,
                'data' => $currentPost->getObject()->attribute('published'),
                'sesso' => isset( $userDataMap['sesso'] ) ? $userDataMap['sesso']->toString() : '',
                'invitato' => $userDataMap['titolo']->content() . ' ' . $userDataMap['nome']->content() . ' ' . $userDataMap['cognome']->content(),
                'ruolo' => nl2br($userDataMap['ruolo']->content()),
                'indirizzo' => isset( $userDataMap['indirizzo'] ) ? nl2br($userDataMap['indirizzo']->content()) : '',
                'luogo' => isset( $sedutaDataMap['luogo'] ) ? $sedutaDataMap['luogo']->content() : '',
                'organo' => $organo instanceof eZContentObject ? $organo->attribute('name') : '',
                'data_seduta' => ( $seduta instanceof Seduta ) ? $seduta->dataOra() : null,
                'ora_invito' => $oraInvito,
                'punti' => $punti,
                'descrizione_firmatario' => 'Il Presidente',
                'firmatario' => '',
                'firma' => '',
                'protocollo' => isset( $dataMap['protocollo'] ) ? $dataMap['protocollo']->toString() : '',
            );

            if ($sedutaDataMap['firmatario']->hasContent()) {
                $listFirmatario = $sedutaDataMap['firmatario']->content();
                $firmatario = eZContentObject::fetch(
                    $listFirmatario['relation_list'][0]['contentobject_id']
                );
                /** @var eZContentObjectAttribute[] $firmatarioDataMap */
                $firmatarioDataMap = $firmatario->dataMap();

                $variables['firmatario'] = str_replace('(CCT)', '', $firmatario->attribute('name'));
                if ($firmatarioDataMap['firma']->hasContent()
                    && $firmatarioDataMap['firma']->attribute('data_type_string') == 'ezimage'
                ) {
                    $image = $firmatarioDataMap['firma']->content()->attribute('original');
                    $url = $image['url'];
                    eZURI::transformURI($url, false, 'full');
                    $variables['firma'] = $url;
                }

                if (isset( $firmatarioDataMap['pre_firma'] ) && $firmatarioDataMap['pre_firma']->hasContent()) {
                    $variables['descrizione_firmatario'] = $firmatarioDataMap['pre_firma']->toString();
                }
            }
        }

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ($variables as $name => $value) {
            $tpl->setVariable($name, $value);
        }
        $content = $tpl->fetch('design:pdf/invito/invito.tpl');

        /** @var eZContentClass $objectClass */
        $objectClass = $currentPost->getObject()->attribute('content_class');
        $languageCode = eZContentObject::defaultLanguage();
        $fileName = $objectClass->urlAliasName($currentPost->getObject(), false, $languageCode);
        $fileName = eZURLAliasML::convertToAlias($fileName);
        $fileName .= '.pdf';

        $pdfParameters = array(
            'exporter' => 'paradox',
            'cache' => array(
                'keys' => array(),
                'subtree_expiry' => '',
                'expiry' => -1,
                'ignore_content_expiry' => false
            )
        );

        if (eZINI::instance()->variable('DebugSettings', 'DebugOutput') == 'enabled') {
            echo '<pre>' . htmlentities($content) . '</pre>';
            eZDisplayDebug();
        } else {
            OpenPAConsiglioPdf::create($fileName, $content, $pdfParameters);
        }
        eZExecution::cleanExit();

    }
}
