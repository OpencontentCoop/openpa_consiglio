<?php

class Punto extends OCEditorialStuffPostNotifiable implements OCEditorialStuffPostFileContainerInterface, OCEditorialStuffPostInputActionInterface, OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;
    use OpenPAConsiglioDiffTrait;

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

    protected $canCreateAreaRoom;
    
    protected $hasAreaRoom;
    
    protected $areaRoomLink;
    
    protected $proposte;

    public function __construct(
        array $data = array(),
        OCEditorialStuffPostFactoryInterface $factory
    ) {
        parent::__construct($data, $factory);
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
        $attributes[] = 'can_add_documenti';
        $attributes[] = 'can_add_osservazioni';
        $attributes[] = 'notification_subscribers';
        $attributes[] = 'votazioni';
        $attributes[] = 'verbale';
        $attributes[] = 'materia';
        $attributes[] = 'data_doc';
        $attributes[] = 'referente_politico';
        $attributes[] = 'referente_tecnico';
        $attributes[] = 'numero';
        $attributes[] = 'can_share';
        $attributes[] = 'is_shared';
        $attributes[] = 'shared_url';
        $attributes[] = 'proposte';
        $attributes[] = 'current_user_is_referente';

        return $attributes;
    }

    public function attribute($property)
    {
        if (( $property == 'seduta' || $property == 'seduta_id' )) {
            /** @return Seduta */
            return $this->getSeduta($property == 'seduta');
        }

        if ($property == 'osservazioni') {
            /** @return Osservazione[] */
            return $this->getOsservazioni('osservazioni');
        }

        if ($property == 'count_osservazioni') {
            /** @return int */
            return $this->getCount('osservazioni');
        }

        if ($property == 'invitati') {
            /** @return OCEditorialStuffPostInterface[] */
            return $this->getInvitati();
        }

        if ($property == 'count_invitati') {
            /** @return int */
            return $this->getCount('invitati');
        }

        if ($property == 'documenti') {
            /** @return Allegato[] */
            return $this->getAllegati('documenti');
        }

        if ($property == 'count_documenti') {
            /** @return int */
            return $this->getCount('documenti');
        }

        if ($property == 'can_add_documenti') {
            /** @return bool */
            return $this->canAddDocumenti();
        }

        if ($property == 'can_add_osservazioni') {
            /** @return bool */
            return $this->canAddOsservazioni();
        }

        if ($property == 'notification_subscribers') {
            /** @return int */
            return $this->notificationSubscribers();
        }

        if ($property == 'votazioni') {
            /** @return Votazione[] */
            return $this->votazioni();
        }

        if ($property == 'verbale') {
            /** @return string */
            return $this->verbale();
        }

        if ($property == 'materia') {            
            $materia = $this->getMateria();
            return $materia instanceof Materia ? $materia->getObject()->attribute('name') : false;            
        }

        if ($property == 'data_doc') {
            /** @return string[] */
            return $this->getDataDoc();
        }

        if ($property == 'referente_politico' || $property == 'referente_tecnico') {
            $referente = $this->stringRelatedObjectAttribute($property, 'name');
            return is_array($referente) ? implode(', ', $referente) : null;
        }

        if ($property == 'numero') {
            return $this->stringAttribute('n_punto', 'intval');
        }

        if ($property == 'can_share') {
            return $this->canShare();
        }

        if ($property == 'is_shared') {
            return $this->isShared();
        }

        if ($property == 'shared_url') {
            return $this->sharedUrl();
        }

        if ($property == 'proposte') {
            return $this->getProposte();
        }

        if ($property == 'current_user_is_referente') {            
            return in_array(eZUser::currentUserID(), $this->getIdsReferenti());
        }

        return parent::attribute($property);
    }

    public function canShare()
    {
        if ($this->canCreateAreaRoom === null) {
            $this->canCreateAreaRoom = false;
            if (eZUser::currentUser()->contentObject()->attribute('class_identifier') == 'politico') {
                try {
                    $this->canCreateAreaRoom = AreaCollaborativaFactory::fetchCountByPolitico(eZUser::currentUser()) > 0;
                } catch (Exception $e) {
                }
            }
        }

        return $this->canCreateAreaRoom;
    }

    public function isShared()
    {
        if ($this->hasAreaRoom === null) {
            $this->hasAreaRoom = false;
            try {
                $aree = AreaCollaborativaFactory::fetchByPolitico(eZUser::currentUser());
                foreach ($aree as $area) {
                    $this->hasAreaRoom = $area->fetchCountRoomsByRelation($this->id()) > 0;
                    break;
                }
            } catch (Exception $e) {
            }
        }

        return $this->hasAreaRoom;
    }

    public function sharedUrl()
    {
        if ($this->areaRoomLink === null) {
            $aree = AreaCollaborativaFactory::fetchByPolitico(eZUser::currentUser());
            foreach ($aree as $area) {
                $rooms = $area->fetchRoomsByRelation($this->id());
                foreach ($rooms as $room) {
                    $this->areaRoomLink = 'consiglio/collaboration/' . $area->getObject()->attribute('id') . '/room-' . $room->attribute('node_id');
                }
            }
        }

        return $this->areaRoomLink;
    }

    public function share()
    {
        if (eZUser::currentUser()->contentObject()->attribute('class_identifier') == 'politico') {
            try {
                $aree = AreaCollaborativaFactory::fetchByPolitico(eZUser::currentUser());
                foreach ($aree as $area) {
                    $helper = new OpenPAConsiglioCollaborationHelper($area);

                    return $helper->addAreaRoom($this->dataMap['oggetto']->content(), $this->id());
                }
            } catch (Exception $e) {
            }
        }

        return false;
    }

    /**
     * calcola la data della prima documentazione allegata in base alla history
     */
    public function getDataDoc()
    {
        $conds = array('handler' => 'history', 'object_id' => $this->id(), 'type' => 'add_file');
        $sort = array('created_time' => 'asc');
        $aLimit = array('offset' => 0, 'length' => 1);
        /** @var OCEditorialStuffHistory[] $firstFileHistory */
        $firstFileHistory = OCEditorialStuffHistory::fetchObjectList(OCEditorialStuffHistory::definition(), null,
            $conds, $sort, $aLimit);
        if (isset( $firstFileHistory[0] ) && $firstFileHistory[0] instanceof OCEditorialStuffHistory) {
            return $firstFileHistory[0]->attribute('created_time');
        }

        return null;
    }

    /**
     * @see Seduta::verbale
     * @return string
     */
    public function verbale()
    {
        return $this->getSeduta()->verbale($this->id());
    }

    /**
     * @see PuntoFactory::fields()
     */
    public function indexOrario()
    {
        $timestamp = 0;
        if (isset( $this->dataMap['orario_trattazione'] )) {
            $orario = $this->dataMap['orario_trattazione']->content();
            if ($orario instanceof eZTime) {
                $timestamp = $orario->attribute('time_of_day');
            }
        }

        return intval($timestamp);
    }

    /**
     * @see Seduta::reorderOdg
     *
     * @param int $number
     */
    public function setNumber($number)
    {
        if (isset( $this->dataMap['n_punto'] )) {
            $this->dataMap['n_punto']->fromString(intval($number));
            $this->dataMap['n_punto']->store();

            $object = $this->getObject();
            /** @var eZContentClass $class */
            $class = $object->attribute('content_class');
            $name = $class->contentObjectName($object);
            $object->setName($name);
            $object->store();

            eZSearch::addObject($object);
            eZContentCacheManager::clearObjectViewCacheIfNeeded(
                $object->attribute('id')
            );
            eZDebug::writeNotice(
                "Set number $number for {$this->id()}",
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
    public function is($stateIdentifier)
    {
        if ($stateIdentifier == '_public') //pseudo stato
        {
            $seduta = $this->getSeduta();
            if ($seduta instanceof Seduta) {
                return $seduta->isAfter('published', true) && $this->is('published');
            }
        }

        return parent::is($stateIdentifier);
    }

    /**
     * Restituisce la Seduta o l'id Seduta di riferimento o null
     *
     * @param bool $asObject
     *
     * @return Seduta|int|null
     */
    public function getSeduta($asObject = true)
    {
        if ($this->seduta === null) {
            if (isset( $this->dataMap['seduta_di_riferimento'] )) {
                $contentArray = explode('-', $this->dataMap['seduta_di_riferimento']->toString());
                $sedutaID = array_pop($contentArray);
                try {
                    if (is_numeric($sedutaID)) {
                        $this->seduta = OCEditorialStuffHandler::instance('seduta')
                                                               ->getFactory()
                                                               ->instancePost(array('object_id' => $sedutaID));
                    }
                } catch (Exception $e) {

                }
            }
        }
        if ($this->seduta instanceof Seduta) {
            if (!$asObject) {
                return $this->seduta->id();
            } else {
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
        $hasAccess = $currentUser->hasAccessTo('consiglio', 'admin');
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
        if ($isAdmin) {
            $tabs[] = array(
                'identifier' => 'inviti',
                'name' => 'Gestione inviti',
                'template_uri' => "design:{$templatePath}/parts/inviti.tpl"
            );
            //            $tabs[] = array(
            //                'identifier' => 'votazioni',
            //                'name' => 'Votazioni',
            //                'template_uri' => "design:{$templatePath}/parts/votazioni.tpl"
            //            );
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
    public function addFile(eZContentObject $object, $attributeIdentifier)
    {
        $result = false;
        if (!$this->getObject()->canRead()){
            return $result;
        }
        
        if ($attributeIdentifier == 'documenti') {
            if (isset( $this->dataMap[$attributeIdentifier] )) {
                $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                $ids[] = $object->attribute('id');
                $ids = array_unique($ids);
                $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                $this->dataMap[$attributeIdentifier]->store();
                
                $seduta = $this->getSeduta();
                if ($seduta instanceof Seduta) {
                    $seduta->assignSection($object);
                }

                $materia = $this->getMateria();
                if ($materia instanceof Materia){
                    $materia->assignState($object);
                }

                eZSearch::addObject($object);
                eZSearch::addObject($this->getObject());
                eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                OCEditorialStuffHistory::addHistoryToObjectId(
                    $this->id(),
                    'add_file',
                    array(
                        'object_id' => $object->attribute('id'),
                        'name' => $object->attribute('name'),
                        'attribute' => $attributeIdentifier
                    )
                );

                try {
                    $allegato = OCEditorialStuffHandler::instance('allegati_seduta')
                                                       ->getFactory()
                                                       ->instancePost(
                                                           array('object_id' => $object->attribute('id'))
                                                       );
                    if ($allegato instanceof OCEditorialStuffPostInterface) {
                        $this->createNotificationEvent('change_allegati', $allegato);
                    }
                } catch (Exception $e) {

                }
                $result = true;
            }
        } elseif ($attributeIdentifier == 'osservazioni') {
            if (isset( $this->dataMap[$attributeIdentifier] )) {
                $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                $ids[] = $object->attribute('id');
                $ids = array_unique($ids);
                $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                $this->dataMap[$attributeIdentifier]->store();
                
                $materia = $this->getMateria();
                if ($materia instanceof Materia){
                    $materia->assignState($object);
                }
                
                eZSearch::addObject($object);
                eZSearch::addObject($this->getObject());
                eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                OCEditorialStuffHistory::addHistoryToObjectId(
                    $this->id(),
                    'add_osservazione',
                    array(
                        'object_id' => $object->attribute('id'),
                        'name' => $object->attribute('name'),
                        'attribute' => $attributeIdentifier
                    )
                );

                try {
                    $osservazione = OCEditorialStuffHandler::instance('osservazioni')
                                                           ->getFactory()
                                                           ->instancePost(
                                                               array('object_id' => $object->attribute('id'))
                                                           );
                    if ($osservazione instanceof OCEditorialStuffPostInterface) {
                        $this->createNotificationEvent('add_osservazione', $osservazione);
                    }
                } catch (Exception $e) {

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
    public function removeFile(eZContentObject $object, $attributeIdentifier)
    {
        if ($attributeIdentifier == 'documenti') {
            if (isset( $this->dataMap[$attributeIdentifier] )) {
                $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                $removeId = $object->attribute('id');
                foreach ($ids as $index => $id) {
                    if ($id == $removeId) {
                        unset( $ids[$index] );
                    }
                }
                $ids = array_unique($ids);
                $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                $this->dataMap[$attributeIdentifier]->store();
                eZSearch::addObject($this->getObject());
                eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                OCEditorialStuffHistory::addHistoryToObjectId(
                    $this->id(),
                    'remove_file',
                    array(
                        'object_id' => $object->attribute('id'),
                        'name' => $object->attribute('name'),
                        'attribute' => $attributeIdentifier
                    )
                );

                //                try
                //                {
                //                    $allegato = OCEditorialStuffHandler::instance( 'allegati_seduta' )
                //                        ->fetchByObjectId( $object->attribute( 'id' ) );
                //                    if ( $allegato instanceof OCEditorialStuffPostInterface )
                //                    {
                //                        $this->createNotificationEvent( 'change_allegati', $allegato );
                //                    }
                //                }
                //                catch ( Exception $e )
                //                {
                //
                //                }
            }

        } elseif ($attributeIdentifier == 'osservazioni') {
            $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
            $removeId = $object->attribute('id');
            foreach ($ids as $index => $id) {
                if ($id == $removeId) {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique($ids);
            $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
            $this->dataMap[$attributeIdentifier]->store();
            eZSearch::addObject($this->getObject());
            eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

            OCEditorialStuffHistory::addHistoryToObjectId(
                $this->id(),
                'remove_osservazione',
                array(
                    'object_id' => $object->attribute('id'),
                    'name' => $object->attribute('name'),
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
    public function fileFactory($attributeIdentifier)
    {
        if ($attributeIdentifier == 'documenti') {
            return OCEditorialStuffHandler::instance('allegati_seduta')->getFactory();
        } elseif ($attributeIdentifier == 'osservazioni') {
            return OCEditorialStuffHandler::instance('osservazioni')->getFactory();
        }
        throw new Exception("FileFactory for $attributeIdentifier not found");
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
    ) {
        $this->setObjectLastModified();

        $this->getSeduta()->reorderOdg();

        if ($afterState->attribute('identifier') == 'published') {            
            $this->createNotificationEvent('publish');
        }
    }

    /**
     * Inserisce il parentObject come seduta di riferimento
     */
    public function onBeforeCreate()
    {
        try {
            $object = $this->getObject();
            /** @var eZContentObjectVersion $version */
            $version = $object->version(1);
            /** @var eZNodeAssignment[] $nodeAssignmentList */
            $nodeAssignmentList = $version->attribute('node_assignments');

            if (isset( $nodeAssignmentList[0] )) {
                $parentNode = eZContentObjectTreeNode::fetch(
                    $nodeAssignmentList[0]->attribute('parent_node')
                );
                $sedutaHandler = OCEditorialStuffHandler::instance('seduta');
                if ($parentNode instanceof eZContentObjectTreeNode
                    && $parentNode->attribute('class_identifier') == $sedutaHandler->getFactory()->classIdentifier()
                ) {
                    /** @var eZContentObjectAttribute[] $dataMap */
                    $dataMap = $object->attribute('data_map');
                    $dataMap['seduta_di_riferimento']->fromString(
                        $parentNode->attribute('contentobject_id')
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
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
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
        eZSearch::addObject($this->getObject(), true);
        $seduta = $this->getSeduta();
        if ($seduta instanceof Seduta) {
            $this->getSeduta()->reorderOdg();
        }
        $this->addUsersToNotifications();
        $proposte = $this->stringAttribute('proposte', function($string){
            return explode('-', $string);
        });
        if (is_array($proposte)){
            try{
                $propostaFactory = OCEditorialStuffHandler::instance('proposta')->getFactory();
                foreach ($proposte as $propostaId) {
                    $proposta = $propostaFactory->instancePost(array('object_id' => $propostaId));
                    if ($proposta instanceof Proposta){
                        $proposta->setState('proposta.online');
                    }
                }
            }catch(Exception $e){
                eZDebug::writeError($e->getMessage(), __METHOD__);
            }
        }

        $materia = $this->getMateria();
        if ($materia instanceof Materia){
            $materia->assignState($this->getObject());
        }
    }

    public function getProposte()
    {
        if ($this->proposte === null){
            $this->proposte = array();
            $proposteIdList = $this->stringAttribute('proposte', function($string){
                return explode('-', $string);
            });
            if (is_array($proposteIdList)){
                try{
                    $propostaFactory = OCEditorialStuffHandler::instance('proposta')->getFactory();
                    foreach ($proposteIdList as $propostaId) {
                        $this->proposte[] = $propostaFactory->instancePost(array('object_id' => $propostaId));                    
                    }
                }catch(Exception $e){
                    eZDebug::writeError($e->getMessage(), __METHOD__);
                }
            }
        }

        return $this->proposte;
    }

    /**
     * @see Seduta::reorderOdg
     * Riordina l'odg della seduta di riferimento
     * notifica l'update se è published e la seduta di riferimento è published
     */
    public function onUpdate()
    {
        $seduta = $this->getSeduta();
        if ($seduta instanceof Seduta) {
            $this->getSeduta()->reorderOdg();
            if ($this->getSeduta()->is('pending')) {
                $lastVersion = $this->getObject()->attribute('current_version') - 1;
                $diff = $this->diff($lastVersion);
                if (isset( $diff['referente_tecnico'] ) || isset( $diff['referente_politico'] )) {
                    $this->addUsersToNotifications();
                    $this->createNotificationEvent('update_referenti');
                }
                if (isset( $diff['termine_osservazioni'] )) {
                    $this->createNotificationEvent('update_termini');
                }
            }
        }

        $materia = $this->getMateria();
        if ($materia instanceof Materia){
            $materia->assignState($this->getObject());
        }
    }

    public function createNotificationEvent($type, OCEditorialStuffPostInterface $refer = null)
    {
        $createNotificationEvent = false;
        if ($this->is('published')) {
            switch ($type) {
                case 'move':
                case 'update_referenti':
                case 'update_termini':
                    $createNotificationEvent = $this->getSeduta()->is('pending');
                    break;

                case 'publish':
                case 'change_allegati':
                case 'add_osservazione':
                    $createNotificationEvent = !$this->getSeduta()->is('draft') && !$this->getSeduta()->is('closed');
                    break;
            }
        }

        if ($createNotificationEvent) {
            return parent::createNotificationEvent($type, $refer);
        }

        return false;
    }

    public function handleDigestItemNotification($event, $notificationType)
    {
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList($notificationType, null, $this->id());
        $alreadySent = array();
        foreach ($subscribersRules as $subscribersRule) {
            $alreadySent[] = $subscribersRule->attribute('user_id');
            $subscribersRuleString = in_array($subscribersRule->attribute('user_id'),
                $this->getIdsReferenti()) ? 'referente' : 'consigliere';
            $this->createNotificationItem($event, $subscribersRule, $subscribersRuleString,
                OpenPAConsiglioNotificationTransport::DIGEST_ITEM_TRANSPORT);
        }

        $utentiAppassionati = $this->getUtentiInteressatiAllaMateria(true);
        if (!empty( $utentiAppassionati )) {
            foreach ($utentiAppassionati as $subscribersRule) {
                if (!in_array($subscribersRule->attribute('user_id'), $alreadySent)) {
                    $this->createNotificationItem($event, $subscribersRule, 'interessato',
                        OpenPAConsiglioNotificationTransport::DIGEST_ITEM_TRANSPORT);
                }
            }
        }
    }

    public function handlePublishNotification($event)
    {
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList('punto/publish', null, $this->id());
        $alreadySent = array();
        foreach ($subscribersRules as $subscribersRule) {
            $subscribersRuleString = in_array($subscribersRule->attribute('user_id'),
                $this->getIdsReferenti()) ? 'referente' : 'consigliere';
            $user = eZUser::fetch($subscribersRule->attribute('user_id'));
            if ($user instanceof eZUser) {
                $alreadySent[] = $user->id();
                $this->createNotificationItem(
                    $event,
                    $subscribersRule,
                    $subscribersRuleString,
                    OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT
                );
            }
        }

        $utentiAppassionati = $this->getUtentiInteressatiAllaMateria(true);
        if (!empty( $utentiAppassionati )) {
            foreach ($utentiAppassionati as $subscribersRule) {
                $user = eZUser::fetch($subscribersRule->attribute('user_id'));
                if ($user instanceof eZUser && !in_array($user->id(), $alreadySent)) {
                    $this->createNotificationItem(
                        $event,
                        $subscribersRule,
                        'interessato',
                        OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT
                    );
                }
            }
        }
    }

    public function handleUpdateReferentiNotification($event)
    {
        $this->handleDigestItemNotification($event, 'punto/update_referenti');
        $this->removeUsersFromNotifications();
        $this->addUsersToNotifications();
    }

    public function handleUpdateTerminiNotification($event)
    {
        $this->handleDigestItemNotification($event, 'punto/update_termini');
    }

    public function handleMoveNotification($event)
    {
        $this->handleDigestItemNotification($event, 'punto/move');
    }

    public function handleChangeAllegatiNotification($event, $refer)
    {
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList('punto/change_allegati', null, $this->id());
        $alreadySent = array();
        foreach ($subscribersRules as $subscribersRule) {
            $subscribersRuleString = in_array($subscribersRule->attribute('user_id'),
                $this->getIdsReferenti()) ? 'referente' : 'consigliere';
            $user = eZUser::fetch($subscribersRule->attribute('user_id'));
            if ($user instanceof eZUser && $refer instanceof OCEditorialStuffPostInterface) {
                $canTool = new OpenPAConsiglioWhoCan($refer->getObject(), 'read', $user);
                if ($canTool->run()) {
                    $alreadySent[] = $user->id();
                    $this->createNotificationItem(
                        $event,
                        $subscribersRule,
                        $subscribersRuleString,
                        OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                        $refer
                    );
                }
            }

        }

        $utentiAppassionati = $this->getUtentiInteressatiAllaMateria(true);
        if (!empty( $utentiAppassionati )) {
            foreach ($utentiAppassionati as $subscribersRule) {
                $user = eZUser::fetch($subscribersRule->attribute('user_id'));
                if ($user instanceof eZUser && $refer instanceof OCEditorialStuffPostInterface
                    && !in_array($user->id(), $alreadySent)
                ) {
                    $canTool = new OpenPAConsiglioWhoCan($refer->getObject(), 'read', $user);
                    if ($canTool->run()) {
                        $this->createNotificationItem(
                            $event,
                            $subscribersRule,
                            'interessato',
                            OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                            $refer
                        );
                    }
                }
            }
        }
    }

    public function handleAddOsservazioneNotification($event, $refer)
    {
        $subscribersRules = OCEditorialStuffNotificationRule::fetchList('punto/add_osservazione', null, $this->id());
        $alreadySent = array();
        foreach ($subscribersRules as $subscribersRule) {
            $subscribersRuleString = in_array($subscribersRule->attribute('user_id'),
                $this->getIdsReferenti()) ? 'referente' : 'consigliere';
            $user = eZUser::fetch($subscribersRule->attribute('user_id'));
            if ($user instanceof eZUser && $refer instanceof OCEditorialStuffPostInterface) {
                $canTool = new OpenPAConsiglioWhoCan($refer->getObject(), 'read', $user);
                if ($canTool->run()) {
                    $alreadySent[] = $user->id();
                    $this->createNotificationItem(
                        $event,
                        $subscribersRule,
                        $subscribersRuleString,
                        OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                        $refer
                    );
                }
            }

        }

        $utentiAppassionati = $this->getUtentiInteressatiAllaMateria(true);
        if (!empty( $utentiAppassionati )) {
            foreach ($utentiAppassionati as $subscribersRule) {
                $user = eZUser::fetch($subscribersRule->attribute('user_id'));
                if ($user instanceof eZUser && $refer instanceof OCEditorialStuffPostInterface
                    && !in_array($user->id(), $alreadySent)
                ) {
                    $canTool = new OpenPAConsiglioWhoCan($refer->getObject(), 'read', $user);
                    if ($canTool->run()) {
                        $this->createNotificationItem(
                            $event,
                            $subscribersRule,
                            'interessato',
                            OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT,
                            $refer
                        );
                    }
                }
            }
        }
    }


    /**
     * @param eZNotificationEvent $event
     * @param OCEditorialStuffNotificationRule $subscribersRule
     * @param $subscribersRuleString
     * @param $type
     * @param $refer
     *
     * @return OpenPAConsiglioNotificationItem
     */
    protected function createNotificationItem(
        eZNotificationEvent $event,
        OCEditorialStuffNotificationRule $subscribersRule,
        $subscribersRuleString,
        $type,
        $refer = null
    ) {

        if ($type == OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT) {
            $now = new DateTime();
            $now->setTime(20, 00);
            $time = $now->getTimestamp();
        } elseif ($type == OpenPAConsiglioNotificationTransport::DIGEST_ITEM_TRANSPORT) {
            $now = new DateTime();
            $now->setTime(20, 00);
            $time = $now->getTimestamp();
            $this->createNotificationItem($event, $subscribersRule, $subscribersRuleString,
                OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT);
        } else {
            $time = time();
        }

        $transport = OpenPAConsiglioNotificationTransport::instance($type);
        $templateName = $transport->notificationTemplateUri($event, $subscribersRuleString);

        $notifiedVersion = $event->attribute(OCEditorialStuffEventType::FIELD_VERSION) - 1;
        $diff = $this->diff($notifiedVersion);

        $variables = array(
            'user' => eZUser::fetch($subscribersRule->attribute('user_id')),
            'punto' => $this,
            'diff' => $diff,
            'refer' => $refer
        );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ($variables as $name => $value) {
            $tpl->setVariable($name, $value);
        }
        $content = $tpl->fetch($templateName);
        $subject = $tpl->variable('subject');

        return $transport->addItem(
            array(
                'object_id' => $this->id(),
                'user_id' => $subscribersRule->attribute('user_id'),
                'created_time' => time(),
                'type' => $type,
                'subject' => $subject,
                'body' => $content,
                'expected_send_time' => $time
            )
        );
    }

    public function executeAction($actionIdentifier, $actionParameters, eZModule $module = null)
    {
        if ($actionIdentifier == 'AddInvitato' && isset( $actionParameters['invitato'] )) {
            $invitatoObject = eZContentObject::fetch($actionParameters['invitato']);
            if ($invitatoObject instanceof eZContentObject) {
                //$ora =  $actionParameters['ora'];
                $this->addInvitato($invitatoObject);
            }
        }

        if ($actionIdentifier == 'RemoveInvitato' && isset( $actionParameters['invitato'] )) {
            $invitatoObject = eZContentObject::fetch($actionParameters['invitato']);
            if ($invitatoObject instanceof eZContentObject) {
                //$ora =  $actionParameters['ora'];
                $this->removeInvitato($invitatoObject);
            }
        }

        if ($actionIdentifier == 'SortAllegati'
            && isset( $actionParameters['identifier'] )
            && isset( $actionParameters['sort_ids'] )
        ) {
            $this->sortAllegati($actionParameters['identifier'], $actionParameters['sort_ids']);
        }

        if ($actionIdentifier == 'RefreshSubscriptions') {
            $this->removeUsersFromNotifications();
            $this->addUsersToNotifications();
        }
    }

    public function moveIn(Seduta $seduta)
    {
        $currentSeduta = $this->getSeduta();

        eZDebug::writeNotice("Salvo alert", __METHOD__);
        $this->dataMap['alert']->fromString(SQLIContentUtils::getRichContent('<p>Il punto è stato spostato dalla ' . $currentSeduta->getObject()->attribute('name') . '</p>'));
        $this->dataMap['alert']->store();

        eZDebug::writeNotice("Aggiorno la seduta", __METHOD__);
        $this->dataMap['seduta_di_riferimento']->fromString($seduta->id());
        $this->dataMap['seduta_di_riferimento']->store();

        eZDebug::writeNotice("Reindex punto", __METHOD__);
        eZSearch::addObject($this->getObject());
        eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

        eZDebug::writeNotice("Muovo punto", __METHOD__);
        $move = eZContentObjectTreeNodeOperations::move($this->getObject()->attribute('main_node_id'),
            $seduta->getObject()->attribute('main_node_id'));

        eZDebug::writeNotice("Creo evento", __METHOD__);
        $this->createNotificationEvent('move');

        eZDebug::writeNotice("Aggiorno seduta {$currentSeduta->id()}", __METHOD__);
        $currentSeduta->reorderOdg();

        eZDebug::writeNotice("Aggiorno seduta {$seduta->id()}", __METHOD__);
        $seduta->reorderOdg();

        eZDebug::writeNotice("Aggiorno inviti", __METHOD__);
        foreach ($this->getInvitati() as $invitato) {
            Invito::move($this->getObject(), $invitato->getObject(), $currentSeduta, $seduta);
        }

        if (!$move) {
            eZDebug::writeError("Spostamento non riuscito", __METHOD__);
        }
    }

    public function notificationSubscribers()
    {
        $data = array();
        foreach (
            $this->getFactory()->notificationEventTypesConfiguration()
            as $identifier => $type
        ) {
            $userIdList = array_unique(
                array_merge(
                    OCEditorialStuffNotificationRule::fetchUserIdList('punto/' . $identifier, $this->id()),
                    $this->getUtentiInteressatiAllaMateria()
                )
            );
            $data[$identifier] = array(
                'name' => $type['name'],
                'user_id_list' => $userIdList
            );
        }

        return $data;
    }

    protected function removeUsersFromNotifications()
    {
        OCEditorialStuffNotificationRule::removeByPostID($this->id());
    }

    protected function addUsersToNotifications()
    {
        $types = $this->getFactory()->availableNotificationEventTypes();

        foreach ($types as $type) {
            $userIds = array();
            switch ($type) {
                case 'publish':
                case 'update_referenti':
                case 'update_termini':
                case 'move':
                case 'add_osservazione':
                case 'change_allegati':
                    $userIds = $this->getIdsReferenti();
                    break;
            }
            if (count($userIds)) {
                $this->createNotificationTypeRule($type, $userIds);
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
    protected function addInvitato(eZContentObject $object)
    {
        try {
            $attributeIdentifier = 'invitati';
            $invitatoFactory = OCEditorialStuffHandler::instance('invitato');
            $invitato = $invitatoFactory->fetchByObjectId($object->attribute('id'));
            if ($invitato instanceof OCEditorialStuffPostInterface) {
                if (isset( $this->dataMap[$attributeIdentifier] )) {
                    // creo invito
                    $invito = Invito::create($this->getObject(), $invitato->getObject());

                    if ($invito instanceof eZContentObject) {

                        // aggiorno attributo del punto
                        $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                        $ids[] = $invitato->id();
                        $ids = array_unique($ids);
                        $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                        $this->dataMap[$attributeIdentifier]->store();
                        eZSearch::addObject($this->getObject());
                        eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                        // aggiorno storia del punto
                        OCEditorialStuffHistory::addHistoryToObjectId(
                            $this->id(),
                            'add_invitato',
                            array(
                                'object_id' => $invitato->id(),
                                'name' => $invitato->getObject()->attribute('name'),
                                'invito_object_id' => $invito->attribute('id')
                            )
                        );
                    }

                    return true;
                }
            }
        } catch (Exception $e) {

        }

        return false;
    }

    protected function removeInvitato(eZContentObject $object)
    {
        try {
            $attributeIdentifier = 'invitati';
            $invitatoFactory = OCEditorialStuffHandler::instance('invitato');
            $invitato = $invitatoFactory->fetchByObjectId($object->attribute('id'));
            $removeId = $object->attribute('id');
            if ($invitato instanceof OCEditorialStuffPostInterface) {
                if (isset( $this->dataMap[$attributeIdentifier] )) {
                    Invito::remove($this->getObject(), $invitato->getObject());

                    // aggiorno attributo del punto
                    $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                    foreach ($ids as $index => $id) {
                        if ($id == $removeId) {
                            unset( $ids[$index] );
                        }
                    }
                    $ids = array_unique($ids);
                    $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                    $this->dataMap[$attributeIdentifier]->store();
                    eZSearch::addObject($this->getObject());
                    eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                    // aggiorno storia del punto
                    OCEditorialStuffHistory::addHistoryToObjectId(
                        $this->id(),
                        'remove_invitato',
                        array(
                            'object_id' => $invitato->id(),
                            'name' => $invitato->getObject()->attribute('name')
                        )
                    );

                    return true;
                }
            }
        } catch (Exception $e) {

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
        foreach ($attributes as $value) {
            if (isset( $this->dataMap[$value] ) && $this->dataMap[$value]->hasContent()) {
                $list = $this->dataMap[$value]->content();
                foreach ($list['relation_list'] as $v) {
                    $result[] = $v['contentobject_id'];
                }
            }
        }

        return array_unique($result);
    }

    /**
     * Restituisce un array con gli id degli utenti appassionati alla materia
     *
     * @param bool $asObjects
     *
     * @return OCEditorialStuffNotificationRule[]
     */
    protected function getUtentiInteressatiAllaMateria($asObjects = false)
    {
        // Prepara notifica per gli interessati alla materia
        /** @var OCEditorialStuffNotificationRule[] $utentiAppassionati */
        $utentiAppassionati = array();        
        $materia = $this->getMateria();
        if ($materia instanceof Materia) {
            $utentiAppassionati = array_merge(
                $utentiAppassionati,
                OCEditorialStuffNotificationRule::fetchList(
                    'materia/like',
                    null,
                    $materia->id()
                )
            );
        }
        $data = array();
        foreach ($utentiAppassionati as $utentiAppassionato) {
            $data[$utentiAppassionato->attribute('user_id')] = $utentiAppassionato;
        }
        if (!$asObjects) {
            return array_keys($data);
        } else {
            return array_values($data);
        }
    }

    /**
     * @return bool
     */
    protected function canAddOsservazioni()
    {
        if ($this->is('published')) {
            if (isset( $this->dataMap['consenti_osservazioni'] ) && isset( $this->dataMap['termine_osservazioni'] )) {
                $now = time();

                return $this->dataMap['consenti_osservazioni']->attribute('data_int') == 1
                       && $now < $this->dataMap['termine_osservazioni']->toString();
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function canAddDocumenti()
    {
        $factory = OCEditorialStuffHandler::instance('allegati_seduta')->getFactory();
        $node = eZContentObjectTreeNode::fetch($factory->creationRepositoryNode());
        $canCreate = false;
        if ($node instanceof eZContentObjectTreeNode){
            $canCreate = $node->canCreate();
        }
        return $this->getObject()->canRead() && $canCreate;
    }

    /**
     * @param $identifier
     *
     * @return Allegato[]
     */
    protected function getAllegati($identifier)
    {
        $result = array();
        if (isset( $this->dataMap[$identifier] )) {
            $factory = OCEditorialStuffHandler::instance('allegati_seduta')->getFactory();
            $idArray = explode('-', $this->dataMap[$identifier]->toString());
            foreach ($idArray as $id) {
                try {
                    $result[] = $factory->instancePost(array('object_id' => $id));
                } catch (Exception $e) {

                }
            }
        }

        return $result;
    }

    /**
     * @todo rifare con meno foreach...
     *
     * @param string $identifier
     * @param array $sortIds
     */
    protected function sortAllegati($identifier, $sortIds)
    {
        if (isset( $this->dataMap[$identifier] )) {
            $idArray = explode('-', $this->dataMap[$identifier]->toString());
            foreach ($idArray as $id) {
                if (!in_array($id, $sortIds)) {
                    $sortIds[] = $id;
                }
            }
            if (count($idArray) != count($sortIds)) {
                foreach ($sortIds as $index => $id) {
                    if (!in_array($id, $idArray)) {
                        unset( $sortIds[$index] );
                    }
                }
            }

            $this->dataMap[$identifier]->fromString(implode('-', $sortIds));
            $this->dataMap[$identifier]->store();
        }
    }

    /**
     * @param $identifier
     *
     * @return Osservazione[]
     */
    protected function getOsservazioni($identifier)
    {
        $result = array();
        if (isset( $this->dataMap[$identifier] )) {
            $factory = OCEditorialStuffHandler::instance('osservazioni')->getFactory();
            $idArray = explode('-', $this->dataMap[$identifier]->toString());
            foreach ($idArray as $id) {
                try {
                    $result[] = $factory->instancePost(array('object_id' => $id));
                } catch (Exception $e) {

                }
            }
        }

        return $result;
    }

    /**
     * @return Invitato[]
     */
    protected function getInvitati()
    {
        $result = array();
        if (isset( $this->dataMap['invitati'] )) {
            $factory = OCEditorialStuffHandler::instance('invitato')->getFactory();
            $idArray = explode('-', $this->dataMap['invitati']->toString());
            foreach ($idArray as $id) {
                try {
                    $result[] = $factory->instancePost(array('object_id' => $id));
                } catch (Exception $e) {

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
    protected function getCount($identifier)
    {
        if (isset( $this->dataMap[$identifier] )) {
            $contentArray = explode('-', $this->dataMap[$identifier]->toString());
            if (isset( $contentArray[0] ) && $contentArray[0] == '') {
                unset( $contentArray[0] );
            }

            return count($contentArray);
        }

        return 0;
    }

    /**
     * @todo occorre usare una factory ad hoc?
     *
     * @param null $attributeIdentifier
     *
     * @return Materia|null
     */
    protected function getMateria()
    {        
        $materiaIdList = $this->stringRelatedObjectAttribute('materia', 'id');
        if (isset($materiaIdList[0])){
            return OCEditorialStuffHandler::instance('materia')->getFactory()->instancePost(array('object_id' => $materiaIdList[0]));
        }

        return null;
    }

    /**
     * Checks if the object is visible by App
     *
     * @return bool
     */

    public function isVisibleByApp()
    {
        $notVisibleStates = array('draft');
        if (in_array($this->currentState()->attribute('identifier'), $notVisibleStates)) {
            return false;
        } else {
            return true;
        }
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
        /** @var eZDateTime $orarioTrattazione */
        $orarioTrattazione = $this->dataMap['orario_trattazione']->content();
        $materia = $this->getMateria();
        $data = array(
            'id' => $this->id(),
            'stato' => $this->currentState()->attribute('identifier'),
            'seduta' => (int)$this->attribute('seduta_id'), //@todo
            'seduta_id' => (int)$this->attribute('seduta_id'), //@todo
            'numero' => $this->stringAttribute('n_punto', 'intval'),
            'oggetto' => $this->dataMap['oggetto']->content(),
            'orario' => $locale->formatShortTime(
                $orarioTrattazione->attribute('timestamp')
            ),
            'materia' => $materia instanceof Materia ? $materia->getObject()->attribute('name') : false,
            'referente_politico' => $this->stringRelatedObjectAttribute('referente_politico', 'name'),
            'referente_tecnico' => $this->stringRelatedObjectAttribute('referente_tecnico', 'name'),
            'documenti' => $this->attribute('count_documenti'),
            'data_doc' => $this->attribute('data_doc'),
            'invitati' => $this->attribute('count_invitati'),
            'osservazioni' => $this->attribute('count_osservazioni'),
            'consenti_osservazioni' => intval($this->dataMap['consenti_osservazioni']->toString()),
            'termine_osservazioni' => $this->dataMap['termine_osservazioni']->hasContent() ? strftime('%d/%m/%Y  alle ore %H:%M',
                $this->dataMap['termine_osservazioni']->toString()) : null,
            'timestamp' => $this->getObject()->attribute('modified'),
            '_timestamp_readable' => date(Seduta::DATE_FORMAT, $this->getObject()->attribute('modified'))
        );
        //        $lastChangeHistory = OCEditorialStuffHistory::getLastHistoryByObjectIdAndType( $this->id(), 'updateobjectstate' );
        //        if ( $lastChangeHistory instanceof OCEditorialStuffHistory )
        //        {
        //            $data['timestamp'] = $lastChangeHistory->attribute( 'created_time' );
        //            $data['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastChangeHistory->attribute( 'created_time' ) );
        //        }
        return $data;
    }

    public function start()
    {
        $seduta = $this->getSeduta();
        if ($seduta instanceof Seduta) {
            if ($seduta->currentState()->attribute('identifier') == 'in_progress') {
                $puntoInProgress = $seduta->getPuntoInProgress();
                if ($puntoInProgress == false) {
                    $this->setState('punto.in_progress');
                    OpenPAConsiglioPushNotifier::instance()->emit(
                        'start_punto',
                        $this->jsonSerialize()
                    );

                    return true;
                } elseif ($puntoInProgress instanceof Punto && $puntoInProgress->id() == $this->id()) {
                    return true;
                } else {
                    throw new ConsiglioApiException('Impossibile aprire il punto richiesto perché il ' . $puntoInProgress->getObject()->attribute('name') . ' è in corso',
                        ConsiglioApiException::PUNTO_ALREADY_OPEN);
                }
            }
        }
        throw new ConsiglioApiException('Seduta in corso non trovata', ConsiglioApiException::NOT_FOUND);
    }

    public function stop()
    {
        $seduta = $this->getSeduta();
        if ($seduta instanceof Seduta) {
            if ($this->currentState()->attribute('identifier') == 'in_progress') {
                $puntoInProgress = $seduta->getPuntoInProgress();
                if ($puntoInProgress instanceof Punto && $puntoInProgress->id() == $this->id()) {
                    $this->setState('punto.closed');
                    OpenPAConsiglioPushNotifier::instance()->emit(
                        'stop_punto',
                        $this->jsonSerialize()
                    );
                }
            }
        }

        return true;
    }

    /**
     * @return Votazione[]
     */
    public function votazioni()
    {
        $data = array();
        /** @var eZContentObject[] $votazioni */
        $votazioni = $this->getObject()->reverseRelatedObjectList(
            false,
            Votazione::puntoClassAttributeId()
        );
        foreach ($votazioni as $votazione) {
            $data[] = new Votazione(
                array('object_id' => $votazione->attribute('id')),
                OCEditorialStuffHandler::instance('votazione')->getFactory()
            );
        }

        return $data;
    }

}
