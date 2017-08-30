<?php

class OpenPAConsiglioConfiguration
{
    /**
     * @var OpenPAConsiglioConfiguration
     */
    private static $instance;

    public static function instance()
    {
        if (self::$instance === null)
            self::$instance = new OpenPAConsiglioConfiguration(); //@todo

        return self::$instance;
    }

    public function useApp()
    {
        return false;
    }

    public function enableVotazioniinCruscotto()
    {
        return false;
    }

    public function getCurrentSiteaccessIdentifier()
    {
        return 'ftcoop';
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

    public function getRepositoryPersistentVariable($repositoryIdentifier)
    {
        return array(
            'top_menu' => true,
            'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
        );
    }

    public function getAlertsContainerNodeId()
    {
        /*
        {def $alerts_container = openpaini( 'OpenPAConsiglio', 'DashboardAlertsContainerNode', false() )} //@todo
         */
        return 0;
    }

    public function getSocketInfo()
    {
        /*
        {openpaini('OpenPAConsiglio','SocketUrl','cal')}
        {openpaini('OpenPAConsiglio','SocketPort','8090')}
        */
        return array(
            'url' => 'ftcoop.local',
            'port' => '8090',
        );
    }

    public function getBackendEndPoint()
    {
        //OpenPAINI::variable( 'OpenPAConsiglio', 'BackendEndPoint' )
        return '';
    }

    public function isMailDebug()
    {
        //OpenPAINI::variable( 'OpenPAConsiglio', 'UseMailDebug', 'true' )
        return true;
    }

    public function getMailDebugAdress()
    {
        //OpenPAINI::variable( 'OpenPAConsiglio', 'UseMailDebugAddress', 'lr@opencontent.it' )
        return 'lr@opencontent.it';
    }

    public function getAvailableClasses()
    {
        return array('rendiconto_spese');
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
            'organo_sociale'
        );
    }

    public function getContainerDashboards()
    {
        return array(
            'seduta',
            //'punto',
            'allegati_seduta',
            'invitato',
//            'invito',
//            'politico',
//            'tecnico',
            'materia',
            'organo',
//            'convocazione_seduta',
            'votazione',
            'osservazioni',
            'referentelocale',
            'areacollaborativa',
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
       );
    }

    public function calcolaImportGettone($percentuale)
    {
        $importoMassimo = 120;
        $base = 0;
        if ( $percentuale >= 75 )
        {
            $base = 100;
        }
        elseif ( $percentuale < 75 && $percentuale >= 25 )
        {
            $base = 50;
        }
        return number_format( ( intval( $base ) * $importoMassimo / 100 ), 2 );
    }

    public function calcolaLivelloGettone($percentuale)
    {
        if ($percentuale >= 75) {
            return 'success';
        } elseif ($percentuale >= 25) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
