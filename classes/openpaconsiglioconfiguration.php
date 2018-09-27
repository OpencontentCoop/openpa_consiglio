<?php

class OpenPAConsiglioConfiguration implements OCPageDataHandlerInterface
{
    /**
     * @var OpenPAConsiglioConfiguration
     */
    private static $instance;

    final public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new OpenPAConsiglioConfiguration();
        }

        return self::$instance;
    }


    public function getRepositoryRootRemoteId($repositoryIdentifier)
    {
        return 'openpa_consiglio_' . $repositoryIdentifier;
    }

    public function getRepositoryRootNodeId($repositoryIdentifier)
    {
        $remote = $this->getRepositoryRootRemoteId($repositoryIdentifier);
        if ($object = eZContentObject::fetchByRemoteID($remote)){
            return $object->attribute('main_node_id');
        }
        return null;
    }

    public function getRepositoryRootNodePathString($repositoryIdentifier)
    {
        $remote = $this->getRepositoryRootRemoteId($repositoryIdentifier);
        if ($object = eZContentObject::fetchByRemoteID($remote)){
            return $object->attribute('main_node')->attribute('path_string');
        }
        return null;
    }

    public function getRepositoryPersistentVariable($repositoryIdentifier)
    {
        return array(
            'top_menu' => true,
            'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
        );
    }

    public function getAlertsContainerNodeId()
    {
        return 0;
    }

    public function getAvailableClasses()
    {
        return array(
            'allegato_seduta',
            'openpa_consiglio_collaboration_area',
            'openpa_consiglio_collaboration_room',
            'openpa_consiglio_collaboration_comment',
            'openpa_consiglio_collaboration_file',
            'convocazione_seduta',
            'invitato',
            'invito',
            'materia',
            'osservazione',
            'punto',
            'seduta',
            'votazione',
            'organo_sociale',
            'rendiconto_spese',
            'proposta_punto',
            'verbale',
            'event',
            'file',
        );
    }

    public function getContainerDashboards()
    {
        return array(
            'seduta' => 'folder',
            'punto' => 'folder',
            'allegati_seduta' => 'folder',
            'invitato' => 'user_group',
            'invito' => 'folder',
            'politico' => 'user_group',
            'tecnico' => 'user_group',
            'materia' => 'folder',
            'organo' => 'folder',
            'convocazione_seduta' => 'folder',
            'votazione' => 'folder',
            'osservazioni' => 'folder',
            'referentelocale' => 'user_group',
            'areacollaborativa' => 'folder',
            'rendiconto_spese' => 'folder', // non è una dashboard
            'proposta' => 'folder',
            'responsabilearea' => 'user_group',
            'cda_evento' => 'folder',
            'cda_documento' => 'folder',
        );
    }

    public function getDashboards()
    {
        return array(
            'seduta',
            'materia',
            'invitato',
            'referentelocale',
            'areacollaborativa',
            'organo',
            'tecnico',
            'politico',
            'proposta',
            'responsabilearea',
            'cda_evento',
            'cda_documento',
            'verbale',
        );
    }

    public function calcolaImportGettone($percentuale)
    {
        $importoMassimo = 120;
        $base = 0;
        if ( $percentuale > 0 )
        {
            $base = 100;
        }
                
        return number_format( ( intval( $base ) * $importoMassimo / 100 ), 2 );
    }

    public function calcolaLivelloGettone($percentuale)
    {
        if ($percentuale > 0) {
            return 'success';        
        } else {
            return 'danger';
        }
    }

    public function siteTitle()
    {
        return OpenPAConsiglioSettings::instance()->getGlobalSetting('site_title')->attribute('value');
    }

    public function siteUrl()
    {
        return OpenPAConsiglioSettings::instance()->getGlobalSetting('site_url')->attribute('value');
    }

    public function assetUrl()
    {
        return OpenPAConsiglioSettings::instance()->getGlobalSetting('asset_url')->attribute('value');
    }

    public function logoPath()
    {
        return OpenPAConsiglioSettings::instance()->getGlobalSetting('logo_path')->attribute('value');
    }

    public function logoTitle()
    {
        return OpenPAConsiglioSettings::instance()->getGlobalSetting('site_title')->attribute('value');
    }

    public function logoSubtitle()
    {
        return null;
    }

    public function headImages()
    {
        return array();
    }

    public function needLogin()
    {
        return null;
    }

    public function attributeContacts()
    {
        return null;
    }

    public function attributeFooter()
    {
        return null;
    }

    public function textCredits()
    {
        return null;
    }

    public function googleAnalyticsId()
    {
        return null;
    }

    public function cookieLawUrl()
    {
        return null;
    }

    public function menu()
    {
        return array();
    }

    public function userMenu()
    {
        return array();
    }

    public function bannerPath()
    {
        return null;
    }

    public function bannerTitle()
    {
        return null;
    }

    public function bannerSubtitle()
    {
        return null;
    }
}
