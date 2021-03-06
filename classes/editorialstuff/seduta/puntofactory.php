<?php

class PuntoFactory extends OpenPAConsiglioNotifiableFactory
{

    /**
     * @var Seduta
     */
    protected $seduta;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
        $this->configuration['CreationRepositoryNode'] = 0;
        $this->configuration['RepositoryNodes'] = array();
        if (isset( $this->configuration['RuntimeParameters']['seduta'] )) {
            $sedutaID = $this->configuration['RuntimeParameters']['seduta'];
            $this->setSeduta($sedutaID);
        }
    }

    public function setSeduta($sedutaID)
    {
        if ($sedutaID instanceof Seduta) {
            $this->seduta = $sedutaID;
        } else {
            try {
                $this->seduta = OCEditorialStuffHandler::instance('seduta')->fetchByObjectId(
                    $sedutaID
                );
            } catch (Exception $e) {
                eZDebug::writeError($e->getMessage(), __METHOD__);
            }
        }
        if ($this->seduta instanceof Seduta) {
            $sedutaNodeId = $this->seduta->getObject()->attribute('main_node_id');
            $this->configuration['CreationRepositoryNode'] = $sedutaNodeId;
            $this->configuration['RepositoryNodes'] = array($sedutaNodeId);
        }
    }

    /**
     * @return array[]
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = array(
            'solr_identifier' => "extra_orario_i",
            'object_property' => 'orario',
            'attribute_identifier' => 'orario',
            'index_extra' => true,
            'index_plugin_call_function' => 'indexOrario'
        );

        return $fields;
    }


    public function getSeduta()
    {
        return $this->seduta;
    }

    public function instancePost($data)
    {
        return new Punto($data, $this);
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/punto';
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
        if ($currentPost instanceof Punto) {
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
            $Result['path'][] = array('url' => false, 'text' => $currentPost->getObject()->attribute('name'));
        } else {
            return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
        }

        return $Result;
    }

    /**
     * @return array[] array( 'type' => array( 'handler_method' => <methodName> ) )
     */
    public function notificationEventTypesConfiguration()
    {
        return array(
            'publish' => array(
                'name' => 'Pubblicazione del punto',
                'handler_method' => 'handlePublishNotification',
            ),
            'update_referenti' => array(
                'name' => 'Aggiornamento dei referenti',
                'handler_method' => 'handleUpdateReferentiNotification'
            ),
            'update_termini' => array(
                'name' => 'Aggiornamento dei termini di scadenza per le osservazioni',
                'handler_method' => 'handleUpdateTerminiNotification'
            ),
            'move' => array(
                'name' => 'Spostamento del punto in un\'altra seduta',
                'handler_method' => 'handleMoveNotification'
            ),
            'change_allegati' => array(
                'name' => 'Modifica sugli allegati',
                'handler_method' => 'handleChangeAllegatiNotification'
            ),
            'add_osservazione' => array(
                'name' => 'Inserimento di un\'osservazione al punto',
                'handler_method' => 'handleAddOsservazioneNotification'
            )
        );
    }
}
