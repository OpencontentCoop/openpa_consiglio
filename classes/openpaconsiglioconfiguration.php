<?php

class OpenPAConsiglioConfiguration implements OCPageDataHandlerInterface
{
    /**
     * @var OpenPAConsiglioConfiguration
     */
    private static $instance;

    private function __construct()
    {
    }

    final public static function instance()
    {
        if (self::$instance === null) {
            $configurationClassName = eZINI::instance('openpa.ini')->variable(
                'OpenPAConsiglio',
                'ConfigurationClass'
            );
            if (!class_exists($configurationClassName)) {
                throw new Exception("OpenPAConsiglio ConfigurationClass $configurationClassName not found");
            }
            self::$instance = new $configurationClassName();
        }

        return self::$instance;
    }

    public function getSyncClassRemoteHost()
    {
        return null;
    }

    public function useApp()
    {
        return null;
    }

    public function enableVotazioniinCruscotto()
    {
        return null;
    }

    public function getCurrentSiteaccessIdentifier()
    {
        return null;
    }

    public function getRepositoryRootRemoteId($repositoryIdentifier)
    {
        return null;
    }

    public function getRepositoryRootNodeId($repositoryIdentifier)
    {
        return null;
    }

    public function getRepositoryRootNodePathString($repositoryIdentifier)
    {
        return null;
    }

    public function getRepositoryPersistentVariable($repositoryIdentifier)
    {
        return null;
    }

    public function getAlertsContainerNodeId()
    {
        return null;
    }

    public function getSocketInfo()
    {
        return array(
            'url' => null,
            'port' => null,
            'js_url' => null,
        );
    }

    public function getBackendEndPoint()
    {
        return null;
    }

    public function isMailDebug()
    {
        return true;
    }

    public function getMailDebugAdress()
    {
        return null;
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

    public function getActiveDashboards()
    {
       return array(
           'seduta' => true,
           'materia' => true,
           'invitato' => true,
           'referentelocale' => true,
           'areacollaborativa' => true,
           'organo' => true,
           'tecnico' => true,
           'politico' => true,
           'proposta' => true,
           'responsabilearea' => true,
           'cda_evento' => true,
           'cda_documento' => true,
           'verbale' => true,
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
        return null;
    }

    public function siteUrl()
    {
        return null;
    }

    public function assetUrl()
    {
        return null;
    }

    public function logoPath()
    {
        return null;
    }

    public function logoTitle()
    {
        return null;
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
