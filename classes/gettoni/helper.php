<?php

class OpenPAConsiglioGettoniHelper
{
    /**
     * @var Politico
     */
    protected $politico;

    /**
     * @var Politico[]
     */
    protected $politici;

    /**
     * @var Seduta[]
     */
    protected $sedute;

    public function __construct()
    {
        if ( !eZContentObject::fetchByRemoteID( 'openpa_consiglio_rendiconto_spese' ) )
        {
            $params =  array(
                'remote_id' => 'openpa_consiglio_rendiconto_spese',
                'class_identifier' => 'folder',
                'parent_node_id' => eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ),
                'attributes' => array(
                    'name' => 'Rendicontazione spese'
                )
            );
            eZContentFunctions::createAndPublishObject( $params );
        }
    }

    public function setPolitico( Politico $politico )
    {
        $this->politico = $politico;
    }

    /**
     * @return Politico[]
     */
    public function getPolitici()
    {
        if ( $this->politici === null )
        {
            $this->politici = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
                array(
                    'limit' => 100,
                    'offset' => 0,
                    'sort' => array( 'politico/cognome' => 'asc' )
                )
            );
        }

        return $this->politici;
    }

    /**
     * @param OpenPAConsiglioGettoniInterval $interval
     *
     * @return Seduta[]
     */
    public function getSedute( OpenPAConsiglioGettoniInterval $interval )
    {
        if ( $this->sedute === null )
        {
            $this->sedute = array();

            $startDate = ezfSolrDocumentFieldBase::preProcessValue(
                $interval->startDateTime->getTimestamp(),
                'date'
            );
            $endDate = ezfSolrDocumentFieldBase::preProcessValue(
                $interval->endDateTime->getTimestamp(),
                'date'
            );
            $filters = array( 'meta_published_dt:[' . $startDate . ' TO ' . $endDate . ']' );

            if ( $this->politico instanceof Politico )
            {
                $organoNodeIds = array();
                $currentLocations = $this->politico->attribute( 'is_in' );
                foreach ( $this->politico->attribute( 'locations' ) as $identifier => $node )
                {
                    /** @var eZContentObjectTreeNode $node */
                    if ( $currentLocations[$identifier] )
                    {
                        $organoNodeIds[] = $node->attribute( 'node_id' );
                    }
                }
                if ( !empty( $organoNodeIds ) )
                {
                    $organoFilters = count( $organoNodeIds ) > 1 ? array( 'or' ) : array();
                    foreach ( $organoNodeIds as $nodeId )
                    {
                        $organoFilters[] = 'submeta_organo___main_node_id_si:' . $nodeId;
                    }
                    $filters[] = count( $organoFilters ) > 1 ? $organoFilters : $organoFilters[0];
                }
            }
            $this->sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems(
                array(
                    'filters' => $filters,
                    'state' => array( 'closed' ),
                    'sort' => array( 'meta_published_dt' => 'asc' ),
                    'limit' => 1000,
                    'offset' => 0
                )
            );
        }

        return $this->sedute;
    }

    public function getGettoni()
    {
        $data = array();
        if ( $this->politico instanceof Politico )
        {
            $gettoni = OpenPAConsiglioGettone::fetchByUserID( $this->politico->id() );
            foreach ( $gettoni as $gettone )
            {
                $data[$gettone->attribute( 'seduta_id' )] = $gettone;
            }
        }

        return $data;
    }

    public static function executeAction( $actionName, $actionParameter, eZUser $currentSelectedUser, Politico $politico, OpenPAConsiglioGettoniInterval $interval )
    {
        switch ( $actionName )
        {
            case 'add_km':
                break;

            case 'add_spesa':
                self::addSpesa( $currentSelectedUser );
                break;

            case 'remove_spesa':
                self::removeSpesa( $currentSelectedUser, $actionParameter );
                break;

            case 'load_spese':
                self::loadSpese( $currentSelectedUser, $actionParameter, $politico->id() );
                break;

            case 'add_iban':
                self::addIban( $currentSelectedUser );
                break;

            case 'add_trattenute':
                self::addTrattenute( $currentSelectedUser );
                break;
        }
    }

    protected static function loadSpese( eZUser $currentSelectedUser, $sedutaId, $politicoId )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'seduta', intval( $sedutaId ) );
        $tpl->setVariable( 'politico', intval( $politicoId ) );
        header( 'HTTP/1.1 200 OK' );
        echo $tpl->fetch( "design:consiglio/gettoni/spese.tpl" );
        eZExecution::cleanExit();
    }

    protected static function removeSpesa( eZUser $currentSelectedUser, $spesaId  )
    {
        header('Content-Type: application/json');
        header( 'HTTP/1.1 200 OK' );
        echo json_encode( array( 'result' => 'success' ));
        eZExecution::cleanExit();
    }

    protected static function addSpesa( eZUser $currentSelectedUser )
    {
        $http = eZHTTPTool::instance();

        $siteaccess = eZSiteAccess::current();
        $options['upload_dir'] = eZSys::cacheDirectory() . '/fileupload/';
        $options['download_via_php'] = true;
        $options['param_name'] = "File";
        $options['image_versions'] = array();
        $options['max_file_size'] = $http->variable( "upload_max_file_size", null );
        $filePath = null;
        if ( $http->hasPostVariable( 'image' ) )
        {
            $im = imagecreatefrompng( $_POST['image'] );
            $tmpFileName = md5( uniqid( 'camupload' ) ) . '.png';
            $filePath = $options['upload_dir'] . $tmpFileName;
            imagepng( $im, $filePath );
        }
        else
        {
            /** @var UploadHandler $uploadHandler */
            $uploadHandler = new UploadHandler( $options, false );
            $data = $uploadHandler->post( false );
            foreach( $data[$options['param_name']] as $file )
            {
                $filePath = $options['upload_dir'] . $file->name;
                break;
            }
        }
        if ( $filePath )
        {
            $params = array(
                'creator_id' => $currentSelectedUser->id(),
                'class_identifier' => 'rendiconto_spese',
                'parent_node_id' => eZContentObject::fetchByRemoteID(
                    'openpa_consiglio_rendiconto_spese'
                )->attribute( 'main_node_id' ),
                'attributes' => array(
                    'description' => $http->postVariable( 'Description' ),
                    'amount' => $http->postVariable( 'Amount' ),
                    'file' => $filePath,
                    'relations' => $http->postVariable( 'seduta' )
                )
            );
            eZContentFunctions::createAndPublishObject( $params );
            $file = eZClusterFileHandler::instance( $filePath );
            if ( $file->exists() )
            {
                $file->delete();
            }
        }
        header( 'Content-Type: application/json' );
        header( 'HTTP/1.1 200 OK' );
        echo json_encode( array( 'result' => 'success' ) );
        eZExecution::cleanExit();
    }

    protected static function addTrattenute( eZUser $currentSelectedUser )
    {
        $http = eZHTTPTool::instance();
        $name = $http->postVariable( 'name' );
        $value = $http->postVariable( 'value' );
        if ( $name == 'trattenute'
             && intval( $http->postVariable( 'pk', 0 ) ) == $currentSelectedUser->id()
             && is_numeric( $value ) )
        {
            eZPreferences::setValue(
                'consiglio_gettoni_trattenute',
                $value,
                $currentSelectedUser->id()
            );
            header( 'HTTP/1.1 200 OK' );
            echo 'Valore salvato';
            eZExecution::cleanExit();
        }
        else
        {
            header( 'HTTP/1.1 500 Internal Server Error' );
            echo 'Valore errato: inserire un numero';
            eZExecution::cleanExit();
        }
    }

    protected static function addIban( eZUser $currentSelectedUser )
    {
        $http = eZHTTPTool::instance();
        $name = $http->postVariable( 'name' );
        $value = $http->postVariable( 'value' );
        if ( $name == 'iban'
             && intval( $http->postVariable( 'pk', 0 ) ) == $currentSelectedUser->id()
             && OpenPAConsiglioGettoniHelper::validateIban( $value ) )
        {
            eZPreferences::setValue(
                'consiglio_gettoni_iban',
                $value,
                $currentSelectedUser->id()
            );
            header( 'HTTP/1.1 200 OK' );
            echo 'Valore salvato';
            eZExecution::cleanExit();
        }
        else
        {
            header( 'HTTP/1.1 500 Internal Server Error' );
            echo 'Codice IBAN non valido';
            eZExecution::cleanExit();
        }
    }

    protected static function validateIban( $iban )
    {
        $iban = strtolower( str_replace( ' ', '', $iban ) );
        $Countries = array(
            'al' => 28,
            'ad' => 24,
            'at' => 20,
            'az' => 28,
            'bh' => 22,
            'be' => 16,
            'ba' => 20,
            'br' => 29,
            'bg' => 22,
            'cr' => 21,
            'hr' => 21,
            'cy' => 28,
            'cz' => 24,
            'dk' => 18,
            'do' => 28,
            'ee' => 20,
            'fo' => 18,
            'fi' => 18,
            'fr' => 27,
            'ge' => 22,
            'de' => 22,
            'gi' => 23,
            'gr' => 27,
            'gl' => 18,
            'gt' => 28,
            'hu' => 28,
            'is' => 26,
            'ie' => 22,
            'il' => 23,
            'it' => 27,
            'jo' => 30,
            'kz' => 20,
            'kw' => 30,
            'lv' => 21,
            'lb' => 28,
            'li' => 21,
            'lt' => 20,
            'lu' => 20,
            'mk' => 19,
            'mt' => 31,
            'mr' => 27,
            'mu' => 30,
            'mc' => 27,
            'md' => 24,
            'me' => 22,
            'nl' => 18,
            'no' => 15,
            'pk' => 24,
            'ps' => 29,
            'pl' => 28,
            'pt' => 25,
            'qa' => 29,
            'ro' => 24,
            'sm' => 27,
            'sa' => 24,
            'rs' => 22,
            'sk' => 24,
            'si' => 19,
            'es' => 24,
            'se' => 24,
            'ch' => 21,
            'tn' => 24,
            'tr' => 26,
            'ae' => 23,
            'gb' => 22,
            'vg' => 24
        );
        $Chars = array(
            'a' => 10,
            'b' => 11,
            'c' => 12,
            'd' => 13,
            'e' => 14,
            'f' => 15,
            'g' => 16,
            'h' => 17,
            'i' => 18,
            'j' => 19,
            'k' => 20,
            'l' => 21,
            'm' => 22,
            'n' => 23,
            'o' => 24,
            'p' => 25,
            'q' => 26,
            'r' => 27,
            's' => 28,
            't' => 29,
            'u' => 30,
            'v' => 31,
            'w' => 32,
            'x' => 33,
            'y' => 34,
            'z' => 35
        );

        if ( strlen( $iban ) == $Countries[substr( $iban, 0, 2 )] )
        {

            $MovedChar = substr( $iban, 4 ) . substr( $iban, 0, 4 );
            $MovedCharArray = str_split( $MovedChar );
            $NewString = "";

            foreach ( $MovedCharArray AS $key => $value )
            {
                if ( !is_numeric( $MovedCharArray[$key] ) )
                {
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if ( bcmod( $NewString, '97' ) == 1 )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

    }
}