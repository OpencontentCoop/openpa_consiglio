<?php

class OpenPAConsiglioPushNotifier
{
    private static $_instance;

    protected $file;

    protected $IsModified;

    protected $data;

    protected function __construct()
    {
        $cacheDirectory = eZSys::cacheDirectory();
        $filename = $cacheDirectory . '/' . 'push_notifications.json';
        $this->file = eZClusterFileHandler::instance( $filename );
        $this->IsModified = false;
    }

    public static function instance()
    {
        if ( self::$_instance === null )
            self::$_instance = new OpenPAConsiglioPushNotifier();
        return self::$_instance;
    }

    public function emit( $identifier, $data )
    {        
        $this->data = array(
            'identifier' => $identifier,
            'data' => $data
        );
        $this->IsModified = true;
        $this->store();
        $this->sendToMobile( $identifier, $data );
    }

    protected function sendToMobile( $identifier, $data )
    {
        $endPoint = OpenPAINI::variable( 'OpenPAConsiglio', 'BackendEndPoint' );
        $url = false;
        $values = array();
        $dateTime = new DateTime();
        $now = $dateTime->format( 'Y-m-d H:i:s' );
        switch( $identifier )
        {
            case 'start_seduta':
            case 'stop_seduta':
                $url = $endPoint . '/api/consiglio/seduta';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now
                );
            break;


            case 'start_punto':
            case 'stop_punto':
                $url = $endPoint . '/api/consiglio/punto';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now
                );
            break;

            case 'start_votazione':
            case 'stop_votazione':
                $url = $endPoint . '/api/consiglio/votazione';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now,
                    'id_point' => $data['punto_id'],
                );
                break;
        }
        
        eZLog::write( var_export( $url, 1 ), 'runtime.log' );        
        if ( $url )
        {
            eZLog::write( var_export( $values, 1 ), 'runtime.log' );
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $values );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $result = curl_exec( $ch );        
            eZLog::write( var_export( $result, 1 ), 'runtime.log' );
            curl_close( $ch );
        }
    }

    function store()
    {
        if ( $this->IsModified )
        {
            $this->file->storeContents( json_encode( $this->data ), 'push_notifications', false, true );
            $this->IsModified = false;
        }
    }


}