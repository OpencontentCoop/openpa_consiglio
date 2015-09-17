<?php

class OpenPAConsiglioDigestNotificationTransport extends OpenPAConsiglioNotificationTransport
{

    public function identifier()
    {
        return 'digest';
    }

    public function addItem( array $itemRow )
    {
        $item = self::fetchByObjectAndUser( $itemRow['object_id'], $itemRow['user_id'] );
        if ( $item instanceof OpenPAConsiglioNotificationItem )
        {
            $item->setAttribute( 'created_time', time() );
            $item->setAttribute( 'subject', $itemRow['subject'] );
            $item->setAttribute( 'body', $itemRow['body'] );
            $item->store();
        }
        else
        {
            $item = OpenPAConsiglioNotificationItem::create( $itemRow );
        }
        return $item;
    }

    public static function fetchByObjectAndUser( $objectId, $userId )
    {
        $exists = OpenPAConsiglioNotificationItem::fetchList( null, null,
            array(
                'user_id' => $userId,
                'object_id' => $objectId,
                'type' => OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT
            ),
            array( 'created_time' => 'desc' )
        );
        if ( isset( $exists[0] ) && $exists[0] instanceof OpenPAConsiglioNotificationItem )
        {
            return $exists[0];
        }
        return null;
    }

    /**
     * @param eZNotificationEvent $event
     * @param string $subscribersRuleString
     * @return string
     */
    public function notificationTemplateUri( $event, $subscribersRuleString )
    {
        $factoryIdentifier = $event->attribute( OCEditorialStuffEventType::FIELD_FACTORY_IDENTIFIER );
        return "design:consiglio/notification/{$this->identifier()}/{$factoryIdentifier}/{$subscribersRuleString}.tpl";

    }

    public function sendMassive( $parameters = array() )
    {
        $items = OpenPAConsiglioNotificationItem::fetchItemsToSend(
            OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT
        );

        foreach ( $items as $notification )
        {
            $now = time();
            $notificationItem = OpenPAConsiglioNotificationItem::create(
                array(
                    'object_id' => $notification->attribute( 'object_id' ),
                    'user_id' => $notification->attribute( 'user_id' ),
                    'created_time' => $now,
                    'type' => OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                    'subject' => $notification->attribute( 'subject' ),
                    'body' => $notification->attribute( 'body' ),
                    'expected_send_time' => $now
                )
            );

            if ( $notificationItem->send() )
            {
                $items = self::fetchDigestItems( $notification );
                foreach( $items as $item )
                {
                    $item->remove();
                }
                $notification->remove();
            }
        }
    }

    protected static function fetchDigestItems( OpenPAConsiglioNotificationItem $digest )
    {
        return OpenPAConsiglioNotificationItem::fetchList( null, null,
            array(
                'user_id' => $digest->attribute( 'user_id' ),
                'object_id' => $digest->attribute( 'object_id' ),
                'type' => OpenPAConsiglioNotificationTransport::DIGEST_ITEM_TRANSPORT
            ),
            array( 'created_time' => 'asc' )
        );
    }

    public static function composeBody( OpenPAConsiglioNotificationItem $digest )
    {
        $items = self::fetchDigestItems( $digest );
        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        $tpl->setVariable( 'items', $items );
        $itemsData = $tpl->fetch( "design:consiglio/notification/digest/items.tpl" );
        $body = str_replace( '<!--ITEMS DATA-->', $itemsData, $digest->attribute( 'body' ) );
        $digest->setAttribute( 'body', $body );
        $digest->store();
    }

}
