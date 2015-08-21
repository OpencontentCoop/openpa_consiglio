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

    /**
     * @var Seduta
     */
    protected $seduta;

    public function getFactory()
    {
        return $this->factory;
    }

    public function __construct(
        array $data = array(),
        OCEditorialStuffPostFactoryInterface $factory
    )
    {
        parent::__construct( $data, $factory );
        $this->dataMap = $this->getObject()->attribute( 'data_map' );
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'seduta_id';
        $attributes[] = 'seduta';
        $attributes[] = 'osservazioni';
        $attributes[] = 'count_osservazioni';
        $attributes[] = 'documenti';
        $attributes[] = 'count_documenti';
        $attributes[] = 'invitati';
        $attributes[] = 'count_invitati';
        $attributes[] = 'can_add_osservazioni';
        $attributes[] = 'notification_subscribers';
        $attributes[] = 'votazioni';
        $attributes[] = 'verbale';
        $attributes[] = 'materia';

        return $attributes;
    }

    public function attribute( $property )
    {
        if ( ( $property == 'seduta' || $property == 'seduta_id' ) )
        {
            /** @return Seduta */
            return $this->getSeduta( $property == 'seduta' );
        }

        if ( $property == 'osservazioni' )
        {
            /** @return Osservazione[] */
            return $this->getOsservazioni( 'osservazioni' );
        }

        if ( $property == 'count_osservazioni' )
        {
            /** @return int */
            return $this->getCount( 'osservazioni' );
        }

        if ( $property == 'invitati' )
        {
            /** @return OCEditorialStuffPostInterface[] */
            return $this->getInvitati();
        }

        if ( $property == 'count_invitati' )
        {
            /** @return int */
            return $this->getCount( 'invitati' );
        }

        if ( $property == 'documenti' )
        {
            /** @return Allegato[] */
            return $this->getAllegati( 'documenti' );
        }

        if ( $property == 'count_documenti' )
        {
            /** @return int */
            return $this->getCount( 'documenti' );
        }

        if ( $property == 'can_add_osservazioni' )
        {
            /** @return bool */
            return $this->canAddOsservazioni();
        }

        if ( $property == 'notification_subscribers' )
        {
            /** @return int */
            return $this->notificationSubscribers();
        }

        if ( $property == 'votazioni' )
        {
            /** @return Votazione[] */
            return $this->votazioni();
        }

        if ( $property == 'verbale' )
        {
            /** @return string */
            return $this->verbale();
        }

        if ( $property == 'materia' )
        {
            /** @return string[] */
            return $this->getMateria( 'name' );
        }

        return parent::attribute( $property );
    }

    /**
     * @see Seduta::verbale
     * @return string
     */
    public function verbale()
    {
        return $this->getSeduta()->verbale( $this->id() );
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
     *
     * @param int $number
     */
    public function setNumber( $number )
    {
        if ( isset( $this->dataMap['n_punto'] ) )
        {
            $this->dataMap['n_punto']->fromString( intval( $number ) );
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
            eZDebug::writeNotice(
                "Set number $number for {$this->id()} ({$this->data['orario']})",
                __METHOD__
            );
        }
    }

    /**
     * Override per lo pseudo stato _public
     *
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
                return $seduta->isAfter( 'published', true )  && $this->is( 'published' );
            }
        }

        return parent::is( $stateIdentifier );
    }

    /**
     * Restituisce la Seduta o l'ide Seduta di riferimento o null
     *
     * @param bool $asObject
     *
     * @return Seduta|int
     */
    public function getSeduta( $asObject = true )
    {
        if ( $this->seduta === null )
        {
            if ( isset( $this->dataMap['seduta_di_riferimento'] ) )
            {
                $contentArray = explode( '-', $this->dataMap['seduta_di_riferimento']->toString() );
                $sedutaID = array_pop( $contentArray );
                try
                {
                    if ( is_numeric( $sedutaID ) )
                    {
                        $this->seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId(
                            $sedutaID
                        );
                    }
                }
                catch ( Exception $e )
                {

                }
            }
        }
        if ( $this->seduta instanceof Seduta )
        {
            if ( !$asObject )
            {
                return $this->seduta->id();
            }
            else
            {
                return $this->seduta;
            }
        }

        return $this->seduta;
    }

    /**
     * @return array
     */
    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $hasAccess = $currentUser->hasAccessTo( 'consiglio', 'admin' );
        $isAdmin = $hasAccess['accessWord'] != 'no';
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
        if ( $isAdmin )
        {
            $tabs[] = array(
                'identifier' => 'inviti',
                'name' => 'Gestione inviti',
                'template_uri' => "design:{$templatePath}/parts/inviti.tpl"
            );
            $tabs[] = array(
                'identifier' => 'votazioni',
                'name' => 'Votazioni',
                'template_uri' => "design:{$templatePath}/parts/votazioni.tpl"
            );
            $tabs[] = array(
                'identifier' => 'notifiche',
                'name' => 'Mail di avviso',
                'template_uri' => "design:{$templatePath}/parts/notifiche.tpl"
            );
            $tabs[] = array(
                'identifier' => 'history',
                'name' => 'Cronologia',
                'template_uri' => "design:{$templatePath}/parts/history.tpl"
            );
        }

        return $tabs;
    }

    /**
     * @param eZContentObject $object
     * @param string $attributeIdentifier
     *
     * @return bool
     */
    public function addFile( eZContentObject $object, $attributeIdentifier )
    {
        $result = false;
        if ( $attributeIdentifier == 'documenti' )
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
                    $allegato = OCEditorialStuffHandler::instance(
                        'allegati_seduta'
                    )->fetchByObjectId(
                        $object->attribute( 'id' )
                    );
                    if ( $allegato instanceof OCEditorialStuffPostInterface )
                    {
                        $this->createNotificationEvent( 'add_file', $allegato );
                    }
                }
                catch ( Exception $e )
                {

                }
                $result = true;
            }
        }
        elseif ( $attributeIdentifier == 'osservazioni' )
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
                    'add_osservazione',
                    array(
                        'object_id' => $object->attribute( 'id' ),
                        'name' => $object->attribute( 'name' ),
                        'attribute' => $attributeIdentifier
                    )
                );

                try
                {
                    $osservazione = OCEditorialStuffHandler::instance(
                        'osservazioni'
                    )->fetchByObjectId(
                        $object->attribute( 'id' )
                    );
                    if ( $osservazione instanceof OCEditorialStuffPostInterface )
                    {
                        $this->createNotificationEvent( 'add_osservazione', $osservazione );
                    }
                }
                catch ( Exception $e )
                {

                }
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param eZContentObject $object
     * @param string $attributeIdentifier
     */
    public function removeFile( eZContentObject $object, $attributeIdentifier )
    {
        if ( $attributeIdentifier == 'documenti' )
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
        elseif ( $attributeIdentifier == 'osservazioni' )
        {
            OCEditorialStuffHistory::addHistoryToObjectId(
                $this->id(),
                'remove_osservazione',
                array(
                    'object_id' => $object->attribute( 'id' ),
                    'name' => $object->attribute( 'name' ),
                    'attribute' => $attributeIdentifier
                )
            );
        }
    }

    /**
     * @param string $attributeIdentifier
     *
     * @return OCEditorialStuffPostFactoryInterface
     * @throws Exception
     */
    public function fileFactory( $attributeIdentifier )
    {
        if ( $attributeIdentifier == 'documenti' )
        {
            return OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
        }
        elseif ( $attributeIdentifier == 'osservazioni' )
        {
            return OCEditorialStuffHandler::instance( 'osservazioni' )->getFactory();
        }
        throw new Exception( "FileFactory for $attributeIdentifier not found" );
    }

    /**
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
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
                $parentNode = eZContentObjectTreeNode::fetch(
                    $nodeAssignmentList[0]->attribute( 'parent_node' )
                );
                $sedutaHandler = OCEditorialStuffHandler::instance( 'seduta' );
                if ( $parentNode instanceof eZContentObjectTreeNode
                     && $parentNode->attribute( 'class_identifier' ) == $sedutaHandler->getFactory(
                    )->classIdentifier()
                )
                {
                    /** @var eZContentObjectAttribute[] $dataMap */
                    $dataMap = $object->attribute( 'data_map' );
                    $dataMap['seduta_di_riferimento']->fromString(
                        $parentNode->attribute( 'contentobject_id' )
                    );
                    $dataMap['seduta_di_riferimento']->store();

                    /*
                    // Verifico che non ci sia un punto nella seduta alla stessa ora, se si aumento l'orario di un minuto fino a trovare un orario libero
                    // TODO: Sostituire con validazione e messaggio all'utente?
                    $seduta = OCEditorialStuffHandler::instance( $parentNode->attribute( 'class_identifier' ) )->fetchByObjectId( $parentNode->ContentObjectID );
                    $odgTimes = $seduta->odgTimes();

                    $locale = eZLocale::instance();
                    $orario = $dataMap['orario_trattazione']->content();
                    $timestamp = '';
                    if ( $orario instanceof eZTime )
                    {
                        $timestamp = $orario->attribute( 'timestamp' );
                    }

                    $saved = false;
                    do {
                        if (in_array( $timestamp, $odgTimes ))
                        {
                            $timestamp += 60;
                        } else {
                            $dataMap['orario_trattazione']->fromString( $locale->formatShortTime( $timestamp ));
                            $dataMap['orario_trattazione']->store();
                            $saved = true;
                        }
                    }
                    while (!$saved);
                    */
                }
            }
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
        }
    }

    /**
     * Reindicizza oggetto
     * Richiede ordinamento odg
     * Richiede iscrizione degli utenti alle notifiche
     * Lancia evento 'create'
     */
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


        // TODO: serve un metodo che elimina i vecchi referenti
        $this->addUsersToNotifications();

        if ( $this->is( '_public' ) )
        {
            $this->createNotificationEvent( 'update' );
        }
    }

    public function handleCreateNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {

    }

    public function handlePublishNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per gli iscritti all'update
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList(
            'punto/publish',
            null,
            $this->id()
        );

        foreach ( $subscribersRules as $subscribersRule )
        {
            $template = 'punto/publish';
            $template .= in_array( $subscribersRule->attribute( 'user_id' ), $this->getIdsReferenti() ) ? '/referente' : '/consigliere';
            $this->createNotificationItem( $event, $subscribersRule, $template );
        }

        $utentiAppassionati = $this->getAppassionati();
        if ( !empty( $utentiAppassionati ) )
        {
            foreach ( $utentiAppassionati as $subscribersRule )
            {
                $template = 'punto/publish/interessato';
                $this->createNotificationItem( $event, $subscribersRule, $template );
            }
        }
    }

    public function handleUpdateNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per gli iscritti all'update
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList(
            'punto/update',
            null,
            $this->id()
        );

        foreach ( $subscribersRules as $subscribersRule )
        {
            $template = 'punto/update';
            $template .= in_array( $subscribersRule->attribute( 'user_id' ), $this->getIdsReferenti() ) ? '/referente' : '/consigliere';
            $this->createNotificationItem( $event, $subscribersRule, $template );
        }

        $utentiAppassionati = $this->getAppassionati();
        if ( !empty( $utentiAppassionati ) )
        {
            foreach ( $utentiAppassionati as $subscribersRule )
            {
                $template = 'punto/update/interessato';
                $this->createNotificationItem( $event, $subscribersRule, $template );
            }
        }
    }

    public function handleMoveNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList(
            null,
            null,
            $this->id()
        );
        $subscribersRules = array_merge( $subscribersRules, $this->getAppassionati() );
        foreach ( $subscribersRules as $subscribersRule )
        {
            $template = 'punto/move';
            $this->createNotificationItem( $event, $subscribersRule, $template );
        }
    }

    public function handleAddFileNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per gli iscritti all'update
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList(
            'punto/add_file',
            null,
            $this->id()
        );

        foreach ( $subscribersRules as $subscribersRule )
        {
            $template = 'punto/add_file';
            $template .= in_array( $subscribersRule->attribute( 'user_id' ), $this->getIdsReferenti() ) ? '/referente' : '/consigliere';
            $this->createNotificationItem( $event, $subscribersRule, $template );
        }

        $utentiAppassionati = $this->getAppassionati();
        if ( !empty( $utentiAppassionati ) )
        {
            foreach ( $utentiAppassionati as $subscribersRule )
            {
                $template = 'punto/add_file/interessato';
                $this->createNotificationItem( $event, $subscribersRule, $template );
            }
        }
    }

    public function handleUpdateFileNotification(
        $event,
        OCEditorialStuffPostInterface $refer = null
    )
    {
        // prepara notifica per gli iscritti all'update
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList(
            'punto/update_file',
            null,
            $this->id()
        );

        foreach ( $subscribersRules as $subscribersRule )
        {
            $template = 'punto/update_file';
            $template .= in_array( $subscribersRule->attribute( 'user_id' ), $this->getIdsReferenti() ) ? '/referente' : '/consigliere';
            $this->createNotificationItem( $event, $subscribersRule, $template );
        }

        $utentiAppassionati = $this->getAppassionati();
        if ( !empty( $utentiAppassionati ) )
        {
            foreach ( $utentiAppassionati as $subscribersRule )
            {
                $template = 'punto/update_file/interessato';
                $this->createNotificationItem( $event, $subscribersRule, $template );
            }
        }
    }

    /**
     * @param eZNotificationEventType $event
     * @param OCEditorialStuffNotificationRule $subscribersRule
     * @param $templateName
     *
     * @return OpenPAConsiglioNotificationItem
     */
    protected function createNotificationItem( eZNotificationEventType $event, OCEditorialStuffNotificationRule $subscribersRule, $templateName )
    {
        //@todo digest a livello di user
//        $type = $subscribersRule->attribute( 'use_digest' ) ?
//            OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT : OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT;
//
//        $time = $subscribersRule->attribute( 'use_digest' ) ? 'todo' : time(); //@todo

        if ( $this->getSeduta()->isAfter( 'sent', true ) )
        {
            $type = OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT;
            $time = time();
        }
        else
        {
            $type = OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT;
            $now = new DateTime();
            $now->setTime( 20, 00 );
            $time = $now->getTimestamp();
        }

        $diff = array();
        $notifiedVersion = $event->attribute( OCEditorialStuffEventType::FIELD_VERSION ) - 1;
        if ( $notifiedVersion > 1 )
        {
            $current = $this->getObject()->currentVersion();
            $version = $this->getObject()->version( $notifiedVersion );

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
                        /** @var eZContentClassAttribute $contentClassAttr */
                        $contentClassAttr = $newAttribute->attribute( 'contentclass_attribute' );
//                        $diff[$contentClassAttr->attribute( 'id' )] = $contentClassAttr->diff(
//                            $oldAttribute,
//                            $newAttribute,
//                            false
//                        );
                        if ( $oldAttribute->toString() !== $newAttribute->toString() )
                        {
                            $diff[] = $newAttribute;
                        }
                    }
                }
            }
        }

        $variables = array(
            'seduta' => $this->getSeduta(),
            'punto' => $this,
            'diff' => $diff
        );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach( $variables as $name => $value )
        {
            $tpl->setVariable( $name, $value );
        }
        $content = $tpl->fetch( 'design:consiglio/notification/mail/' . $templateName . '.tpl');
        $subject = $tpl->variable( 'subject' );

        return OpenPAConsiglioNotificationItem::create(
            array(
                'object_id'          => $this->id(),
                'user_id'            => $subscribersRule->attribute( 'user_id' ),
                'created_time'       => time(),
                'type'               => $type,
                'subject'            => $subject,
                'body'               => $content,
                'expected_send_time' => $time
            )
        );
    }

    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null )
    {
        if ( $actionIdentifier == 'AddInvitato' && isset( $actionParameters['invitato'] ) )
        {
            $invitatoObject = eZContentObject::fetch( $actionParameters['invitato'] );
            if ( $invitatoObject instanceof eZContentObject )
            {
                //$ora =  $actionParameters['ora'];
                $this->addInvitato( $invitatoObject );
            }
        }

        if ( $actionIdentifier == 'SortAllegati'
             && isset( $actionParameters['identifier'] )
             && isset( $actionParameters['sort_ids'] ) )
        {
            $this->sortAllegati( $actionParameters['identifier'], $actionParameters['sort_ids'] );
        }
    }

    public function moveIn( Seduta $seduta )
    {
        $currentSeduta = $this->getSeduta();

        eZDebug::writeNotice( "Salvo alert", __METHOD__ );
        $this->dataMap['alert']->fromString( SQLIContentUtils::getRichContent( '<p>Il punto è stato spostato dalla ' . $currentSeduta->getObject()->attribute( 'name' ) .'</p>' ) );
        $this->dataMap['alert']->store();

        eZDebug::writeNotice( "Aggiorno la seduta", __METHOD__ );
        $this->dataMap['seduta_di_riferimento']->fromString( $seduta->id() );
        $this->dataMap['seduta_di_riferimento']->store();

        eZDebug::writeNotice( "Reindex punto", __METHOD__ );
        eZSearch::addObject( $this->getObject() );
        eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->id() );

        eZDebug::writeNotice( "Muovo punto", __METHOD__ );
        $move = eZContentObjectTreeNodeOperations::move( $this->getObject()->attribute( 'main_node_id' ), $seduta->getObject()->attribute( 'main_node_id' ) );

        eZDebug::writeNotice( "Creo evento", __METHOD__ );
        $this->createNotificationEvent( 'move' );

        eZDebug::writeNotice( "Aggiorno seduta {$currentSeduta->id()}", __METHOD__ );
        $currentSeduta->reorderOdg();

        eZDebug::writeNotice( "Aggiorno seduta {$seduta->id()}", __METHOD__ );
        $seduta->reorderOdg();

        if ( !$move )
        {
            eZDebug::writeError( "Spostamento non riuscito", __METHOD__ );
        }
    }

    public function notificationSubscribers()
    {
        $data = array();
        foreach ( $this->getFactory()->notificationEventTypesConfiguration()
                  as $identifier => $type )
        {
            $data[$identifier] = array(
                'name' => $type['name'],
                'user_id_list' => OCEditorialStuffNotificationRule::fetchUserIdList(
                    'punto/' . $identifier,
                    $this->id()
                )
            );
        }

        return $data;
    }

    protected function addUsersToNotifications()
    {
        $types = $this->getFactory()->availableNotificationEventTypes();

        foreach ( $types as $type )
        {
            $userIds = array();
            switch ( $type )
            {
                case 'create':
                    $userIds = $this->getIdsReferenti();

                    break;
                case 'publish':
                case 'update':
                case 'add_file':
                case 'update_file':
                    $userIds = array_merge(
                        $this->getIdsReferenti(),
                        $this->getSeduta()->partecipanti( false )
                    );
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
     *
     * @param eZContentObject $object
     *
     * @return bool
     */
    protected function addInvitato( eZContentObject $object, $ora = false )
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
                    $invito = Invito::create( $this->getObject(), $invitato->getObject(), $ora );

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
        catch ( Exception $e )
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
        $attributes = array( 'referente_politico', 'referente_tecnico' );
        foreach ( $attributes as $value )
        {
            if ( isset( $this->dataMap[$value] ) && $this->dataMap[$value]->hasContent() )
            {
                $list = $this->dataMap[$value]->content();
                foreach ( $list['relation_list'] as $v )
                {
                    $result[] = $v['contentobject_id'];
                }
            }
        }

        return array_unique( $result );
    }

    /**
     * Restituisce un array con gli id degli yenyi appassionati alla materia
     *
     * @return OCEditorialStuffNotificationRule[]
     */
    protected function getAppassionati()
    {
        // Prepara notifica per gli interessati alla materia
        $utentiAppassionati = array();
        foreach ( $this->getMateria() as $materia )
        {
            if ( $materia instanceof eZContentObject )
            {
                $utentiAppassionati = array_merge(
                    $utentiAppassionati,
                    OCEditorialStuffNotificationRule::fetchList(
                        'materia/like',
                        null,
                        $materia->attribute( 'id' )
                    )
                );
            }
        }
        $utentiAppassionati = array_unique( $utentiAppassionati );
        return $utentiAppassionati;
    }

    /**
     * @return bool
     */
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

    /**
     * @param $identifier
     *
     * @return Allegato[]
     */
    protected function getAllegati( $identifier )
    {
        $result = array();
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $factory = OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
            $idArray = explode( '-', $this->dataMap[$identifier]->toString() );
            foreach ( $idArray as $id )
            {
                try
                {
                    $result[] = $factory->instancePost( array( 'object_id' => $id ) );
                }
                catch ( Exception $e )
                {

                }
            }
        }

        return $result;
    }

    /**
     * @todo rifare con meno foreach...
     * @param string $identifier
     * @param array $sortIds
     */
    protected function sortAllegati( $identifier, $sortIds )
    {
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $idArray = explode( '-', $this->dataMap[$identifier]->toString() );
            foreach( $idArray as $id )
            {
                if ( !in_array( $id, $sortIds ) )
                {
                    $sortIds[] = $id;
                }
            }
            if ( count( $idArray ) != count( $sortIds ) )
            {
                foreach( $sortIds as $index => $id )
                {
                    if ( !in_array( $id, $idArray ) )
                    {
                        unset( $sortIds[$index] );
                    }
                }
            }

            $this->dataMap[$identifier]->fromString( implode( '-', $sortIds ) );
            $this->dataMap[$identifier]->store();
        }
    }

    /**
     * @param $identifier
     *
     * @return Osservazione[]
     */
    protected function getOsservazioni( $identifier )
    {
        $result = array();
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $factory = OCEditorialStuffHandler::instance( 'osservazioni' )->getFactory();
            $idArray = explode( '-', $this->dataMap[$identifier]->toString() );
            foreach ( $idArray as $id )
            {
                try
                {
                    $result[] = $factory->instancePost( array( 'object_id' => $id ) );
                }
                catch ( Exception $e )
                {

                }
            }
        }

        return $result;
    }

    /**
     * @return OCEditorialStuffPostInterface[]
     */
    protected function getInvitati()
    {
        $result = array();
        if ( isset( $this->dataMap['invitati'] ) )
        {
            $factory = OCEditorialStuffHandler::instance( 'invitato' )->getFactory();
            $idArray = explode( '-', $this->dataMap['invitati']->toString() );
            foreach ( $idArray as $id )
            {
                try
                {
                    $result[] = $factory->instancePost( array( 'object_id' => $id ) );
                }
                catch ( Exception $e )
                {

                }
            }
        }

        return $result;
    }

    /**
     * @param $identifier
     *
     * @return int
     */
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

    /**
     * @todo occorre usare una factory ad hoc?
     *
     * @param null $attributeIdentifier
     *
     * @return eZContentObject[]|string[]
     */
    protected function getMateria( $attributeIdentifier = null )
    {
        return $this->stringRelatedObjectAttribute( 'materia', $attributeIdentifier );
    }

    /**
     * Restituisce l'attributo $attributeIdentifier degli oggetti correlati all'attributo $identifier
     * Se $attributeIdentifier = null restituisce gli oggetti
     *
     * @param string $identifier
     * @param string $attributeIdentifier
     *
     * @return array|null
     */
    protected function stringRelatedObjectAttribute( $identifier, $attributeIdentifier = null )
    {
        $data = array();
        $ids = explode( '-', $this->stringAttribute( $identifier ) );
        foreach ( $ids as $id )
        {
            if ( is_numeric( $id ) )
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
        }

        return empty( $data ) ? null : $data;
    }

    /**
     * Restituisce il toString dell'attributo $identifier filtrato da $callback (se presente)
     * @param string $identifier
     * @param Callable $callback
     *
     * @return bool|mixed|string
     */
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
     * Checks if the object is visible by App
     * @return bool
     */

    public function isVisibleByApp()
    {
        $notVisibleStates = array('draft');
        if (in_array($this->currentState()->attribute( 'identifier' ), $notVisibleStates))
            return false;
        else
            return true;
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

        // Todo: aggiungere data

        return array(
            'id' => $this->id(),
            'stato' => $this->currentState()->attribute( 'identifier' ),
            'seduta'  => (int)$this->attribute( 'seduta_id' ), //@todo
            'seduta_id'  => (int)$this->attribute( 'seduta_id' ), //@todo
            'numero'  => $this->stringAttribute( 'n_punto', 'intval' ),
            'oggetto' => $this->dataMap['oggetto']->content(),
            'orario'  => $locale->formatShortTime(
                $this->dataMap['orario_trattazione']->content()->attribute( 'timestamp' )
            ),
            'materia' => $this->getMateria( 'name' ),
            'referente_politico' => implode('', (array)$this->stringRelatedObjectAttribute( 'referente_politico', 'titolo' )) . ' ' . implode('', (array)$this->stringRelatedObjectAttribute( 'referente_politico', 'nome' )) . ' ' . implode('', (array)$this->stringRelatedObjectAttribute( 'referente_politico', 'cognome' )),
            'referente_tecnico' => implode('', (array)$this->stringRelatedObjectAttribute( 'referente_tecnico', 'titolo' )) . ' ' . implode('', (array)$this->stringRelatedObjectAttribute( 'referente_tecnico', 'nome' )) . ' ' . implode('', (array)$this->stringRelatedObjectAttribute( 'referente_tecnico', 'cognome' )),
            'documenti' => $this->attribute( 'count_documenti' ),
            'invitati' => $this->attribute( 'count_invitati' ),
            'osservazioni' => $this->attribute( 'count_osservazioni' ),
            'consenti_osservazioni' => $this->dataMap['consenti_osservazioni']->toString(),
            'termine_osservazioni' => strftime(
                '%d/%m/%Y  alle ore %H:%M',
                $this->dataMap['termine_osservazioni']->toString()
            )
        );
    }

    public function start()
    {
        $seduta = $this->getSeduta();
        if ( $seduta instanceof Seduta )
        {
            if ( $seduta->currentState()->attribute( 'identifier' ) == 'in_progress' )
            {
                $puntoInProgress = $seduta->getPuntoInProgress();
                if ( $puntoInProgress == false )
                {
                    $this->setState( 'punto.in_progress' );
                    OpenPAConsiglioPushNotifier::instance()->emit(
                        'start_punto',
                        $this->jsonSerialize()
                    );
                    return true;
                }
                elseif( $puntoInProgress instanceof Punto && $puntoInProgress->id() == $this->id() )
                {
                    return true;
                }
                else
                {
                    throw new Exception( 'Il punto ' . $puntoInProgress->getObject()->attribute( 'name' ) . ' è in corso' );
                }
            }
        }
        throw new Exception( 'Seduta in corso non trovata' );
    }

    public function stop()
    {
        $seduta = $this->getSeduta();
        if ( $seduta instanceof Seduta )
        {
            if ( $this->currentState()->attribute( 'identifier' ) == 'in_progress' )
            {
                $puntoInProgress = $seduta->getPuntoInProgress();
                if ( $puntoInProgress instanceof Punto && $puntoInProgress->id() == $this->id() )
                {
                    $this->setState( 'punto.closed' );
                    OpenPAConsiglioPushNotifier::instance()->emit(
                        'stop_punto',
                        $this->jsonSerialize()
                    );                    
                }
            }
        }
        return true;
        //throw new Exception( "Errore" ); //@todo
    }

    /**
     * @return Votazione[]
     */
    public function votazioni()
    {
        $data = array();
        /** @var eZContentObject $votazioni */
        $votazioni = $this->getObject()->reverseRelatedObjectList(
            false,
            Votazione::puntoClassAttributeId()
        );
        foreach ( $votazioni as $votazione )
        {
            $data[] = new Votazione(
                array( 'object_id' => $votazione->attribute( 'id' ) ),
                OCEditorialStuffHandler::instance( 'votazione' )->getFactory()
            );
        }

        return $data;
    }

}
