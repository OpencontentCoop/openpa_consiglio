<?php

class OpenPAConsiglioNotificationTransport
{

    const DEFAULT_TRANSPORT = 'Mail';
    const DIGEST_TRANSPORT  = 'Digest';
    const DIGEST_ITEM_TRANSPORT  = 'DigestItem';
    const WHATSAPP_TRASPORT = 'Whatsapp';

    /**
     * @param bool $transport
     * @param bool $forceNewInstance
     *
     * @return OpenPAConsiglioNotificationTransport
     */
    final public static function instance( $transport = false, $forceNewInstance = false )
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
     * @return OpenPAConsiglioNotificationTransport[]
     */
    final public static function availableTransports()
    {
        return array(
          self::instance( self::DEFAULT_TRANSPORT ),
          self::instance( self::DIGEST_TRANSPORT )
        );
    }

    /**
     * @return null|string
     */
    public function identifier()
    {
        return null;
    }

    /**
     * @param array $itemRow
     *
     * @return OpenPAConsiglioNotificationItem
     */
    public function addItem( array $itemRow )
    {
        return OpenPAConsiglioNotificationItem::create( $itemRow );
    }

    /**
     * @param eZNotificationEvent $event
     * @param string $subscribersRuleString
     * @return string
     */
    public function notificationTemplateUri( $event, $subscribersRuleString )
    {
        $factoryIdentifier = $event->attribute( OCEditorialStuffEventType::FIELD_FACTORY_IDENTIFIER );
        $eventType = $event->attribute( OCEditorialStuffEventType::FIELD_TYPE );
        return "design:consiglio/notification/{$this->identifier()}/{$factoryIdentifier}/{$eventType}/{$subscribersRuleString}.tpl";

    }

    /**
     * @param OpenPAConsiglioNotificationItem $item
     *
     * @return bool
     */
    public function send( OpenPAConsiglioNotificationItem $item )
    {
        return false;
    }

    /**
     * @return bool
     */
    public function sendMassive( $parameters = array() )
    {
        return false;
    }

    /**
     * Controlla se l'oggetto di riferimento ha un attributo di tipo ezmatrix con identificatore 'altre_email'
     * Se esiste popola il risultato con il contenuto
     *
     * @param eZUser $user
     *
     * @return array of email address
     */
    protected function getUserAddresses( eZUser $user )
    {
        $addresses = array(
            $user->attribute( 'email' )
        );
        $object = $user->contentObject();
        if ( $object instanceof eZContentObject )
        {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $object->dataMap();
            if ( isset( $dataMap['altre_email'] ) && $dataMap['altre_email']->hasContent() )
            {
                /** @var eZMatrix $content */
                $content = $dataMap['altre_email']->content();
                if ( $content instanceof eZMatrix )
                {
                    $rows = $content->attribute( 'rows' );
                    foreach ( $rows['sequential'] as $row )
                    {
                        $address = $row['columns'][0];
                        if ( eZMail::validate( $address ) )
                        {
                            $addresses[] = $address;
                        }
                    }
                }
            }
        }
        return $addresses;
    }
}

