<?php

class OpenPAConsiglioDigestNotificationTransport extends OpenPAConsiglioNotificationTransport
{
    /*!
     Constructor
    */
    function OpenPAConsiglioDigestNotificationTransport() {}

    function send( OpenPAConsiglioNotificationItem $item, $parameters )
    {
        return false;
    }

    public static function sendMassive()
    {
        $digest = array();
        $items = OpenPAConsiglioNotificationItem::fetchItemsToSend( OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT);

        /** @var OpenPAConsiglioNotificationItem $i */
        foreach ($items as $i)
        {
            $digest [$i->__get('user_id')] ['notifications'][]= $i;
            $digest [$i->__get('user_id')] ['content'][]= $i->__get('body');
        }

        foreach ($digest as $key => $value) {
            /** @var eZUser $user */
            $user = eZUser::fetch($key);
            if  ( $user instanceof eZUser && $user->attribute( 'is_enabled' ) )
            {
                $ini = eZINI::instance();
                $mail = new eZMail();

                $receiver = $user->attribute( 'email' );

                if ( $mail->validate( $receiver ) )
                {
                    $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
                    if ( !$emailSender )
                        $emailSender = $ini->variable( "MailSettings", "AdminEmail" );

                    $senderName = $ini->variable( 'SiteSettings', 'SiteName' );
                    $mail->setSender( $emailSender, $senderName );
                    $mail->setReceiver( $receiver );

                    $mail->setSubject( 'Notifiche' . date('d-m-Y') );

                    $tpl = eZTemplate::factory();
                    $tpl->resetVariables();
                    $tpl->setVariable( 'content', implode('<hr>', $value['content']) );

                    $content = $tpl->fetch( 'design:notification/mail_pagelayout.tpl');
                    $mail->setBody( $content );

                    if ( isset( $parameters['message_id'] ) )
                        $mail->addExtraHeader( 'Message-ID', $parameters['message_id'] );
                    if ( isset( $parameters['references'] ) )
                        $mail->addExtraHeader( 'References', $parameters['references'] );
                    if ( isset( $parameters['reply_to'] ) )
                        $mail->addExtraHeader( 'In-Reply-To', $parameters['reply_to'] );
                    if ( isset( $parameters['from'] ) )
                        $mail->setSenderText( $parameters['from'] );
                    if ( isset( $parameters['content_type'] ) )
                        $mail->setContentType( $parameters['content_type'] );

                    $mailResult = eZMailTransport::send( $mail );
                    if ($mailResult)
                    {
                        /** @var OpenPAConsiglioNotificationItem $v */
                        foreach ($value['notifications'] as $v) {
                            $v->setSent();
                        }
                    }
                }
            }
        }
    }

    function prepareAddressString( $addressList, $mail )
    {
        if ( is_array( $addressList ) )
        {
            $validatedAddressList = array();
            foreach ( $addressList as $address )
            {
                if ( $mail->validate( $address ) )
                {
                    $validatedAddressList[] = $address;
                }
            }
//             $addressString = '';
//             if ( count( $validatedAddressList ) > 0 )
//             {
//                 $addressString = implode( ',', $validatedAddressList );
//                 return $addressString;
//             }
            return $validatedAddressList;
        }
        else if ( strlen( $addressList ) > 0 )
        {
            if ( $mail->validate( $addressList ) )
            {
                return $addressList;
            }
        }
        return false;
    }

}
