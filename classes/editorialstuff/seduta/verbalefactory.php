<?php

class VerbaleFactory extends OpenPAConsiglioDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    use OpenPAConsiglioConfigurableTrait;

    public function __construct($configuration)
    {
        parent::__construct($configuration);
        $this->overrideConfiguration($this->configuration);
    }

    public function instancePost($data)
    {
        return new Verbale($data, $this);
    }

    protected function getPdfContentFromVersion(eZContentObjectVersion $objectVersion, $parameters = array())
    {
        if (!$objectVersion instanceof eZContentObjectVersion) {
            throw new Exception("ObjectVersion not found");
        }

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $objectVersion->dataMap();
        $variables = array(
            'line_height' => isset( $parameters['line_height'] ) ? $parameters['line_height'] : 1.2,
            'text' => $dataMap[Verbale::$textIdentifier]
        );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ($variables as $name => $value) {
            $tpl->setVariable($name, $value);
        }

        return $tpl->fetch('design:pdf/seduta/verbale.tpl');

    }

    public function generatePdf(Verbale $currentPost, $version = false)
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
            $cacheDirectory = eZSys::cacheDirectory();
            $directory = eZDir::path(array($cacheDirectory, 'pdf_creator'));
            eZFile::create($fileName, $directory, $fileContent);
            $tempFile = $directory . '/' . $fileName;
            $dataMap['file']->fromString($tempFile);
            $dataMap['file']->store();
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

    public function overrideConfiguration(&$configuration)
    {
        $needRootFactories = array_keys($this->getConfigurationProvider()->getContainerDashboards());
        $factoryIdentifier = $configuration['identifier'];
        if(empty($configuration['CreationRepositoryNode']) && in_array($factoryIdentifier, $needRootFactories)) {
            $configuration['CreationRepositoryNode'] = $this->getConfigurationProvider()->getRepositoryRootNodeId($factoryIdentifier);
        }
        if(empty($configuration['RepositoryNodes'])) {
            if (in_array($factoryIdentifier, $needRootFactories)){
                $configuration['RepositoryNodes'] = array(1, $configuration['CreationRepositoryNode']);
            }else{
                $configuration['RepositoryNodes'] = array(1);
            }
        }
        $configuration['PersistentVariable'] = $this->getConfigurationProvider()->getRepositoryPersistentVariable($factoryIdentifier);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/verbale';
    }

    public function editModuleResult($parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module)
    {
        $currentPost = $this->getModuleCurrentPost($parameters, $handler, $module);
        if ($currentPost instanceof Punto) {
            $seduta = $currentPost->attribute('seduta');
            if (( $seduta instanceof Seduta && !$seduta->getObject()->attribute('can_read') ) || !$seduta instanceof Seduta) {
                return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
            }
        }

        $tpl = $this->editModuleResultTemplate($currentPost, $parameters, $handler, $module);

        $Result = array();
        $Result['content'] = $tpl->fetch("design:{$this->getTemplateDirectory()}/edit.tpl");
        $tpl->setVariable('site_title', false);
        $contentInfoArray = array('url_alias' => 'editorialstuff/dashboard');
        $contentInfoArray['persistent_variable'] = array('show_path' => true, 'site_title' => 'Dashboard');
        if (is_array($tpl->variable('persistent_variable'))) {
            $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'],
                $tpl->variable('persistent_variable'));
        }
        if (isset( $this->configuration['PersistentVariable'] ) && is_array($this->configuration['PersistentVariable'])) {
            $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'],
                $this->configuration['PersistentVariable']);
        }
        $tpl->setVariable('persistent_variable', false);
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
        if ($currentPost instanceof Verbale) {
            $seduta = $currentPost->attribute('seduta');
            if (( $seduta instanceof Seduta && !$seduta->getObject()->attribute('can_read') ) || !$seduta instanceof Seduta) {
                return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
            }
            $sedutaFactoryConfiguration = OCEditorialStuffHandler::instance('seduta')->getFactory()->getConfiguration();
            if ($seduta instanceof Seduta) {
                $sedutaObject = $seduta->getObject();
                if ($sedutaObject instanceof eZContentObject) {
                    $Result['path'][] = array(
                        'text' => isset( $sedutaFactoryConfiguration['Name'] ) ? $sedutaFactoryConfiguration['Name'] : 'Dashboard',
                        'url' => 'editorialstuff/dashboard/seduta/'
                    );
                    $Result['path'][] = array(
                        'text' => $sedutaObject->attribute('name'),
                        'url' => 'editorialstuff/edit/seduta/' . $seduta->id()
                    );
                }
            }
            $Result['path'][] = array('url' => false, 'text' => 'Verbale');
        } else {
            return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
        }

        return $Result;
    }
}
