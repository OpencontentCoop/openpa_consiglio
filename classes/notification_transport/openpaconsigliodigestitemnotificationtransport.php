<?php

class OpenPAConsiglioDigestItemNotificationTransport extends OpenPAConsiglioNotificationTransport
{

    public function identifier()
    {
        return 'digest_item';
    }

    public function sendMassive( $parameters = array() )
    {
        return false;
    }

    public function send( OpenPAConsiglioNotificationItem $item )
    {
        return false;
    }

    public function addItem( array $itemRow )
    {
        $item = self::fetchByObjectUserAndSubject( $itemRow['object_id'], $itemRow['user_id'], $itemRow['subject'] );
        if ( $item instanceof OpenPAConsiglioNotificationItem )
        {
            $item->setAttribute( 'created_time', time() );
            $item->setAttribute( 'subject', $itemRow['subject'] );
            $item->setAttribute( 'body', $itemRow['body'] );
            $item->store();
            eZDebug::writeWarning( "Item {$item->attribute( 'id' )} updated", __METHOD__ );
        }
        else
        {
            $item = OpenPAConsiglioNotificationItem::create( $itemRow );
        }

        $digest = OpenPAConsiglioDigestNotificationTransport::fetchByObjectAndUser( $itemRow['object_id'], $itemRow['user_id'] );
        if ( $digest instanceof OpenPAConsiglioNotificationItem )
        {
            OpenPAConsiglioDigestNotificationTransport::composeBody( $digest );
        }
        return $item;
    }

    public static function fetchByObjectUserAndSubject( $objectId, $userId, $subject )
    {
        $exists = OpenPAConsiglioNotificationItem::fetchList( null, null,
            array(
                'user_id' => $userId,
                'object_id' => $objectId,
                'subject' => $subject,
                'type' => OpenPAConsiglioNotificationTransport::DIGEST_ITEM_TRANSPORT
            ),
            array( 'created_time' => 'desc' )
        );
        if ( isset( $exists[0] ) && $exists[0] instanceof OpenPAConsiglioNotificationItem )
        {
            return $exists[0];
        }
        return null;
    }

}
