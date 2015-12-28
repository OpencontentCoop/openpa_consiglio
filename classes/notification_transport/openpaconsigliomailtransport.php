<?php

class OpenPAConsiglioMailTransport
{
    /*!
     Constructor
    */
    function OpenPAConsiglioMailTransport()
    {
    }

    function sendMail( eZMail $mail, $debugReceivers )
    {
        $ini = eZINI::instance();
        $sendmailOptions = '';
        $emailFrom = $mail->sender();
        $emailSender = isset( $emailFrom['email'] ) ? $emailFrom['email'] : false;
        if ( !$emailSender || count( $emailSender) <= 0 )
            $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
        if ( !$emailSender )
            $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );
        if ( !eZMail::validate( $emailSender ) )
            $emailSender = false;

        $isSafeMode = ini_get( 'safe_mode' ) != 0;

        $sendmailOptionsArray = $ini->variable( 'MailSettings', 'SendmailOptions' );
        if( is_array($sendmailOptionsArray) )
            $sendmailOptions = implode( ' ', $sendmailOptionsArray );
        elseif( !is_string($sendmailOptionsArray) )
            $sendmailOptions = $sendmailOptionsArray;
        if ( !$isSafeMode and
             $emailSender )
            $sendmailOptions .= ' -f'. $emailSender;

        if ( $isSafeMode and
             $emailSender and
             $mail->sender() == false )
            $mail->setSenderText( $emailSender );

        if( function_exists( 'mail' ) )
        {
            $message = $mail->body();
            $excludeHeaders = array( 'Subject' );
            $receiverEmailText = $debugReceivers;
            $excludeHeaders[] = 'To';
            $excludeHeaders[] = 'Cc';
            $excludeHeaders[] = 'Bcc';

            $extraHeaders = $mail->headerText( array( 'exclude-headers' => $excludeHeaders ) );

            $returnedValue = mail( $receiverEmailText, $mail->subject(), $message, $extraHeaders, $sendmailOptions );
            if ( $returnedValue === false )
            {
                eZDebug::writeError( 'An error occurred while sending e-mail. Check the Sendmail error message for further information (usually in /var/log/messages)',
                    __METHOD__ );
            }

            return $returnedValue;
        }
        else
        {
            eZDebug::writeWarning( "Unable to send mail: 'mail' function is not compiled into PHP.", __METHOD__ );
        }

        return false;
    }

    /*!
     \static
     Sends the contents of the email object \a $mail using the default transport.
    */
    static function send( eZMail $mail )
    {
        if ( OpenPAINI::variable( 'OpenPAConsiglio', 'UseMailDebug', true ) == 'true' )
        {
            $debugTransport = new self();
            return $debugTransport->sendMail( $mail,  OpenPAINI::variable( 'OpenPAConsiglio', 'UseMailDebugAddress', 'lr@opencontent.it' ) );
        }
        else
        {
            return eZMailTransport::send( $mail );
        }
    }
}

?>
