<?php

class OpenPAConsiglioSettings
{
    private $values = array();

    public static function instance()
    {
        return new self();
    }

    private function __construct()
    {
        $this->loadValues();
    }

    public function loadValues()
    {
        $this->values = array();
        $availableSettings = $this->availableGlobalSettings();
        $filterCond = array();
        foreach ($availableSettings as $identifier => $availableSetting) {
            $filterCond[] = $availableSetting['stored_name'];
        }
        $values = eZSiteData::fetchObjectList(
            eZSiteData::definition(),
            null,
            array('name' => array($filterCond))
        );

        foreach ($availableSettings as $identifier => $availableSetting) {
            foreach ($values as $value) {
                if ($value->attribute('name') == $availableSetting['stored_name']) {
                    $this->values[$identifier] = $value;
                }
            }
            if (!isset($this->values[$identifier])) {
                $this->values[$identifier] = new eZSiteData(array('name' => $availableSetting['stored_name']));
            }
        }
    }

    public function availableGlobalSettings()
    {
        return array(
            'identifier' => array(
                'stored_name' => 'OpenPAConsiglio.Identifier',
                'name' => "Identificatore",
                'help_text' => "Inserisci una stringa identificativa dell'installazione",
                'type' => 'text'
            ),
            'site_title' => array(
                'stored_name' => 'OpenPAConsiglio.SiteTitle',
                'name' => "Titolo",
                'help_text' => "Inserisci l'intestazione del sito (compare nelle mail)",
                'type' => 'text'
            ),
            'site_url' => array(
                'stored_name' => 'OpenPAConsiglio.SiteUrl',
                'name' => "URL",
                'help_text' => "Inserisci l'indirizzo del sito (senza barra finale)",
                'type' => 'text'
            ),
            'asset_url' => array(
                'stored_name' => 'OpenPAConsiglio.AssetUrl',
                'name' => "Asset URL",
                'help_text' => "Inserisci l'indirizzo degli asset del sito (senza barra finale)",
                'type' => 'text'
            ),
            'logo_path' => array(
                'stored_name' => 'OpenPAConsiglio.LogoPath',
                'name' => "Percorso del logo (compare nelle mail)",
                'help_text' => "(esempio: extension/ftcoop-cda/design/cda/images/ftcoop/logo.png)",
                'type' => 'text'
            ),
            'mail_debug' => array(
                'stored_name' => 'OpenPAConsiglio.EnableMailDebug',
                'name' => "Mail",
                'help_text' => "Abilita il debug (invia tutte le mail a un solo indirizzo)",
                'type' => 'checkbox'
            ),
            'mail_debug_address' => array(
                'stored_name' => 'OpenPAConsiglio.MailDebugAddress',
                'name' => "Indirizzo mail di debug",
                'help_text' => "Specifica l'indirizzo di debug",
                'type' => 'text'
            ),
            'active_dashboards' => array(
                'stored_name' => 'OpenPAConsiglio.ActiveDashboards',
                'name' => "Abilita dashboard in menu",
                'type' => 'checkbox-list',
                'list' => OpenPAConsiglioConfiguration::instance()->getDashboards()
            ),
            'use_app' => array(
                'stored_name' => 'OpenPAConsiglio.EnableApp',
                'name' => "App",
                'help_text' => "Abilita all'utilizzo dell'app",
                'type' => 'checkbox'
            ),
            'use_voting' => array(
                'stored_name' => 'OpenPAConsiglio.EnableVoting',
                'name' => "Voto",
                'help_text' => "Abilita le votazioni nel cruscotto",
                'type' => 'checkbox'
            ),
            'socket_url' => array(
                'stored_name' => 'OpenPAConsiglio.SocketUrl',
                'name' => "Socket Url",
                'help_text' => "(esempio: 'devnginx2.opencontent.it')",
                'type' => 'text'
            ),
            'socket_port' => array(
                'stored_name' => 'OpenPAConsiglio.SocketPort',
                'name' => "Socket Port",
                'help_text' => "(esempio: '8091')",
                'type' => 'text'
            ),
            'socket_js_url' => array(
                'stored_name' => 'OpenPAConsiglio.SocketJsUrl',
                'name' => "Socket Js Url",
                'help_text' => "(esempio: 'devnginx2.opencontent.it:8091')",
                'type' => 'text'
            ),
            'backend_endpoint' => array(
                'stored_name' => 'OpenPAConsiglio.BackendEndpoint',
                'name' => "Backend Endpoint",
                'help_text' => "(esempio: 'http://example_endpoint.opencontent.it')",
                'type' => 'text'
            ),
            'api_local_server' => array(
                'stored_name' => 'OpenPAConsiglio.APILocalServerURL',
                'name' => 'URL server locale per servire i file da api',
                'help_text' => "Specifica un indirizzo http valido (ad esempio 'http://local.cal/cached_files'). Se lasci il campo vuoto il file saranno serviti dal server web pubblico",
                'type' => 'text'
            ),
        );
    }

    public function listGlobalSettings()
    {
        $data = array();
        $availableSettings = $this->availableGlobalSettings();
        foreach (array_keys($availableSettings) as $identifier) {
            $data[$identifier] = $availableSettings[$identifier];
            $data[$identifier]['value'] = $this->getGlobalSetting($identifier);
        }

        return $data;
    }

    public function storeGlobalSetting($identifier, $value)
    {
        $availableSettings = $this->availableGlobalSettings();
        if (isset($availableSettings[$identifier])) {
            $siteData = $this->getGlobalSetting($identifier);            
            if ($availableSettings[$identifier]['type'] == 'checkbox-list') {
                $value = implode('-', (array)$value);
            }
            $siteData->setAttribute('value', $value);
            $siteData->store();
            eZDebug::writeDebug($siteData, __METHOD__);
        }
    }

    /**
     * @param $identifier
     *
     * @return eZSiteData
     */
    public function getGlobalSetting($identifier)
    {
        return $this->values[$identifier];
    }

    public function localFileServerDownloadUrl(eZBinaryFile $binaryFile)
    {
        if ($binaryFile instanceof eZBinaryFile) {
            $url = $binaryFile->attribute('filename');
            $this->localFileServerTransformURI($url);

            return $url;
        }

        return null;
    }

    public function localFileServerIsEnabled()
    {
        return mb_strlen($this->localFileServerURL()) > 0;
    }

    public function localFileServerURL()
    {
        return $this->getGlobalSetting('api_local_server')->attribute('value');
    }

    public function useApp()
    {
        return $this->getGlobalSetting('use_app')->attribute('value') == 1;
    }

    public function enableVotazioniInCruscotto()
    {
        return $this->getGlobalSetting('use_voting')->attribute('value') == 1;
    }

    public function getIdentifier()
    {
        return $this->getGlobalSetting('identifier')->attribute('value');
    }

    public function getSocketInfo()
    {
        return array(
            'url' => $this->getGlobalSetting('socket_url')->attribute('value'),
            'port' => $this->getGlobalSetting('socket_port')->attribute('value'),
            'js_url' => $this->getGlobalSetting('socket_js_url')->attribute('value'),
        );
    }

    public function getBackendEndPoint()
    {
        return $this->getGlobalSetting('backend_endpoint')->attribute('value');
    }

    public function isMailDebug()
    {
        return $this->getGlobalSetting('mail_debug')->attribute('value') == 1;
    }

    public function getMailDebugAddress()
    {
        return $this->getGlobalSetting('mail_debug_address')->attribute('value');
    }

    public function getActiveDashboards()
    {
        return (array)explode('-', $this->getGlobalSetting('active_dashboards')->attribute('value'));
    }

    private function localFileServerTransformURI(&$href, $ignoreIndexDir = false)
    {
        if (preg_match("#^[a-zA-Z0-9]+:#", $href) || substr($href, 0, 2) == '//')
            return false;

        if (strlen($href) == 0)
            $href = '/';
        else if ($href[0] == '#') {
            $href = htmlspecialchars($href);
            return true;
        } else if ($href[0] != '/') {
            $href = '/' . $href;
        }

        $sys = eZSys::instance();
        $dir = !$ignoreIndexDir ? $sys->indexDir() : $sys->wwwDir();
        $serverURL = $this->localFileServerURL();
        $href = $serverURL . $dir . $href;
        if (!$ignoreIndexDir) {
            $href = preg_replace("#^(//)#", "/", $href);
            $href = preg_replace("#(^.*)(/+)$#", "\$1", $href);
        }
        $href = str_replace('&amp;amp;', '&amp;', htmlspecialchars($href));

        if ($href == "")
            $href = "/";

        return true;
    }
}
