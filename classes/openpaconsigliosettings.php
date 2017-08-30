<?php

class OpenPAConsiglioSettings
{
    public static function instance()
    {
        return new self();
    }

    public function availableGlobalSettings()
    {
        return array(
            'api_local_server' => array(
                'stored_name' => 'OpenPAConsiglio.APILocalServerURL',
                'name' => 'URL server locale per servire i file da api',
                'help_text' => "Specifica un indirizzo http valido (ad esempio 'http://local.cal/cached_files'). Se lasci il campo vuoto il file saranno serviti dal server web pubblico"
            )
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
        if (isset( $availableSettings[$identifier] )) {
            $siteData = $this->getGlobalSetting($identifier);
            $siteData->setAttribute('value', $value);
            $siteData->store();
        }
    }

    /**
     * @param $identifier
     *
     * @return eZSiteData
     */
    public function getGlobalSetting($identifier)
    {
        $availableSettings = $this->availableGlobalSettings();
        $siteData = null;
        if (isset( $availableSettings[$identifier] )) {
            $siteData = eZSiteData::fetchByName($availableSettings[$identifier]['stored_name']);
        }
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData(array('name' => $availableSettings[$identifier]['stored_name']));
        }

        return $siteData;
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

    private function localFileServerURL()
    {
        return $this->getGlobalSetting('api_local_server')->attribute('value');
    }

    private function localFileServerTransformURI( &$href, $ignoreIndexDir = false )
    {
        if ( preg_match( "#^[a-zA-Z0-9]+:#", $href ) || substr( $href, 0, 2 ) == '//' )
            return false;

        if ( strlen( $href ) == 0 )
            $href = '/';
        else if ( $href[0] == '#' )
        {
            $href = htmlspecialchars( $href );
            return true;
        }
        else if ( $href[0] != '/' )
        {
            $href = '/' . $href;
        }

        $sys = eZSys::instance();
        $dir = !$ignoreIndexDir ? $sys->indexDir() : $sys->wwwDir();
        $serverURL = $this->localFileServerURL();
        $href = $serverURL . $dir . $href;
        if ( !$ignoreIndexDir )
        {
            $href = preg_replace( "#^(//)#", "/", $href );
            $href = preg_replace( "#(^.*)(/+)$#", "\$1", $href );
        }
        $href = str_replace( '&amp;amp;', '&amp;', htmlspecialchars( $href ) );

        if ( $href == "" )
            $href = "/";

        return true;
    }
}
