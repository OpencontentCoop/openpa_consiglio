<?php


class OpenPAConsiglioDefaultFactory extends OCEditorialStuffPostDefaultFactory
{
    public function __construct( $configuration )
    {
        parent::__construct( $configuration );
        $this->configuration['PersistentVariable'] = array(
            'top_menu' => true,
            'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
        );
    }

    public function instancePost( $data )
    {
        return new OpenPAConsiglioDefaultPost( $data, $this );
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/consiglio_default';
    }

    public static function availableGlobalSettings()
    {
        return array(
            'api_local_server' => array(
                'stored_name' => 'OpenPAConsiglio.APILocalServerURL',
                'name' => 'URL server locale per servire i file da api',
                'help_text' => "Specifica un indirizzo http valido (ad esempio 'http://local.cal/cached_files'). Se lasci il campo vuoto il file saranno serviti dal server web pubblico"
            )
        );
    }

    public static function listGlobalSettings()
    {
        $data = array();
        $availableSettings = self::availableGlobalSettings();
        foreach( array_keys( $availableSettings ) as $identifier )
        {
            $data[$identifier] = $availableSettings[$identifier];
            $data[$identifier]['value'] = self::getGlobalSetting( $identifier );
        }
        return $data;
    }

    public static function storeGlobalSetting( $identifier, $value )
    {
        $availableSettings = self::availableGlobalSettings();
        if ( isset( $availableSettings[$identifier] ) )
        {
            $siteData = self::getGlobalSetting( $identifier );
            $siteData->setAttribute( 'value', $value );
            $siteData->store();
        }
    }

    /**
     * @param $identifier
     * @return eZSiteData
     */
    public static function getGlobalSetting( $identifier )
    {
        $availableSettings = self::availableGlobalSettings();
        $siteData = null;
        if ( isset( $availableSettings[$identifier] ) )
        {
            $siteData = eZSiteData::fetchByName( $availableSettings[$identifier]['stored_name'] );
        }
        if ( !$siteData instanceof eZSiteData )
        {
            $siteData = new eZSiteData( array( 'name' => $availableSettings[$identifier]['stored_name'] ) );
        }
        return $siteData;
    }

    public static function localFileServerIsEnabled()
    {
        return mb_strlen( self::getGlobalSetting( 'api_local_server' )->attribute( 'value' ) ) > 0;
    }

    public static function localFileServerDownloadUrl( eZBinaryFile $binaryFile )
    {
        if ( $binaryFile instanceof eZBinaryFile )
        {
            $url = $binaryFile->attribute( 'filename' );
            self::localFileServerTransformURI( $url );
            return $url;
        }
        return null;
    }

    public static function localFileServerURL()
    {
        return self::getGlobalSetting( 'api_local_server' )->attribute( 'value' );
    }

    public static function localFileServerTransformURI( &$href, $ignoreIndexDir = false )
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
        $serverURL = self::localFileServerURL();
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

    protected function dashboardModuleResultTemplate( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $parameters['heuristic'] = true;
        return parent::dashboardModuleResultTemplate( $parameters, $handler, $module );
    }
}