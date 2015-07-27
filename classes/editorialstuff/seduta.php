<?php

class Seduta extends OCEditorialStuffPost implements OCEditorialStuffPostFileContainerInterface, OCEditorialStuffPostInputActionInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $partecipanti;

    public function __construct(
        array $data = array(),
        OCEditorialStuffPostFactoryInterface $factory
    )
    {
        parent::__construct( $data, $factory );
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'data_ora';
        $attributes[] = 'data_ora_fine';
        $attributes[] = 'referenti';
        $attributes[] = 'odg';
        $attributes[] = 'count_documenti';
        $attributes[] = 'documenti';
        $attributes[] = 'presenze';
        $attributes[] = 'partecipanti';
        $attributes[] = 'registro_presenze';
        $attributes[] = 'votazioni';
        $attributes[] = 'verbale';
        $attributes[] = 'protocollo';

        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'data_ora' )
        {
            return $this->dataOra();
        }

        if ( $property == 'data_ora_fine' )
        {
            return $this->dataOraFine();
        }

        if ( $property == 'referenti' )
        {
            return $this->referenti();
        }

        if ( $property == 'odg' )
        {
            return $this->odg();
        }

        if ( $property == 'count_documenti' )
        {
            return $this->getCount( 'documenti' );
        }

        if ( $property == 'documenti' )
        {
            return $this->getAllegati( 'documenti' );
        }

        if ( $property == 'presenze' )
        {
            return $this->presenze();
        }

        if ( $property == 'partecipanti' )
        {
            return $this->partecipanti();
        }

        if ( $property == 'registro_presenze' )
        {
            return $this->registroPresenze();
        }

        if ( $property == 'votazioni' )
        {
            return $this->votazioni();
        }

        if ( $property == 'verbale' )
        {
            return $this->verbale();
        }

        if ( $property == 'protocollo' )
        {
            return $this->protocollo();
        }

        return parent::attribute( $property );
    }

    protected function createUpdateConvocazione()
    {
        return ConvocazioneSeduta::create( $this->getObject() );
    }

    public function protocollo()
    {
        return $this->stringAttribute(  'protocollo', 'intval'  );
    }

    public function verbale( $postId = null )
    {
        if ( $postId == null )
        {
            $postId = $this->id();
        }
        $verbali = array();
        $data = $this->stringAttribute( 'verbale' );
        if ( empty( $data ) )
        {
            $hash = array( $this->id() => '' );
            foreach( $this->odg() as $punto )
            {
                $hash[$punto->id()] = '';
            }
            $this->saveVerbale( $hash );
        }
        $rows = explode( '&', $data );
        foreach( $rows as $row )
        {
            $columns = explode( '|', $row );
            $verbali[$columns[0]] = $columns[1];
        }
        return $verbali[$postId];
    }

    public function saveVerbale( $hash )
    {
        $data = array();
        foreach( $hash as $id => $text )
        {
            $data[] = $id . '|' . $text;
        }
        $string = implode( '&', $data );
        if ( isset( $this->dataMap['verbale'] ) )
        {
            $this->dataMap['verbale']->fromString( $string );
            $this->dataMap['verbale']->store();
        }
        else
        {
            throw new Exception( "Attributo verbale non trovato" );
        }
    }

    public function reorderOdg()
    {
        foreach ( $this->odg() as $index => $punto )
        {
            $number = $index + 1;
            $punto->setNumber( $number );
        }

        $this->createUpdateConvocazione();
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

        $tabs[] = array(
            'identifier' => 'presenze',
            'name' => 'Presenze',
            'template_uri' => "design:{$templatePath}/parts/presenze.tpl"
        );

        $tabs[] = array(
            'identifier' => 'votazioni',
            'name' => 'Votazioni e esito',
            'template_uri' => "design:{$templatePath}/parts/votazioni.tpl"
        );

        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );

        return $tabs;
    }

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
                $result = true;
            }
        }

        return $result;
    }

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
    }

    public function fileFactory( $attributeIdentifier )
    {
        if ( $attributeIdentifier == 'documenti' )
        {
            return OCEditorialStuffHandler::instance( 'allegati_seduta' )->getFactory();
        }
        throw new Exception( "FileFactory for $attributeIdentifier not found" );
    }

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        if ( $beforeState->attribute( 'identifier' ) == 'pending'
             && $afterState->attribute( 'identifier' ) == 'published'
        )
        {
            foreach ( $this->odg() as $punto )
            {
                if ( $punto->is( '_public' ) )
                {
                    $punto->createNotificationEvent( 'publish' );
                }
            }
        }

        if ( $beforeState->attribute( 'identifier' ) == 'published'
             && $afterState->attribute( 'identifier' ) == 'in_progress'
        )
        {
            OpenPAConsiglioPushNotifier::instance()->emit(
                'start_seduta',
                $this->jsonSerialize()
            );
        }

        if ( $beforeState->attribute( 'identifier' ) == 'in_progress'
             && $afterState->attribute( 'identifier' ) == 'closed'
        )
        {
            if ( isset( $this->dataMap['orario_conclusione_effettivo'] ) )
            {
                $now = time();
                $this->dataMap['orario_conclusione_effettivo']->fromString( $now );
                $this->dataMap['orario_conclusione_effettivo']->store();
            }
            OpenPAConsiglioPushNotifier::instance()->emit(
                'stop_seduta',
                $this->jsonSerialize()
            );
        }
    }

    /**
     * @param string $returnFormat
     *
     * @return DateTime|string
     */
    public function dataOra( $returnFormat = 'U' )
    {
        /** @var eZDate $data */
        $data = $this->dataMap['data']->content();
        /** @var eZTime $ora */
        $ora = $this->dataMap['orario']->content();

        $dateTime = new DateTime();
        $dateTime->setTimestamp( $data->attribute( 'timestamp' ) );
        $dateTime->setTime( $ora->attribute( 'hour' ), $ora->attribute( 'minute' ) );

        if ( $returnFormat )
        {
            return $dateTime->format( $returnFormat );
        }

        return $dateTime;
    }

    /**
     * @param string $returnFormat
     *
     * @return DateTime|string
     */
    public function dataOraFine( $returnFormat = 'U' )
    {
        if ( $this->is( 'closed' ) && isset( $this->dataMap['orario_conclusione_effettivo'] ) )
        {
            /** @var eZDate $data */
            $data = $this->dataMap['orario_conclusione_effettivo']->content();

            $dateTime = new DateTime();
            $dateTime->setTimestamp( $data->attribute( 'timestamp' ) );
        }
        else
        {
            /** @var eZDate $data */
            $data = $this->dataMap['data']->content();
            /** @var eZTime $ora */
            $ora = $this->dataMap['orario_conclusione']->content();

            $dateTime = new DateTime();
            $dateTime->setTimestamp( $data->attribute( 'timestamp' ) );
            $dateTime->setTime( $ora->attribute( 'hour' ), $ora->attribute( 'minute' ) );
        }

        if ( $returnFormat )
        {
            return $dateTime->format( $returnFormat );
        }

        return $dateTime;
    }

    public function referenti()
    {
        //@todo
        return array();
    }

    /**
     * @return Punto[]
     */
    public function odg()
    {
        $sedutaId = $this->object->attribute( 'id' );
        $items = OCEditorialStuffHandler::instance( 'punto', array( 'seduta' => $sedutaId ) )
            ->fetchItems(
                array(
                    'limit' => 100,
                    'offset' => 0,
                    'filters' => 'submeta_seduta_di_riferimento___id_si:' . $this->id(),
                    'sort' => array( 'extra_orario_i' => 'asc' )
                )
            );

        //eZDebug::writeNotice( var_export( OCEditorialStuffHandler::getLastFetchData(), 1 ), __METHOD__ );
        return $items;
    }

    public function odgSerialized()
    {
        $rows = array();
        $items = $this->odg();
        foreach ( $items as $v )
        {
            /** @var eZContentObjectAttribute[] $tempDataMap */
            $tempDataMap = $v->getObject()->dataMap();
            $rows[$tempDataMap['n_punto']->content()] = $v->jsonSerialize();
        }
        return $rows;
    }


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
                    $result[] = new Allegato( array( 'object_id' => $id ), $factory );
                }
                catch ( Exception $e )
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

    protected function stringRelatedObjectAttribute( $identifier, $attributeIdentifier = null )
    {
        $data = array();
        $ids = explode( '-', $this->stringAttribute( $identifier ) );
        foreach ( $ids as $id )
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

    /**
     *
     *   Seduta
     *   id             integer    id univoco Seduta
     *   data           string     data della seduta in formato 'Y-m-d H:i:s'
     *   protocollo     integer    numero di protocollo
     *   stato          string     identificatre stato draft|pending|published
     *   documenti      integer    numero dei documenti allegati
     *
     * @see ConsiglioApiController
     * @return array
     */
    public function jsonSerialize()
    {
        if ( isset( $this->dataMap['data'] ) && $this->dataMap['data']->hasContent()
             && isset( $this->dataMap['orario'] )
             && $this->dataMap['orario']->hasContent()
        )
        {
            try
            {
                $competenza = $this->stringRelatedObjectAttribute( 'organo', 'name' );

                return array(
                    'id' => $this->id(),
                    'competenza' => isset( $competenza[0] ) ? $competenza[0] : null,
                    'data_svolgimento' => $this->dataOra( self::DATE_FORMAT ),
                    'protocollo' => $this->protocollo(),
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

    public function presenze( $startTime = null, $inOut = null, $type = null, $userId = null )
    {
        return OpenPAConsiglioPresenza::fetchBySeduta( $this, $startTime, $inOut, $type, $userId );
    }

    public function partecipanti()
    {
        if ( $this->partecipanti === null )
        {
            $organoNodeId = $this->stringRelatedObjectAttribute( 'organo', 'main_node_id' );
            if ( is_array( $organoNodeId ) && is_numeric( $organoNodeId[0] ) )
            {
                eZDebug::writeNotice( '...' );
                $this->partecipanti = OCEditorialStuffHandler::instance( 'politico' )->fetchItems(
                    array(
                        'filters' => array( 'meta_path_si:' . $organoNodeId[0] ),
                        'limit' => 100,
                        'offset' => 0
                    )
                );
            }
        }

        return $this->partecipanti;
    }

    public function registroPresenze()
    {
        $data = array(
            'total' => 0,
            'in' => 0,
            'out' => 0,
            'hash_user_id' => array()
        );
        $partecipanti = $this->partecipanti();
        $data['total'] = count( $partecipanti );
        foreach ( $partecipanti as $partecipante )
        {
            //@todo gestire anomalie
            $presente = OpenPAConsiglioPresenza::getUserInOutInSeduta( $this, $partecipante->id() );
            $data['hash_user_id'][$partecipante->id()] = $presente;
            if ( $presente )
            {
                $data['in']++;
            }
            else
            {
                $data['out']++;
            }
        }

        return $data;
    }

    public function addPresenza( $inOut, $type = 'manual', $userId = null )
    {
        if ( $inOut === null )
        {
            throw new Exception( "Parametro in_out non trovato" );
        }

        if ( $type === null )
        {
            throw new Exception( "Parametro type non trovato" );
        }

        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
        }

        $this->checkAccess( $userId );
        $presenza = OpenPAConsiglioPresenza::create( $this, $inOut, $type, $userId );
        $presenza->store();
        OpenPAConsiglioPushNotifier::instance()->emit(
            'presenze',
            $presenza->jsonSerialize()
        );

        return $presenza;
    }

    public function checkAccess( $userId )
    {
        //check $userId: se non è un politico viene sollevata eccezione
        try
        {
            OCEditorialStuffHandler::instance( 'politico' )->fetchByObjectId( $userId );
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Politico non trovato' );
        }

        //check data ora seduta
        $dataOra = $this->dataOra( false );
        if ( !$dataOra instanceof DateTime )
        {
            throw new Exception( 'Errore nella definizione del valore data-ora della seduta' );
        }
        $now = new DateTime();
        if ( $dataOra->diff( $now )->days > 1 )
        {
            throw new Exception( 'Seduta svolta in data ' . $dataOra->format( self::DATE_FORMAT ) );
        }

        //check valid in progress Seduta
        if ( !$this->is( 'in_progress' ) )
        {
            throw new Exception( 'Seduta non in corso' );
        }
    }

    public function getPuntoInProgress()
    {
        if ( $this->currentState()->attribute( 'identifier' ) == 'in_progress' )
        {
            foreach ( $this->odg() as $punto )
            {
                if ( $punto->currentState()->attribute( 'identifier' ) == 'in_progress' )
                {
                    return $punto;
                }
            }
        }

        return false;
    }

    public function start()
    {
        $this->setState( 'seduta.in_progress' );
    }

    public function stop()
    {
        $this->setState( 'seduta.closed' );
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
            Votazione::sedutaClassAttributeId()
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

    public function onCreate()
    {
        $this->createUpdateConvocazione();
    }

    public function onUpdate()
    {
        $this->createUpdateConvocazione();
    }

    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null )
    {
        if ( $actionIdentifier == 'GetConvocazione' )
        {
            $convocazione = ConvocazioneSeduta::get( $this->getObject() );
            $downloadUrl = 'editorialstuff/download/convocazione_seduta/' . $convocazione->attribute( 'id' ) . '?' . http_build_query( $actionParameters );
            $module->redirectTo( $downloadUrl );
        }
        elseif ( $actionIdentifier == 'GetAttestatoPresenza' )
        {
            $politico = eZContentObject::fetch( $actionParameters['presente'] );
            if ( $politico instanceof eZContentObject )
            {
                $tpl = eZTemplate::factory();
                $tpl->resetVariables();
                $tpl->setVariable( 'line_height', '1.2' );
                $tpl->setVariable( 'seduta', $this );
                $tpl->setVariable( 'politico', $politico );
                $competenza = $this->stringRelatedObjectAttribute( 'organo', 'name' );

                $tpl->setVariable( 'organo', $competenza );

                if ( isset( $this->dataMap['firmatario'] ) && $this->dataMap['firmatario']->hasContent() )
                {
                    $listFirmatario = $this->dataMap['firmatario']->content();
                    if ( isset( $listFirmatario['relation_list'][0]['contentobject_id'] ) )
                    {
                        $firmatario = eZContentObject::fetch(
                            $listFirmatario['relation_list'][0]['contentobject_id']
                        );
                        /** @var eZContentObjectAttribute[] $firmatarioDataMap */
                        $firmatarioDataMap = $firmatario->dataMap();

                        $tpl->setVariable( 'firmatario', $firmatario->attribute( 'name' ) );
                        if ( $firmatarioDataMap['firma']->hasContent()
                             && $firmatarioDataMap['firma']->attribute( 'data_type_string' ) == 'ezimage' )
                        {
                            $image = $firmatarioDataMap['firma']->content()->attribute( 'original' );
                            $url = $image['url'];
                            eZURI::transformURI( $url, false, 'full' );
                            $tpl->setVariable( 'firma', $url );
                        }
                    }
                }


                $content = $tpl->fetch( 'design:pdf/presenza/presenza.tpl' );

                /** @var eZContentClass $objectClass */
                $objectClass = $this->getObject()->attribute( 'content_class' );
                $languageCode = eZContentObject::defaultLanguage();
                $fileName = $objectClass->urlAliasName( $this->getObject(), false, $languageCode );
                $fileName = eZURLAliasML::convertToAlias( $fileName );
                $fileName .= '.' . $actionParameters['presente'] . '.pdf';

                $parameters = array(
                    'exporter' => 'paradox',
                    'cache' => array(
                        'keys' => array(),
                        'subtree_expiry' => '',
                        'expiry' => -1,
                        'ignore_content_expiry' => false
                    )
                );

                OpenPAConsiglioPdf::create( $fileName, $content, $parameters );

                if ( eZINI::instance()->variable( 'DebugSettings', 'DebugOutput' ) == 'enabled' )
                {
                    echo '<pre>' . htmlentities( $content ) . '</pre>';
                    eZDisplayDebug();
                }
                eZExecution::cleanExit();

            }
        }
    }
}