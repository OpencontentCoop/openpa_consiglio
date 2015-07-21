<?php

use mikehaertl\wkhtmlto\Pdf;

class OpenPAConsiglioPdf
{
    public static function create( $filename, $content, $templatePartsDirPath = 'pdf/', $forceDownload = false )
    {
        // Todo: L'immagine di sfondo viene inserita tramite css, in caso provare fpdi

        $headerTemplatePath = "design:{$templatePartsDirPath}header.tpl";
        $header = self::createTempFile( $headerTemplatePath );

        $footerTemplatePath = "design:{$templatePartsDirPath}footer.tpl";
        $footer = self::createTempFile( $footerTemplatePath );

        // Initialize the PDF using this library: https://github.com/mikehaertl/phpwkhtmltopdf
        $pdf = new Pdf();

        // specify wkhtmltopdf options; see: http://wkhtmltopdf.org/usage/wkhtmltopdf.txt
        $options = array(
            'page-width' => '216mm',
            'page-height' => '297mm',
            'dpi' => 96,
            'image-quality' => 100,
            //'margin-top' => '20',
            'margin-right' => '0',
            'margin-bottom' => '34',
            'margin-left' => '0',
            'header-spacing' => 25,
            'footer-spacing' => 0,
            'disable-smart-shrinking',
            'no-outline',
            'user-style-sheet' => ltrim( eZURLOperator::eZDesign( eZTemplate::factory(), 'stylesheets/pdf.css', 'ezdesign' ), '/' ),
            'header-html' => $header,
            'footer-html' => $footer

        );

        $pdf->setOptions( $options );

        // uses eZ Template to build the cover and frontpage
        //$tpl = eZTemplate::factory();
        //$tpl->setVariable( 'object', $object );
        //$pdf->addPage( $tpl->fetch( 'design:pdf/cover.tpl' ) );
        //$pdf->addPage( $tpl->fetch( 'design:pdf/frontpage.tpl' ) );

        // Adds a Table of Contents
        //$pdf->addToc(array('user-style-sheet' => 'extension/myextension/design/standard/stylesheets/pdf.css','xsl-style-sheet' => 'extension/myextension/design/standard/stylesheets/toc.xsl'));

        // Fill the body of the PDF
        $pdf->addPage( $content );

        // Adds the backpage
        //$pdf->addPage( $tpl->fetch( 'design:pdf/backpage.tpl' ) );

        // Downloads the PDF


        if ( eZINI::instance()->variable( 'DebugSettings', 'DebugOutput' ) == 'enabled' )
        {
            $pdf->createPdf();
            echo '<pre>';
            print_r($pdf);
            echo '</pre>';
            eZDisplayDebug();
            eZExecution::cleanExit();
        }
        else
        {
            if ( $forceDownload )
            {
                $pdf->send( $filename );
            }
            else
            {
                $pdf->send();
            }
        }
    }

    protected static function createTempFile( $templatePath, $templateVars = array() )
    {
        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ( $templateVars as $name => $value )
        {
            $tpl->setVariable( $name, $value );
        }
        $content = $tpl->fetch( $templatePath );
        $cacheDirectory = eZSys::cacheDirectory();
        $directory =  eZDir::path( array( $cacheDirectory, 'pdf_creator' ) );
        $filename = md5( $content ) . '.html';
        $filePath = $directory . '/' . $filename;
        if ( !file_exists( $filePath ) )
        {
            eZFile::create( $filename, $directory, $content, true );
            $perm = eZINI::instance()->variable( 'FileSettings', 'StorageFilePermissions' );
            chmod( $filePath, octdec( $perm ) );
        }
        return $filePath;
    }


}