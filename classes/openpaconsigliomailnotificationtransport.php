<?php

class OpenPAConsiglioMailNotificationTransport extends OpenPAConsiglioNotificationTransport
{
    public function send( OpenPAConsiglioNotificationItem $item, $parameters = array() )
    {
        $user = $item->getUser();
        if ( $user instanceof eZUser && $user->attribute( 'is_enabled' ) )
        {

            $ini = eZINI::instance();
            $mail = new eZMail();

            $receiver = $user->attribute( 'email' );

            if ( !$mail->validate( $receiver ) )
            {
                eZDebug::writeError( 'Error with receiver', __METHOD__ );

                return false;
            }

            $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
            if ( !$emailSender )
            {
                $emailSender = $ini->variable( "MailSettings", "AdminEmail" );
            }

            $senderName = $ini->variable( 'SiteSettings', 'SiteName' );
            $mail->setSender( $emailSender, $senderName );
            $mail->setReceiver( $receiver );

            $mail->setSubject( $item->__get( 'subject' ) );


            $tpl = eZTemplate::factory();
            $tpl->resetVariables();

            $tpl->setVariable( 'content', $item->__get( 'body' ) );

            $content = $tpl->fetch( 'design:notification/mail_pagelayout.tpl' );
            $mail->setBody( $content );

            if ( isset( $parameters['message_id'] ) )
            {
                $mail->addExtraHeader( 'Message-ID', $parameters['message_id'] );
            }
            if ( isset( $parameters['references'] ) )
            {
                $mail->addExtraHeader( 'References', $parameters['references'] );
            }
            if ( isset( $parameters['reply_to'] ) )
            {
                $mail->addExtraHeader( 'In-Reply-To', $parameters['reply_to'] );
            }
            if ( isset( $parameters['from'] ) )
            {
                $mail->setSenderText( $parameters['from'] );
            }
            if ( isset( $parameters['content_type'] ) )
            {
                $mail->setContentType( $parameters['content_type'] );
            }

            $mailResult = eZMailTransport::send( $mail );

            return $mailResult;
        }
        return false;
    }

    public function sendMassive( $parameters = array() )
    {
        $items = OpenPAConsiglioNotificationItem::fetchItemsToSend(
            OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT
        );

        foreach ( $items as $i )
        {
            if ( $this->send( $i, $parameters ) )
            {
                $i->setSent();
            }
        }
    }

    protected function prepareAddressString( $addressList, eZMail $mail )
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
