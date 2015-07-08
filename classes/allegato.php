<?php

class Allegato extends OCEditorialStuffPost
{

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {

    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'riferimento';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'riferimento' )
            return $this->getFirstReverseRelatedPost();

        return parent::attribute( $property );
    }

    public function onUpdate()
    {
        $post = $this->getFirstReverseRelatedPost();
        if ( $post instanceof Punto )
        {
            $post->createNotificationEvent( 'update_file', $this );
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

    public function jsonSerialize()
    {
        $data = array(
            'id' => $this->id(),
            'data_pubblicazione' => DateTime::createFromFormat( 'U', $this->getObject()->attribute( 'published' ) )->format( 'Y-m-d H:i:s' ),
            'data_ultima_modifica' => DateTime::createFromFormat( 'U', $this->getObject()->attribute( 'modified' ) )->format( 'Y-m-d H:i:s' ),
            'visibilita' => $this->currentState()->attribute( 'identifier' ),
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
            $data['file_download_url'] = $this->downloadFileUrl();
        }

        return $data;
    }

}