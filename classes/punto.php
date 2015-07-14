<?php

class Punto extends OCEditorialStuffPostNotifiable implements OCEditorialStuffPostFileContainerInterface, OCEditorialStuffPostInputActionInterface
{
    /**
     * @var PuntoFactory
     */
    protected $factory;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $dataMap;

    public function getFactory()
    {
        return $this->factory;
    }

    public function __construct( array $data = array(), OCEditorialStuffPostFactoryInterface $factory )
    {
        parent::__construct( $data, $factory );
        $this->dataMap = $this->getObject()->attribute( 'data_map' );
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'seduta_id';
        $attributes[] = 'seduta';
        $attributes[] = 'count_osservazioni';
        $attributes[] = 'documenti';
        $attributes[] = 'count_documenti';
        $attributes[] = 'invitati';
        $attributes[] = 'count_invitati';
        $attributes[] = 'can_add_osservazioni';
        $attributes[] = 'notification_subscribers';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( ( $property == 'seduta' || $property == 'seduta_id' ) )
            return $this->getSeduta( $property == 'seduta' );

        if ( $property == 'count_osservazioni' )
            return $this->getCount( 'osservazioni' );

        if ( $property == 'invitati' )
            return $this->getInvitati();

        if ( $property == 'count_invitati' )
            return $this->getCount( 'invitati' );

        if ( $property == 'documenti' )
            return $this->getAllegati( 'documenti' );

        if ( $property == 'count_documenti' )
            return $this->getCount( 'documenti' );

        if ( $property == 'can_add_osservazioni' )
            return $this->canAddOsservazioni();

        if ( $property == 'notification_subscribers' )
            return $this->notificationSubscribers();

        return parent::attribute( $property );
    }

    /**
     * @see PuntoFactory::fields()
     */
    public function indexOrario()
    {
        $timestamp = 0;
        if ( isset( $this->dataMap['orario_trattazione'] ) )
        {
            $orario = $this->dataMap['orario_trattazione']->content();
            if ( $orario instanceof eZTime )
            {
                $timestamp = $orario->attribute( 'time_of_day' );
            }
        }
        return intval( $timestamp );
    }

    /**
     * @see Seduta::reorderOdg
     * @param int $number
     */
    public function setNumber( $number )
    {
        if ( isset( $this->dataMap['n_punto'] ) )
        {
            $this->dataMap['n_punto']->fromString( $number );
            $this->dataMap['n_punto']->store();
            
            $object = $this->getObject();
            /** @var eZContentClass $class */
            $class = $object->attribute( 'content_class' );
            $name = $class->contentObjectName( $object );
            $object->setName( $name );        
            $object->store();		
            
            eZSearch::addObject( $object );
            eZContentCacheManager::clearObjectViewCacheIfNeeded(
                $object->attribute( 'id' )
            );
            eZDebug::writeNotice( "Set number $number for {$this->id()} ({$this->data['orario']})", __METHOD__ );
        }
    }

    /**
     * Override per lo pseudo stato _public
     * @param $stateIdentifier
     *
     * @return bool
     */
    public function is( $stateIdentifier )
    {
        if ( $stateIdentifier == '_public' ) //pseudo stato
        {
            $seduta = $this->getSeduta();
            if ( $seduta instanceof Seduta )
            {
                return $seduta->is( 'published' ) && $this->is( 'published' );
            }
        }
        return parent::is( $stateIdentifier );
    }

    /**
     * Restituisce la seduta di riferimeno o null
     *
     * @param bool $asObject
     *
     * @return int|Seduta
     */
    public function getSeduta( $asObject = true )
    {
        if ( isset( $this->dataMap['seduta_di_riferimento'] ) )
        {
            $contentArray = explode( '-', $this->dataMap['seduta_di_riferimento']->toString() );
            $sedutaID = array_pop( $contentArray );
            try
            {
                if ( is_numeric( $sedutaID ) )
                {
                    $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId(
                        $sedutaID
                    );
                    if ( !$asObject )
                    {
                        return $sedutaID;
                    }
                    else
                    {
                        return $seduta;
                    }
                }
            }
            catch ( Exception $e )
            {

            }
        }
        return null;
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Dettagli',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            ),
            array(
                'identifier' => 'documenti',
                'name' => 'Documenti',
                'template_uri' => "design:{$templatePath}/parts/documenti.tpl"
            ),
            array(
                'identifier' => 'osservazioni',
                'name' => 'Osservazioni',
                'template_uri' => "design:{$templatePath}/parts/osservazioni.tpl"
            )
        );
        if ( $currentUser->hasAccessTo( 'punto', 'inviti' ) )
        {
            $tabs[] = array(
                'identifier' => 'inviti',
                'name' => 'Gestione inviti',
                'template_uri' => "design:{$templatePath}/parts/inviti.tpl"
            );
        }
//        if ( $currentUser->hasAccessTo( 'punto', 'persone_coinvolte' ) )
//        {
//            $tabs[] = array(
//                'identifier' => 'persone_coinvolte',
//                'name' => 'Persone coinvolte',
//                'template_uri' => "design:{$templatePath}/parts/persone_coinvolte.tpl"
//            );
//        }
//        if ( $currentUser->hasAccessTo( 'punto', 'presenze' ) )
//        {
//            $tabs[] = array(
//                'identifier' => 'presenze',
//                'name' => 'Presenze',
//                'template_uri' => "design:{$templatePath}/parts/presenze.tpl"
//            );
//        }
        if ( $currentUser->hasAccessTo( 'punto', 'votazioni' ) )
        {
            $tabs[] = array(
                'identifier' => 'votazioni',
                'name' => 'Votazioni',
                'template_uri' => "design:{$templatePath}/parts/votazioni.tpl"
            );
        }

        if ( $currentUser->hasAccessTo( 'punto', 'notifiche' ) )
        {
            $tabs[] = array(
                'identifier' => 'notifiche',
                'name' => 'Gestione notifiche',
                'template_uri' => "design:{$templatePath}/parts/notifiche.tpl"
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

            OCEditorialStuffHistory::addHistoryToObjectId(
                $this->id(),
                'add_file',
                array(
                    'object_id' => $object->attribute( 'id' ),
                    'name' => $object->attribute( 'name' ),
                    'attribute' => $attributeIdentifier
                )
            );

            try
            {
                $allegato = OCEditorialStuffHandler::instance( 'allegati_seduta' )->fetchByObjectId( $object->attribute( 'id' ) );
                if ( $allegato instanceof OCEditorialStuffPostInterface )
                {
                    $this->createNotificationEvent( 'add_file', $allegato );
                }
            }
            catch( Exception $e )
            {

            }
            return true;
        }
        return false;
    }

    public function removeFile( eZContentObject $object, $attributeIdentifier )
    {
        OCEditorialStuffHistory::addHistoryToObjectId(
            $this->id(),
            'remove_file',
            array(
                'object_id' => $object->attribute( 'id' ),
                'name' => $object->attribute( 'name' ),
                'attribute' => $attributeIdentifier
            )
        );
    }

    public function fileFactory()
    {
        return OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
    }

    /**
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        if ( $beforeState->attribute( 'identifier' ) == 'draft'
             && $afterState->attribute( 'identifier' ) == 'published'
        )
        {
            if ( $this->is( '_public' ) )
            {
                $this->createNotificationEvent( 'publish' );
            }
        }
    }

    /**
     * Inserisce il parentObject come seduta di riferimento
     */
    public function onBeforeCreate()
    {
        try
        {
            $object = $this->getObject();
            /** @var eZContentObjectVersion $version */
            $version = $object->version( 1 );
            /** @var eZNodeAssignment[] $nodeAssignmentList */
            $nodeAssignmentList = $version->attribute( 'node_assignments' );
            
            if ( isset( $nodeAssignmentList[0] ) )
            {
                $parentNode = eZContentObjectTreeNode::fetch( $nodeAssignmentList[0]->attribute( 'parent_node' ) );
                $sedutaHandler = OCEditorialStuffHandler::instance( 'seduta' );
                if ( $parentNode instanceof eZContentObjectTreeNode
                     && $parentNode->attribute( 'class_identifier' ) == $sedutaHandler->getFactory()->classIdentifier() )
                {
                    /** @var eZContentObjectAttribute[] $dataMap */
                    $dataMap = $object->attribute( 'data_map' );
                    $dataMap['seduta_di_riferimento']->fromString( $parentNode->attribute( 'contentobject_id' ) );
                    $dataMap['seduta_di_riferimento']->store();                
                }
            }
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
        }
    }
    
    public function onCreate()
    {
        eZSearch::addObject( $this->getObject(), true );
        $seduta = $this->getSeduta();
        if ( $seduta instanceof Seduta )
        {
            $this->getSeduta()->reorderOdg();
        }
        $this->addUsersToNotifications();
        $this->createNotificationEvent( 'create' );
    }

    /**
     * @see Seduta::reorderOdg
     * Riordina l'odg della seduta di riferimento
     * notifica l'update se è published e la seduta di riferimento è published
     */
    public function onUpdate()
    {
        $seduta = $this->getSeduta();        
        if ( $seduta instanceof Seduta )
        {
            $this->getSeduta()->reorderOdg();
        }

        if ( $this->is( '_public' ) )
        {
            $this->createNotificationEvent( 'update' );
        }
    }

    public function handleCreateNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per gli interessati alla materia
        // prepara notifica per i referenti

        echo '<pre>';
        echo 'create';
        print_r($event);
        exit;


    }

    public function handlePublishNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per gli interessati alla materia
        // prepara notifica per i referenti

        echo '<pre>';
        echo 'update';
        print_r($event);
        exit;

    }

    public function handleUpdateNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per i referenti


        // Prepara notifica per gli interessati alla materia
        $materia = $this->ge
        $utentiAppassionati = array();
        foreach( $this->getMateria() as $materia )
            $utentiAppassionati = array_merge( $utentiAppassionati, OCEditorialStuffNotificationRule::fetchUserIdList( 'materia/like', $materia->attribute( 'id' ) ) );
        $utentiAppassionati = array_unique( $utentiAppassionati );
    }

    public function handleAddFileNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per i referenti
        // prepara notifica per gli invitati
    }

    public function handleUpdateFileNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per i referenti
        // prepara notifica per gli invitati
    }

    public function executeAction( $actionIdentifier, $actionParameters )
    {
        if ( $actionIdentifier == 'AddInvitato' && isset( $actionParameters['invitato'] ) )
        {
            $invitatoObject = eZContentObject::fetch( $actionParameters['invitato'] );
            if ( $invitatoObject instanceof eZContentObject )
            {
                $this->addInvitato( $invitatoObject );
            }
        }
    }

    public function notificationSubscribers()
    {
        $data = array();
        foreach( $this->getFactory()->notificationEventTypesConfiguration() as $identifier => $type )
        {
            $data[$identifier] = array(
                'name' => $type['name'],
                'user_id_list' => OCEditorialStuffNotificationRule::fetchUserIdList( $identifier, $this->id() )
            );
        }
        return $data;
    }

    protected function addUsersToNotifications()
    {
        $types = $this->getFactory()->availableNotificationEventTypes();

        foreach( $types as $type )
        {
            $userIds = array();
            switch ( $type )
            {
                case 'create':
                    // referenti
                    $userIds = $this->getIdsReferenti();

                    break;
                case 'update':

                    break;
                case 'add_file':

                    break;
                case 'update_file':

                    break;
            }
            if ( count( $userIds ) )
            {
                $this->createNotificationTypeRule( $type, $userIds );
            }
        }
    }

    /**
     * @see self::executeAction()
     * @param eZContentObject $object
     *
     * @return bool
     */
    protected function addInvitato( eZContentObject $object )
    {
        try
        {
            $attributeIdentifier = 'invitati';
            $invitatoFactory = OCEditorialStuffHandler::instance( 'invitato' );
            $invitato = $invitatoFactory->fetchByObjectId( $object->attribute( 'id' ) );
            if ( $invitato instanceof OCEditorialStuffPostInterface )
            {
                if ( isset( $this->dataMap[$attributeIdentifier] ) )
                {
                    // creo invito
                    $invito = Invito::create( $this->getObject(), $invitato->getObject() );

                    if ( $invito instanceof eZContentObject )
                    {

                        // aggiorno attributo del punto
                        $ids = explode( '-', $this->dataMap[$attributeIdentifier]->toString() );
                        $ids[] = $invitato->id();
                        $ids = array_unique( $ids );
                        $this->dataMap[$attributeIdentifier]->fromString( implode( '-', $ids ) );
                        $this->dataMap[$attributeIdentifier]->store();
                        eZSearch::addObject( $this->getObject() );
                        eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->id() );

                        // aggiorno storia del punto
                        OCEditorialStuffHistory::addHistoryToObjectId(
                            $this->id(),
                            'add_invitato',
                            array(
                                'object_id' => $invitato->id(),
                                'name' => $invitato->getObject()->attribute( 'name' ),
                                'invito_object_id' => $invito->attribute( 'id' )
                            )
                        );

                        // notifico il punto
                        $this->createNotificationEvent( 'add_invitato', $invitato );
                    }
                    return true;
                }
            }
        }
        catch  ( Exception $e )
        {

        }
        return false;
    }

    /**
     * Restituisce un array con gli id dei referenti politici e tecnici
     *
     * @return array
     */

    protected function getIdsReferenti()
    {
        $result = array();
        $attributes = array('referente_politico', 'referente_tecnico');
        foreach ($attributes as $value)
        {
            if (isset( $this->dataMap[$value]) && $this->dataMap[$value]->hasContent())
            {
                $list = $this->dataMap[$value]->content();
                foreach ($list['relation_list'] as $v)
                {
                    $result[]= $v['contentobject_id'];
                }
            }
        }
        return array_unique($result);
    }

    protected function canAddOsservazioni()
    {
        if ( $this->is( '_public' ) )
        {
            if ( isset( $this->dataMap['consenti_osservazioni'] ) && isset( $this->dataMap['termine_osservazioni'] ) )
            {
                $now = time();

                return $this->dataMap['consenti_osservazioni']->attribute( 'data_int' ) == 1
                       && $now < $this->dataMap['termine_osservazioni']->toString();

            }
        }
        return false;
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
                    $result[] = $factory->instancePost( array( 'object_id' => $id ) );
                }
                catch( Exception $e )
                {

                }
            }
        }
        return $result;
    }

    protected function getInvitati()
    {
        $result = array();
        if ( isset( $this->dataMap['invitati'] ) )
        {
            $factory = OCEditorialStuffHandler::instance( 'invitato' )->getFactory();
            $idArray = explode( '-', $this->dataMap['invitati']->toString() );
            foreach( $idArray as $id )
            {
                try
                {
                    $result[] = $factory->instancePost( array( 'object_id' => $id ) );
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

    // @todo usare una factory ad hoc?
    protected function getMateria( $attributeIdentifier = null )
    {
        return $this->stringRelatedObjectAttribute( 'materia', $attributeIdentifier );
    }

    protected function stringRelatedObjectAttribute( $identifier, $attributeIdentifier = null )
    {
        $data = array();
        $ids = explode( '-', $this->stringAttribute( $identifier ) );
        foreach( $ids as $id )
        {
            $related = eZContentObject::fetch( $id );
            if ( $related instanceof eZContentObject )
            {
                if ( $attributeIdentifier )
                {
                    if ( $related->hasAttribute( $attributeIdentifier ) )
                    {
                        $data[] = $related->attribute( $attributeIdentifier );
                    }
                    else
                    {
                        /** @var eZContentObjectAttribute[] $dataMap */
                        $dataMap = $related->attribute( 'data_map' );
                        if ( isset( $dataMap[$attributeIdentifier] ) )
                        {
                            $data[] = $dataMap[$attributeIdentifier]->toString();
                        }
                    }
                }
                else
                {
                    $data[] = $related;
                }
            }
        }
        return empty( $data ) ? null : $data;
    }

    protected function stringAttribute( $identifier, $callback = null )
    {
        $string = '';
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $string = $this->dataMap[$identifier]->toString();
        }
        if ( is_callable( $callback ) )
        {
            return call_user_func( $callback, $string );
        }
        return $string;
    }

    /**
     *
     * Punto
     * id                 integer    id univoco Punto
     * seduta             integer    id univoco Seduta
     * numero             integer    numero del punto relativo all’odg
     * orario             string     orario di trattazione del punto in formato “H:i”
     * materia            string[]   Nomi delle materie relazionate da verificare se 1 o molti
     * referente_politico string     Nome del referente politico
     * referente_tecnico  string     Nome del referente tecnico
     * documenti          integer    numero dei documenti allegati
     * invitati           integer    numero delle persone invitate
     * osservazioni       integer    numero delle osservazioni presenti
     *
     * @see ConsiglioApiController
     * @return array
     */
    public function jsonSerialize()
    {
        $locale = eZLocale::instance();
        return array(
            'id' => $this->id(),
            'seduta' => (int)$this->attribute( 'seduta_id' ),
            'numero' => $this->stringAttribute( 'n_punto', 'intval' ),
            'orario' => $locale->formatShortTime( $this->dataMap['orario_trattazione']->content()->attribute( 'timestamp' ) ),
            'materia' => $this->getMateria( 'name' ),
            'referente_politico' => $this->stringRelatedObjectAttribute( 'referente_politico', 'name' ),
            'referente_tecnico' => $this->stringRelatedObjectAttribute( 'referente_tecnico', 'name' ),
            'documenti' => $this->attribute( 'count_documenti' ),
            'invitati' => $this->attribute( 'count_invitati' ),
            'osservazioni' => $this->attribute( 'count_osservazioni' ),
        );
    }

}