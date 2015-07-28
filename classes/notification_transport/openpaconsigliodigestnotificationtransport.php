<?php

class OpenPAConsiglioDigestNotificationTransport extends OpenPAConsiglioNotificationTransport
{

    public function sendMassive( $parameters = array() )
    {
        $digest = array();
        $items = OpenPAConsiglioNotificationItem::fetchItemsToSend(
            OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT
        );

        foreach ( $items as $i )
        {
            $userId = $i->attribute( 'user_id' );
            $objectId = $i->attribute( 'object_id' );

            if ( !isset( $digest[$userId] ) )
            {
                $digest[$userId] = array();
            }
            if ( !isset( $digest[$userId][$objectId] ) )
            {
                $digest[$userId][$objectId] = array();
            }

            $digest[$userId][$objectId][] = $i;
        }

        foreach ( $digest as $userId => $postNotifications )
        {
            foreach( $postNotifications as $objectId => $notifications )
            {
                /** @var OpenPAConsiglioNotificationItem[] $notifications */

                $subject = 'Aggiornamenti di ' . date( 'd-m-Y' );
                $body = '';
                foreach ( $notifications as $notification )
                {
                    $body .= "<p><strong>{$notification->attribute( 'subject' )}</strong></p>{$notification->attribute( 'body' )}<hr />";
                }

                $now = time();
                $notificationItem = OpenPAConsiglioNotificationItem::create(
                    array(
                        'object_id' => $objectId,
                        'user_id' => $userId,
                        'created_time' => $now,
                        'type' => OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                        'subject' => $subject,
                        'body' => $body,
                        'expected_send_time' => $now
                    )
                );

                if ( $notificationItem->send() )
                {
                    foreach ( $notifications as $notification )
                    {
                        $notification->setSent();
                    }
                }
            }
        }
    }
}
