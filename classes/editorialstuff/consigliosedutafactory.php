<?php

class ConsiglioSedutaFactory extends OCEditorialStuffPostDefaultFactory implements OCEditorialStuffPostDownloadableFactoryInterface
{
    public function instancePost( $data )
    {
        return new ConsiglioSeduta( $data, $this );
    }

    protected function getPdfContentFromVersion( eZContentObjectVersion $objectVersion, $parameters = array() )
    {
        if ( !$objectVersion instanceof eZContentObjectVersion )
        {
            throw new Exception( "ObjectVersion not found" );
        }

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $objectVersion->dataMap();

        /** @var eZContentObject $seduta */
        $seduta = $dataMap['seduta']->content();

        /** @var eZContentObjectAttribute[] $sedutaDataMap */
        $sedutaDataMap = $seduta->dataMap();

        $odg = json_decode( $dataMap['odg']->content(), true );

        $listOrgano = $sedutaDataMap['organo']->content();
        $organo =  ( isset( $listOrgano['relation_list'][0]['contentobject_id'] ) ) ?
            $organo = eZContentObject::fetch( $listOrgano['relation_list'][0]['contentobject_id'] )->attribute( 'name' ) : '';

        $luogo = isset( $sedutaDataMap['luogo'] ) ? $sedutaDataMap['luogo']->content() : '';

        $dataOra = isset( $dataMap['data_ora'] ) ? $dataMap['data_ora']->toString() : 0;

        $variables = array(
            'line_height' => isset( $parameters['line_height'] ) ? $parameters['line_height'] : 20,
            'data' => $objectVersion->attribute( 'created' ),
            'luogo' => $luogo,
            'organo' => $organo,
            'data_seduta' => $dataOra,
            'odg' => $odg,
            'firmatario' => '',
            'firma' => '',
        );

        if ( isset( $sedutaDataMap['firmatario'] ) && $sedutaDataMap['firmatario']->hasContent() )
        {
            $listFirmatario = $sedutaDataMap['firmatario']->content();
            if ( isset( $listFirmatario['relation_list'][0]['contentobject_id'] ) )
            {
                $firmatario = eZContentObject::fetch(
                    $listFirmatario['relation_list'][0]['contentobject_id']
                );
                /** @var eZContentObjectAttribute[] $firmatarioDataMap */
                $firmatarioDataMap = $firmatario->dataMap();

                $variables['firmatario'] = $firmatario->attribute( 'name' );
                if ( $firmatarioDataMap['firma']->hasContent()
                     && $firmatarioDataMap['firma']->attribute( 'data_type_string' ) == 'ezimage' )
                {
                    $image = $firmatarioDataMap['firma']->content()->attribute( 'original' );
                    $url = $image['url'];
                    eZURI::transformURI( $url, false, 'full' );
                    $variables['firma'] = $url;
                }
            }
        }

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ( $variables as $name => $value )
        {
            $tpl->setVariable( $name, $value );
        }
        return $tpl->fetch( 'design:pdf/seduta/seduta.tpl' );

    }

    public function downloadModuleResult(
        $parameters,
        OCEditorialStuffHandlerInterface $handler,
        eZModule $module,
        $version = false
    )
    {
        $currentPost = $this->getModuleCurrentPost( $parameters, $handler, $module );
        $parameters = array();
        if ( !$version )
        {
            $content = $this->getPdfContentFromVersion(
                $currentPost->getObject()->currentVersion(),
                $parameters
            );
        }
        else
        {
            $content = $this->getPdfContentFromVersion(
                $currentPost->getObject()->version( $version ),
                $parameters
            );
        }

        $fileName = $currentPost->attribute( 'name' );
        /** @var eZContentClass $objectClass */
        $objectClass = $currentPost->getObject()->attribute( 'content_class' );
        $languageCode = eZContentObject::defaultLanguage();
        $fileName = $objectClass->urlAliasName( $currentPost->getObject(), false, $languageCode );
        $fileName = eZURLAliasML::convertToAlias( $fileName );
        $fileName .= '.pdf';
        OpenPAConsiglioPdf::create( $fileName, $content, 'pdf/seduta/' );

        eZExecution::cleanExit();

        return true;
    }
}