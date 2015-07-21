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