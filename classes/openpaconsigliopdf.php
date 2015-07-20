<?php

use mikehaertl\wkhtmlto\Pdf;

class OpenPAConsiglioPdf
{
    public static function create($filename, $content, $forceDownload = false, $factory = false)
    {

        // Todo: L'immagine di sfono viene inserita tramite css, in caso provare fpdi

        $header = $factory ? 'extension/openpa_consiglio/design/standard/templates/pdf/' . $factory . '/header.html' : 'extension/openpa_consiglio/design/standard/templates/pdf/header.html';
        $footer = $factory ? 'extension/openpa_consiglio/design/standard/templates/pdf/' . $factory . '/footer.html' : 'extension/openpa_consiglio/design/standard/templates/pdf/footer.html';

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
            'footer-spacing' => 25,
            'disable-smart-shrinking',
            'no-outline',
            'user-style-sheet' => 'extension/openpa_consiglio/design/standard/stylesheets/pdf.css',
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
        if ($forceDownload)
        {
            $pdf->send( $filename . '.pdf' );
        }
        else
        {
            $pdf->send();
        }

    }
}