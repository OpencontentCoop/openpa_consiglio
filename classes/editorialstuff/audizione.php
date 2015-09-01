<?php

class Audizione extends OCEditorialStuffPostNotifiable implements OCEditorialStuffPostFileContainerInterface, OCEditorialStuffPostInputActionInterface
{
    /**
     * @var AudizioneFactory
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
        $attributes[] = 'data_ora';
        $attributes[] = 'osservazioni';
        $attributes[] = 'count_osservazioni';
        $attributes[] = 'documenti';
        $attributes[] = 'count_documenti';
        $attributes[] = 'can_add_osservazioni';
        $attributes[] = 'notification_subscribers';

        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'data_ora' )
        {
            return $this->dataMap['data_trattazione']->content()->attribute( 'timestamp' );
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

        return parent::attribute( $property );
    }

    public function indexFromTime()
    {
        return ezfSolrDocumentFieldBase::preProcessValue(
            $this->attribute( 'data_ora' ),
            'date'
        );
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
            $this->createNotificationEvent( 'publish' );
        }
    }

    /**
     * @see Seduta::reorderOdg
     * Riordina l'odg della seduta di riferimento
     * notifica l'update se è published e la seduta di riferimento è published
     */
    public function onUpdate()
    {
        $this->createNotificationEvent( 'update' );
    }

    public function handlePublishNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
    }

    public function handleUpdateNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
    }

    public function handleAddFileNotification( $event, OCEditorialStuffPostInterface $refer = null )
    {
        // prepara notifica per i referenti
        // prepara notifica per gli invitati
    }

    public function handleUpdateFileNotification(
        $event,
        OCEditorialStuffPostInterface $refer = null
    )
    {
        // prepara notifica per i referenti
        // prepara notifica per gli invitati
    }

    /**
     * @param OCEditorialStuffNotificationRule $subscribersRule
     * @param string $templateName
     *
     * @return OpenPAConsiglioNotificationItem
     */
    protected function createNotificationItem( OCEditorialStuffNotificationRule $subscribersRule, $templateName )
    {
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
                case 'update':
                    $userIds = $this->getIdsReferenti();

                    break;
                case 'add_file':
                    $userIds = $this->getIdsReferenti();

                    break;
                case 'update_file':
                    $userIds = $this->getIdsReferenti();

                    break;
            }
            if ( count( $userIds ) )
            {
                $this->createNotificationTypeRule( $type, $userIds );
            }
        }
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
}