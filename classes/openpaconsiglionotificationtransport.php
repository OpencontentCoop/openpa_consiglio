<?php

class OpenPAConsiglioNotificationTransport
{

    const DEFAULT_TRANSPORT = 'Mail';
    const DIGEST_TRANSPORT  = 'Digest';
    const WHATSAPP_TRASPORT = 'Whatsapp';

    /**
     * @param bool $transport
     * @param bool $forceNewInstance
     *
     * @return OpenPAConsiglioNotificationTransport
     */
    public static function instance( $transport = false, $forceNewInstance = false )
    {
        if (!$transport)
        {
            $transport = OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT;
        }

        $className = 'OpenPAConsiglio' . $transport . 'NotificationTransport';

        if (class_exists( $className )) {
            $impl = new $className();
        }

        if ( !isset( $impl ) )
        {
            $impl = new eZNotificationTransport();
            eZDebug::writeError( 'Transport implementation not supported: ' . $transport, __METHOD__ );
        }
        return $impl;
    }

    /**
     * @param OpenPAConsiglioNotificationItem $item
     *
     * @return bool
     */
    public function send( OpenPAConsiglioNotificationItem $item )
    {
        return true;
    }

    /**
     * @return bool
     */
    public function sendMassive( $parameters = array() )
    {
        return true;
    }
}

