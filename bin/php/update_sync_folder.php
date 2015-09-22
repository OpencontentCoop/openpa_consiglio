#!/usr/bin/env php
<?php

// script initializing
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Aggiorna la cartella messa a disposizione per la sincronizzazione con il server locale" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[dir:]', '', array( 'dir'  => "Percorso della cartella (default ../<identificatore_sito>_da _sincronizzare" ) );

$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

try
{
    $identifier = OpenPABase::getCurrentSiteaccessIdentifier();
    $dirPath = "../{$identifier}_da_sincronizzare";

    if ( !empty( $options['dir'] ) )
    {
        $dirPath = $options['dir'];
    }

    if ( !file_exists( $dirPath ) )
    {
        if ( !eZDir::mkdir( $dirPath ) )
        {
            throw new Exception( "Non posso creare la cartella $dirPath" );
        }
    }

    $dirPath = realpath( $dirPath );
    $cli->notice( "Cartella di destinazione: $dirPath" );

    $totalSize = 0;
    function copyBinary( eZBinaryFile $binaryFile )
    {
        global $dirPath;
        global $totalSize;
        $fileHandler = eZClusterFileHandler::instance( $binaryFile->filePath() );
        $totalSize += $fileHandler->size();
        eZFile::create( $binaryFile->attribute( 'filename' ), $dirPath, $fileHandler->fetchContents() );
    }

    function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    $sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems( array( 'limit' => 100 ) );
    foreach( $sedute as $seduta )
    {
        $cli->notice( $seduta->getObject()->attribute( 'name' ) );
        foreach( $seduta->attribute( 'documenti' ) as $allegato )
        {
            /** @var Allegato $allegato */
            copyBinary( $allegato->binaryFile() );
            $cli->notice( " |- [Allegato] " . $allegato->getObject()->attribute( 'name' ) . " " . formatBytes( $allegato->binaryFile()->fileSize() ) );
        }

        foreach( $seduta->attribute( 'odg' ) as $punto )
        {
            /** @var Punto $punto */
            if ( $punto->attribute( 'count_documenti' ) > 0 || $punto->attribute( 'count_osservazioni' ) > 0 )
            {
                $cli->notice( " |- " . $punto->getObject()->attribute( 'name' ) );
                foreach ( $punto->attribute( 'documenti' ) as $allegato )
                {
                    /** @var Allegato $allegato */
                    copyBinary( $allegato->binaryFile() );
                    $cli->notice( "    |- [Allegato] " . $allegato->getObject()->attribute( 'name' ) . " " . formatBytes( $allegato->binaryFile()->fileSize() ) );
                }
                foreach ( $punto->attribute( 'osservazioni' ) as $osservazione )
                {
                    /** @var Osservazione $osservazione */
                    copyBinary( $osservazione->binaryFile() );
                    $cli->notice( "    |- [Osservazione] " . $osservazione->getObject()->attribute( 'name' ) . " " . formatBytes( $osservazione->binaryFile()->fileSize() ) );
                }
            }
        }
    }

    $cli->notice( formatBytes( $totalSize ) );
    $script->shutdown();
}
catch( Exception $e )
{
    $script->shutdown( $e->getCode(), $e->getMessage() );
}

