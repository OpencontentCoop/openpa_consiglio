<?php

class Seduta extends OCEditorialStuffPost implements OCEditorialStuffPostFileContainerInterface
{
    public function __construct( array $data = array(), OCEditorialStuffPostFactoryInterface $factory )
    {
        parent::__construct( $data, $factory );
    }


    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'referenti';
        $attributes[] = 'odg';
        $attributes[] = 'count_documenti';
        $attributes[] = 'documenti';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'referenti')
            return $this->referenti();

        if ( $property == 'odg')
            return $this->odg();

        if ( $property == 'count_documenti' )
            return $this->getCount( 'documenti' );

        if ( $property == 'documenti' )
            return $this->getAllegati( 'documenti' );

        return parent::attribute( $property );
    }

    public function reorderOdg()
    {
        foreach( $this->odg() as $index => $punto )
        {
            $number = $index +1;
            $punto->setNumber( $number );
        }
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Informazioni',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            ),
            array(
                'identifier' => 'documenti',
                'name' => 'Documenti',
                'template_uri' => "design:{$templatePath}/parts/documenti.tpl"
            )
        );
        if ( $currentUser->hasAccessTo( 'seduta', 'persone_coinvolte' ) )
        {
            $tabs[] = array(
                'identifier' => 'persone_coinvolte',
                'name' => 'Persone coinvolte',
                'template_uri' => "design:{$templatePath}/parts/persone_coinvolte.tpl"
            );
        }
        if ( $currentUser->hasAccessTo( 'seduta', 'presenze' ) )
        {
            $tabs[] = array(
                'identifier' => 'presenze',
                'name' => 'Presenze',
                'template_uri' => "design:{$templatePath}/parts/presenze.tpl"
            );
        }
        if ( $currentUser->hasAccessTo( 'seduta', 'votazioni' ) )
        {
            $tabs[] = array(
                'identifier' => 'votazioni',
                'name' => 'Votazioni e esito',
                'template_uri' => "design:{$templatePath}/parts/votazioni.tpl"
            );
        }
        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );
        return $tabs;
    }

    public function addFile( eZContentObject $object, $attributeIdentifier )
    {
        if ( isset( $this->dataMap[$attributeIdentifier] ) )
        {
            $ids = explode( '-', $this->dataMap[$attributeIdentifier]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$attributeIdentifier]->fromString( implode( '-', $ids ) );
            $this->dataMap[$attributeIdentifier]->store();
            eZSearch::addObject( $this->getObject() );
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->id() );
            //            OCEditorialStuffHistory::addHistoryToObjectId(
            //                $this->id(),
            //                'addfile',
            //                array(
            //                    'object_id' => $object->attribute( 'id' ),
            //                    'name' => $object->attribute( 'name' ),
            //                    'attribute' => $attributeIdentifier
            //                )
            //            );
            return true;
        }
        return false;
    }

    public function removeFile( eZContentObject $object, $attributeIdentifier )
    {
        // TODO: Implement removeFile() method.
    }

    public function fileFactory()
    {
        return OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
    }

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        foreach( $this->odg() as $punto )
        {
            if ( $punto->is( '_public' ) )
            {
                $punto->createNotificationEvent( 'publish' );
            }
        }
    }

    protected function referenti()
    {
        //@todo
        return array();
    }

    /**
     * @return Punto[]
     */
    protected function odg()
    {
        $sedutaId = $this->object->attribute( 'id' );
        $items = OCEditorialStuffHandler::instance( 'punto', array( 'seduta' => $sedutaId ) )->fetchItems(
            array(
                'limit' => 100,
                'offset' => 0,
                'filters' => 'submeta_seduta_di_riferimento___id_si:' . $this->id(),
                'sort' => array( 'extra_orario_i' => 'asc' )
            )
        );
        eZDebug::writeNotice( var_export( OCEditorialStuffHandler::getLastFetchData(), 1 ), __METHOD__ );
        return $items;
    }

    protected function getAllegati( $identifier )
    {
        $result = array();
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $factory = OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
            $idArray = explode( '-', $this->dataMap[$identifier]->toString() );
            foreach( $idArray as $id )
            {
                try
                {
                    $result[] = new Allegato( array( 'object_id' => $id ), $factory );
                }
                catch( Exception $e )
                {

                }
            }
        }
        return $result;
    }

    protected function getCount( $identifier )
    {
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $contentArray = explode( '-', $this->dataMap[$identifier]->toString() );
            if ( isset( $contentArray[0] ) && $contentArray[0] == '' )
            {
                unset( $contentArray[0] );
            }
            return count( $contentArray );
        }
        return 0;
    }

    protected function stringAttribute( $identifier, $callback = null )
    {
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $string = $this->dataMap[$identifier]->toString();
            if ( is_callable( $callback ) )
            {
                return call_user_func( $callback, $string );
            }
            return $string;
        }
        return '';
    }

    /**
     * @see ConsiglioApiController
     * @return array
     */
    public function jsonSerialize()
    {
        if ( isset( $this->dataMap['data'] ) && $this->dataMap['data']->hasContent()
             && isset( $this->dataMap['orario'] ) && $this->dataMap['orario']->hasContent() )
        {
            try
            {

                /** @var eZDate $data */
                $data = $this->dataMap['data']->content();
                /** @var eZTime $ora */
                $ora = $this->dataMap['orario']->content();

                $dateTime = new DateTime();
                $dateTime->setTimestamp( $data->attribute( 'timestamp' ) );
                $dateTime->setTime( $ora->attribute( 'hour' ), $ora->attribute( 'minute' ) );

                return array(
                    'id' => $this->id(),
                    'data' => $dateTime->format( 'Y-m-d H:i:s' ),
                    'protocollo' => $this->stringAttribute( 'protocollo', 'intval' ),
                    'stato' => $this->currentState()->attribute( 'identifier' ),
                    'documenti' => $this->attribute( 'count_documenti' )
                );
            }
            catch ( Exception $e )
            {

            }
        }
        return false;
    }
}