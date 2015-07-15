<?php

class OpenPAConsiglioNotificationTransport
{

    const DEFAULT_TRANSPORT = 'Mail';

    public function __construct(){}

    public static function instance( $transport = false, $forceNewInstance = false )
    {
        if (!$transport)
        {
            $transport = OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT;
        }

        $className = 'OpenPAConsiglio' . $transport . 'NotificationTransport';

        if (class_exists( $className )) {

            echo $className;

            $impl = new $className();
        }

        if ( !isset( $impl ) )
        {
            $impl = new eZNotificationTransport();
            eZDebug::writeError( 'Transport implementation not supported: ' . $transport, __METHOD__ );
        }
        return $impl;
    }

    public function send( OpenPAConsiglioNotificationItem $item)
    {
        return true;
    }
}

