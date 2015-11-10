<?php

$cli = eZCLI::instance();
$cli->setUseStyles( true );
$cli->setIsQuiet( $isQuiet );

foreach( OpenPAConsiglioNotificationTransport::availableTransports() as $transport )
{
    $transport->sendMassive();
}

SedutaFactory::reindex();