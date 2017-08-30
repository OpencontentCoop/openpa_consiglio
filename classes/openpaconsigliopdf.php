<?php

class OpenPAConsiglioPdf
{
    public static function create($filename, $content, $parameters, $forceDownload = true)
    {
        return self::exportParadoxPdf($filename, $content, $parameters, $forceDownload);
    }

    protected static function exportParadoxPdf($filename, $content, $parameters, $forceDownload)
    {
        $paradoxPdf = new ParadoxPDF();
        if ($forceDownload) {
            $paradoxPdf->exportPDF(
                $content,
                $filename,
                $parameters['cache']['keys'],
                $parameters['cache']['subtree_expiry'],
                $parameters['cache']['expiry'],
                $parameters['cache']['ignore_content_expiry']
            );

            return true;
        } else {
            return array(
                'content' => $paradoxPdf->generatePDF($content),
                'exporter' => $paradoxPdf
            );
        }
    }

    protected static function createTempFile($templatePath, $templateVars = array())
    {
        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ($templateVars as $name => $value) {
            $tpl->setVariable($name, $value);
        }
        $content = $tpl->fetch($templatePath);
        $cacheDirectory = eZSys::cacheDirectory();
        $directory = eZDir::path(array($cacheDirectory, 'pdf_creator'));
        $filename = md5($content) . '.html';
        $filePath = $directory . '/' . $filename;
        if (!file_exists($filePath)) {
            eZFile::create($filename, $directory, $content, true);
            $perm = eZINI::instance()->variable('FileSettings', 'StorageFilePermissions');
            chmod($filePath, octdec($perm));
        }

        return $filePath;
    }


}
