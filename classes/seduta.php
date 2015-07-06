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
        $attributes[] = 'odg';
        $attributes[] = 'documenti';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'odg')
            return $this->odg();

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

    /**
     * @return Punto[]
     */
    protected function odg()
    {
        $sedutaId = $this->object->attribute( 'id' );
        return OCEditorialStuffHandler::instance( 'punto', array( 'seduta' => $sedutaId ) )->fetchItems(
            array(
                'limit' => 100,
                'offset' => 0,
                'filters' => 'submeta_seduta_di_riferimento___id_si:' . $this->id(),
                'sort' => array( 'extra_orario_i' => 'asc' )
            )
        );
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
}