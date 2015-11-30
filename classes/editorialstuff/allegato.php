<?php

class Allegato extends OCEditorialStuffPost
{

    const DATE_FORMAT = 'Y-m-d H:i:s';

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'riferimento';
        $attributes[] = 'sostituito';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'riferimento' )
            return $this->getFirstReverseRelatedPost();

        if ( $property == 'sostituito' )
            return $this->isSostituito();

        return parent::attribute( $property );
    }

    public function isSostituito()
    {
        if ( isset( $this->dataMap['sostituito'] ) )
        {
            return (bool) $this->dataMap['sostituito']->attribute( 'data_int' );
        }
        return false;
    }

    public function onUpdate()
    {
        $post = $this->getFirstReverseRelatedPost();
        if ( $post instanceof Punto )
        {
            $lastVersion = $this->getObject()->attribute( 'current_version' ) - 1;
            $diff = $this->diff( $lastVersion );
            if ( isset( $diff['sostituito'] ) )
            {
                $post->createNotificationEvent( 'change_allegati', $this );
            }
        }
    }

    protected function getFirstReverseRelatedPost()
    {
        $firstPost = null;
        $reverseObjects = $this->getObject()->reverseRelatedObjectList( false, 0, false, array( 'AllRelations' => true, 'AsObject' => false ) );
        foreach( OCEditorialStuffHandler::instances() as $instance )
        {
            foreach ( $reverseObjects as $reverseObject )
            {
                if( $reverseObject['contentclass_identifier'] == $instance->getFactory()->classIdentifier() )
                {
                    try
                    {
                        return $instance->fetchByObjectId( $reverseObject['id'] );
                    }
                    catch( Exception $e )
                    {

                    }
                }
            }
        }
        return $firstPost;
    }

    /**
     * @return eZBinaryFile
     */
    public function binaryFile()
    {
        $factory = $this->getFactory();
        if ( $factory instanceof OCEditorialStuffPostFileFactoryInterface )
        {
            $fileIdentifier = $factory->fileAttributeIdentifier();
            if ( isset( $this->dataMap[$fileIdentifier] ) && $this->dataMap[$fileIdentifier]->hasContent() )
            {
                return $this->dataMap[$fileIdentifier]->content();
            }
        }
        return null;
    }

    /**
     * @return eZBinaryFile
     */
    public function attributeFile()
    {
        $factory = $this->getFactory();
        if ( $factory instanceof OCEditorialStuffPostFileFactoryInterface )
        {
            $fileIdentifier = $factory->fileAttributeIdentifier();
            if ( isset( $this->dataMap[$fileIdentifier] ) )
            {
                return $this->dataMap[$fileIdentifier];
            }
        }
        return null;
    }

    public function downloadFileUrl()
    {
        $binaryFile = $this->binaryFile() ;
        if ( $binaryFile instanceof eZBinaryFile )
        {
            $url = 'content/download/' . $this->id() . '/' . $binaryFile->attribute( 'contentobject_attribute_id' );
            eZURI::transformURI( $url, false, 'full' );
            return $url;
        }
        return null;
    }

    public function apiDownloadFileUrl()
    {
        $binaryFile = $this->binaryFile() ;
        if ( $binaryFile instanceof eZBinaryFile )
        {
            if ( OpenPAConsiglioDefaultFactory::localFileServerIsEnabled() )
            {
                $url = OpenPAConsiglioDefaultFactory::localFileServerDownloadUrl( $binaryFile );
            }
            else
            {
                $router = new ezpRestRouter( new ezcMvcRequest() );
                $url = $router->generateUrl(
                    'consiglioApiSedutaDownloadAllegato',
                    array( 'Id' => $this->id() )
                );
                eZURI::transformURI( $url, false, 'full' );
            }
            return $url;
        }
        return null;
    }

    /**
     *
     * Allegato
     * id                   integer    id univoco Allegato
     * data_pubblicazione   string     data in formato 'Y-m-d H:i:s'
     * data_ultima_modifica string     data in formato 'Y-m-d H:i:s'
     * visibilita           string     consiglieri|referenti
     * file_name            string     nome del file
     * file_mime_type       string     myme del file
     * file_size            integer    dimensione del file
     * file_download_url    string     url per il download
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = array(
            'id' => $this->id(),
            'data_pubblicazione' => DateTime::createFromFormat( 'U', $this->getObject()->attribute( 'published' ) )->format( self::DATE_FORMAT ),
            'data_ultima_modifica' => DateTime::createFromFormat( 'U', $this->getObject()->attribute( 'modified' ) )->format( self::DATE_FORMAT ),
            'visibilita' => $this->currentState()->attribute( 'identifier' ),
            'title'      => $this->dataMap['name']->content(),
            'type'       => $this->dataMap['tipo']->toString(),
            'file_name' => null,
            'file_mime_type' => null,
            'file_size' => null,
            'file_download_url' => null
        );
        $binaryFile = $this->binaryFile() ;
        if ( $binaryFile instanceof eZBinaryFile )
        {
            $data['file_name'] = $binaryFile->attribute( 'original_filename' );
            $data['file_mime_type'] = $binaryFile->attribute( 'mime_type' );
            $data['file_size'] = $binaryFile->attribute( 'filesize' );
            $data['file_download_url'] = $this->apiDownloadFileUrl();
        }

        return $data;
    }

    protected function diff( $version )
    {
        $diff = array();
        if ( $version >= 1 && $version != $this->getObject()->attribute( 'current_version' ) )
        {
            $current = $this->getObject()->currentVersion();
            $version = $this->getObject()->version( $version );

            if ( $version instanceof eZContentObjectVersion && $current instanceof eZContentObjectVersion )
            {
                if ( $version->attribute( 'version' ) != $current->attribute( 'version' ) )
                {
                    /** @var eZContentObjectAttribute[] $oldAttributes */
                    $oldAttributes = $version->dataMap();
                    /** @var eZContentObjectAttribute[] $newAttributes */
                    $newAttributes = $current->dataMap();
                    foreach ( $oldAttributes as $oldAttribute )
                    {
                        $newAttribute = $newAttributes[$oldAttribute->attribute( 'contentclass_attribute_identifier' )];
                        if ( $oldAttribute->toString() !== $newAttribute->toString() )
                        {
                            $diff[$oldAttribute->attribute( 'contentclass_attribute_identifier' )] = array(
                                'old' => $oldAttribute,
                                'new' => $newAttribute
                            );
                        }
                    }
                }
            }
        }
        return $diff;
    }

}
