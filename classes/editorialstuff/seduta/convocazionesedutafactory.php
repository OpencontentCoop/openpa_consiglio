<?php

class ConvocazioneSedutaFactory extends OpenPAConsiglioDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    public function instancePost($data)
    {
        return new ConvocazioneSeduta($data, $this);
    }

    protected function getPdfContentFromVersion(eZContentObjectVersion $objectVersion, $parameters = array())
    {
        if (!$objectVersion instanceof eZContentObjectVersion) {
            throw new Exception("ObjectVersion not found");
        }

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $objectVersion->dataMap();

        /** @var eZContentObject $seduta */
        $seduta = $dataMap['seduta']->content();

        /** @var eZContentObjectAttribute[] $sedutaDataMap */
        $sedutaDataMap = $seduta->dataMap();

        $odg = json_decode($dataMap['odg']->content(), true);

        $listOrgano = $sedutaDataMap['organo']->content();
        $organo = '';
        $destinatariOrgano = false;
        if (isset( $listOrgano['relation_list'][0]['contentobject_id'] )){
            $organoObject = eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id']);
            if($organoObject instanceof eZContentObject){
                $organo = $organoObject->attribute('name');
                $organoObjectDataMap = $organoObject->dataMap();
                if(isset($organoObjectDataMap['stringa_destinatari']) && $organoObjectDataMap['stringa_destinatari']->hasContent()){
                    $destinatariOrgano = $organoObjectDataMap['stringa_destinatari']->toString();
                }
            }
        }        

        $luogo = isset( $sedutaDataMap['luogo'] ) ? $sedutaDataMap['luogo']->content() : '';

        $dataOra = isset( $dataMap['data_ora'] ) ? $dataMap['data_ora']->toString() : 0;

        $oraConclusione = isset( $sedutaDataMap['orario_conclusione'] ) && $sedutaDataMap['orario_conclusione']->hasContent() ? $sedutaDataMap['orario_conclusione']->content() : null;

        $protocollo = isset( $dataMap['protocollo'] ) ? $dataMap['protocollo']->toString() : 0;

        $variables = array(
            'line_height' => isset( $parameters['line_height'] ) ? $parameters['line_height'] : 1.2,
            'data' => $objectVersion->attribute('created'),
            'luogo' => $luogo,
            'organo' => $organo,
            'destinatari_organo' => $destinatariOrgano,
            'data_seduta' => $dataOra,
            'ora_conclusione' => $oraConclusione,
            'odg' => $odg,
            'descrizione_firmatario' => 'Il Presidente',
            'firmatario' => '',
            'firma' => '',
            'protocollo' => $protocollo
        );

        if ($sedutaDataMap['firmatario']->hasContent()) {
            $listFirmatario = $sedutaDataMap['firmatario']->content();
            if (isset( $listFirmatario['relation_list'][0]['contentobject_id'] )) {
                $firmatario = eZContentObject::fetch(
                    $listFirmatario['relation_list'][0]['contentobject_id']
                );
                /** @var eZContentObjectAttribute[] $firmatarioDataMap */
                $firmatarioDataMap = $firmatario->dataMap();

                $variables['firmatario'] = str_replace('(CCT)', '', $firmatario->attribute('name'));
                if (isset($firmatarioDataMap['firma']) && $firmatarioDataMap['firma']->hasContent()
                    && $firmatarioDataMap['firma']->attribute('data_type_string') == 'ezimage'
                ) {
                    $image = $firmatarioDataMap['firma']->content()->attribute('original');
                    $url = $image['url'];
                    eZURI::transformURI($url, true);
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

        return $tpl->fetch('design:pdf/seduta/seduta.tpl');

    }

    public function generatePdf(ConvocazioneSeduta $currentPost, $version = false)
    {
        $currentPost->flushObject();
        if (!$version) {
            $content = $this->getPdfContentFromVersion(
                $currentPost->getObject()->currentVersion(),
                $_GET
            );
            $revision = $currentPost->getObject()->attribute('current_version');
        } else {
            $content = $this->getPdfContentFromVersion(
                $currentPost->getObject()->version($version),
                $_GET
            );
            $revision = $version;
        }

        /** @var eZContentClass $objectClass */
        $objectClass = $currentPost->getObject()->attribute('content_class');
        $languageCode = eZContentObject::defaultLanguage();
        $fileName = $objectClass->urlAliasName($currentPost->getObject(), false, $languageCode);
        $fileName = eZURLAliasML::convertToAlias($fileName);
        $fileName .= '-rev' . $revision . '.pdf';

        $pdfParameters = array(
            'exporter' => 'paradox',
            'cache' => array(
                'keys' => array(),
                'subtree_expiry' => '',
                'expiry' => -1,
                'ignore_content_expiry' => false
            )
        );

        $exportData = OpenPAConsiglioPdf::create($fileName, $content, $pdfParameters, false);
        $exportData['filename'] = $fileName;
        $fileContent = $exportData['content'];

        if (!$version) {
            $dataMap = $currentPost->getObject()->dataMap();
            /** @var eZContentObject $seduta */
            $seduta = $dataMap['seduta']->content();
            $sedutaDataMap = $seduta->dataMap();

            $cacheDirectory = eZSys::cacheDirectory();
            $directory = eZDir::path(array($cacheDirectory, 'pdf_creator'));
            eZFile::create($fileName, $directory, $fileContent);
            $tempFile = $directory . '/' . $fileName;
            
            $sedutaDataMap['convocazione']->fromString($tempFile);
            $sedutaDataMap['convocazione']->store();
            
            $handler = eZFileHandler::instance(false);
            $handler->unlink($tempFile);
        }

        return $exportData;
    }

    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module,
        $version = false
    ) {
        $currentPost = $this->getModuleCurrentPost($parameters, $handler, $module);
        $exportData = $this->generatePdf($currentPost, $version);

        if (eZINI::instance()->variable('DebugSettings', 'DebugOutput') == 'enabled') {
            echo '<pre>' . htmlentities($exportData['raw_content']) . '</pre>';
            eZDisplayDebug();
        } else {
            /** @var ParadoxPDF $paradoxPdf */
            $paradoxPdf = $exportData['exporter'];
            $fileContent = $exportData['content'];
            $size = strlen($fileContent);
            $paradoxPdf->flushPDF($fileContent, $exportData['filename'], $size, null, null);

        }
        eZExecution::cleanExit();

        return true;
    }

}
