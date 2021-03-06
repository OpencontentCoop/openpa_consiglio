<?php

class OpenPAConsiglioPushNotifier
{
    private static $_instance;

    protected $file;

    protected $backendEndPoint;

    protected $socketIo;

    protected function __construct()
    {
        $cacheDirectory = eZSys::cacheDirectory();
        $filename = $cacheDirectory . '/' . 'push_notifications.json';
        $this->file = eZClusterFileHandler::instance( $filename );

        $this->backendEndPoint = OpenPAConsiglioSettings::instance()->getBackendEndPoint();

        $socketInfo = OpenPAConsiglioSettings::instance()->getSocketInfo();
        $host = $socketInfo['url'];
        $port = $socketInfo['port'];
        $this->socketIo = new SocketIO($host, $port);
    }

    public static function instance()
    {
        if ( self::$_instance === null )
            self::$_instance = new OpenPAConsiglioPushNotifier();
        return self::$_instance;
    }

    public function emit( $identifier, $data )
    {
        //$this->storeToFile( $identifier, $data );
        $this->sendToSocket( $identifier, $data );
        if ($this->backendEndPoint) {
            $this->sendToBackend($identifier, $data);
        }
    }

    protected function storeToFile( $identifier, $data )
    {
        $data = array(
            'identifier' => $identifier,
            'data' => $data
        );
        $this->file->storeContents( json_encode( $data ), 'push_notifications', false, true );
        if ( $data['identifier'] !== 'null' )
            eZLog::write( $data['identifier'] . ' ' . var_export( $data['data'], 1 ), 'openpa_consiglio_push_emit.log', eZSys::varDirectory() . '/log' );
    }

    protected function sendToSocket( $identifier, $data )
    {
        eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
        $data = array(
            'sa' => OpenPAConsiglioSettings::instance()->getIdentifier(),
            'identifier' => $identifier,
            'data' => $data
        );

        try{
            $result = $this->socketIo->emit('broadcast', $data);
            if (!$result){
                throw new Exception("Error socket emit", 1);
            }
        }catch (Exception $e){
            eZLog::write( $e->getMessage(), 'openpa_consiglio_push_emit.log', eZSys::varDirectory() . '/log');
        }
    }

    protected function sendToBackend( $identifier, $data )
    {
        $url = false;
        $values = array();
        $dateTime = new DateTime();
        $now = $dateTime->format( 'Y-m-d H:i:s' );
        switch( $identifier )
        {
            case 'start_seduta':
            case 'stop_seduta':
                $url = $this->backendEndPoint . '/api/consiglio/seduta';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now,
                    'timestamp' => $data['timestamp']
                );
            break;


            case 'start_punto':
            case 'stop_punto':
                $url = $this->backendEndPoint . '/api/consiglio/punto';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now,
                    'timestamp' => $data['timestamp']
                );
            break;

            case 'start_votazione':
            case 'stop_votazione':
                $url = $this->backendEndPoint . '/api/consiglio/votazione';
                $values = array(
                    'id' => $data['id'],
                    'type' => $data['stato'],
                    'date' => $now,
                    'id_point' => $data['punto_id'],
                    'timestamp' => $data['timestamp']
                );
                break;
        }

        if ( $url )
        {
            eZLog::write( var_export( $values, 1 ), 'openpa_consiglio_push_send_to_backend.log', eZSys::varDirectory() . '/log'  );
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $values );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $result = curl_exec( $ch );        
            eZLog::write( var_export( $result, 1 ), 'openpa_consiglio_push_send_to_backend.log', eZSys::varDirectory() . '/log'  );
            curl_close( $ch );
        }
    }

}
