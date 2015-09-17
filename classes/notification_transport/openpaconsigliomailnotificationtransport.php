<?php

class OpenPAConsiglioMailNotificationTransport extends OpenPAConsiglioNotificationTransport
{
    public function identifier()
    {
        return 'mail';
    }

    /**
     * @param array $itemRow
     *
     * @return OpenPAConsiglioNotificationItem
     */
    public function addItem( array $itemRow )
    {
        $item = OpenPAConsiglioNotificationItem::create( $itemRow );
        if ( $item instanceof OpenPAConsiglioNotificationItem )
        {
            $item->send();
        }
        return $item;
    }

    public function send( OpenPAConsiglioNotificationItem $item, $parameters = array() )
    {
        $user = $item->getUser();
        if ( $item->canSend() && ( $user instanceof eZUser && $user->attribute( 'is_enabled' ) ) )
        {
            $ini = eZINI::instance();
            $mail = new eZMail();

            $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
            if ( !$emailSender )
            {
                $emailSender = $ini->variable( "MailSettings", "AdminEmail" );
            }

            $senderName = $ini->variable( 'SiteSettings', 'SiteName' );
            $mail->setSender( $emailSender, $senderName );
            foreach( $this->getUserAddresses( $user ) as $index => $address )
            {
                if ( $index == 0 )
                {
                    $mail->setReceiver( $address );
                }
                else
                {
                    $mail->addCc( $address );
                }
            }

            $mail->setSubject( $item->attribute( 'subject' ) );


            $tpl = eZTemplate::factory();
            $tpl->resetVariables();

            $tpl->setVariable( 'content', $item->attribute( 'body' ) );

            $content = $tpl->fetch( 'design:consiglio/notification/mail_pagelayout.tpl' );
            $mail->setBody( $content );

            if ( isset( $parameters['message_id'] ) )
            {
                $mail->addExtraHeader( 'Message-ID', $parameters['message_id'] );
            }
            //@ MessagedID automatico
            if ( isset( $parameters['references'] ) )
            {
                $mail->addExtraHeader( 'References', $parameters['references'] );
            }
            if ( isset( $parameters['reply_to'] ) )
            {
                $mail->addExtraHeader( 'In-Reply-To', $parameters['reply_to'] );
            }
            //@ MessagedID automatico
            if ( isset( $parameters['from'] ) )
            {
                $mail->setSenderText( $parameters['from'] );
            }

            $mail->setContentType( 'text/html' );
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

}
