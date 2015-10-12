<?php

class Votazione extends OCEditorialStuffPost
{
    /**
     * @var OpenPAConsiglioVotazioneResultHandlerInterface[]
     */
    private static $_resultHandlers = array();

    public static $classIdentifier = 'votazione';
    public static $sedutaIdentifier = 'seduta';
    public static $puntoIdentifier = 'punto';
    public static $shortTextIdentifier = 'short_text';
    public static $textIdentifier = 'text';
    public static $typeIdentifier = 'type';
    public static $startDateIdentifier = 'start_datetime';
    public static $endDateIdentifier = 'end_datetime';
    public static $presentiIdentifier = 'presenti';
    public static $votantiIdentifier = 'votanti';
    public static $astenutiIdentifier = 'astenuti';
    public static $favorevoliIdentifier = 'favorevoli';
    public static $contrariIdentifier = 'contrari';

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $dataMap;

    public function __construct(
        array $data = array(),
        OCEditorialStuffPostFactoryInterface $factory
    )
    {
        parent::__construct( $data, $factory );
        $this->dataMap = $this->getObject()->attribute( 'data_map' );
    }

    /**
     * @return OpenPAConsiglioVotazioneResultHandlerDefault
     * @throws Exception
     */
    public function getResultHandler()
    {
        $currentType = $this->stringAttribute( self::$typeIdentifier );
        if ( !isset( self::$_resultHandlers[$currentType] ) )
        {
            $factoryConfiguration = $this->getFactory()->getConfiguration();
            $handlersAlias = $factoryConfiguration['VotazioneResultHandlersAlias'];
            if ( isset( $handlersAlias[$currentType] ) )
            {
                $currentType = $handlersAlias[$currentType];
            }
            $availableHandlers = $factoryConfiguration['VotazioneResultHandlers'];

            if ( $currentType == 'default' )
            {
                $handlerClassName = 'OpenPAConsiglioVotazioneResultHandlerDefault';
            }
            else
            {
                $handlerClassName = isset( $availableHandlers[$currentType] ) ? $availableHandlers[$currentType] : null;
            }
            if ( $handlerClassName && class_exists( $handlerClassName ) )
            {
                $handlerInstance = new $handlerClassName();
                if ( $handlerInstance instanceof OpenPAConsiglioVotazioneResultHandlerInterface )
                {
                    self::$_resultHandlers[$currentType] = $handlerInstance;
                }
                else
                {
                    throw new Exception(
                        "$handlerClassName non implementa OpenPAConsiglioVotazioneResultHandlerInterface"
                    );
                }
            }
            else
            {
                throw new Exception(
                    "Non è stato trovato un gestore valido per le votazioni di tipo $currentType"
                );
            }
        }
        return self::$_resultHandlers[$currentType]->setCurrentVotazione( $this );
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'seduta_id';
        $attributes[] = 'result';
        $attributes[] = 'result_template';
        $attributes[] = 'type_description';
        $attributes[] = 'is_valid';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'seduta_id' )
        {
            return $this->getSeduta()->id();
        }
        elseif ( $property == 'is_valid' )
        {
            return $this->getResultHandler()->isValid();
        }
        elseif ( $property == 'result' )
        {
            return $this->getResultHandler();
        }
        elseif ( $property == 'result_template' )
        {
            return $this->getResultHandler()->getTemplateName();
        }
        elseif ( $property == 'type_description' )
        {
            return $this->getResultHandler()->getDescription();
        }

        return parent::attribute( $property );
    }

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        $this->setObjectLastModified();
        //@todo empty cache
    }

    public static function create( Seduta $seduta, Punto $punto = null, $shortText, $text, $type )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new ConsiglioApiException( "Seduta non trovata", ConsiglioApiException::NOT_FOUND );
        }

        if ( trim( $shortText ) == '' || trim( $text ) == '' || trim( $type ) == '' )
        {
            throw new ConsiglioApiException( "Dati insufficienti", ConsiglioApiException::NOT_VALID );
        }

        $votazione = eZContentFunctions::createAndPublishObject( array(
            'class_identifier' => self::$classIdentifier,
            'parent_node_id' => OCEditorialStuffHandler::instance( 'votazione' )->getFactory()->creationRepositoryNode(),
            'attributes' => array(
                self::$sedutaIdentifier => $seduta->id(),
                self::$puntoIdentifier => $punto instanceof Punto ? $punto->id() : null,
                self::$shortTextIdentifier => $shortText,
                self::$textIdentifier => $text,
                self::$typeIdentifier => $type
            )
        ));

        if ( !$votazione instanceof eZContentObject )
        {
            throw new Exception( "Errore creando la votazione" );
        }
    }

    public static function sedutaClassAttributeId()
    {
        $class = eZContentClass::fetchByIdentifier( self::$classIdentifier );
        if ( $class instanceof eZContentClass )
        {
            $attribute = $class->fetchAttributeByIdentifier( self::$sedutaIdentifier );
            if ( $attribute instanceof eZContentClassAttribute )
            {
                return $attribute->attribute( 'id' );
            }
        }
        return null;
    }

    public static function puntoClassAttributeId()
    {
        $class = eZContentClass::fetchByIdentifier( self::$classIdentifier );
        if ( $class instanceof eZContentClass )
        {
            $attribute = $class->fetchAttributeByIdentifier( self::$puntoIdentifier );
            if ( $attribute instanceof eZContentClassAttribute )
            {
                return $attribute->attribute( 'id' );
            }
        }
        return null;
    }

    public function start()
    {
        $seduta = $this->getSeduta();
        if ( $seduta instanceof Seduta && $seduta->is( 'in_progress' ) )
        {
            if ( !$this->getResultHandler()->isValid() )
            {
                throw new ConsiglioApiException( "La votazione non può essere aperta per mancanza del quorum strutturale", ConsiglioApiException::VOTAZIONE_NOT_ALLOWED );
            }
            $this->setState( 'stato_votazione.in_progress' );
            OpenPAConsiglioPushNotifier::instance()->emit(
                'start_votazione',
                $this->jsonSerialize()
            );

            $now = time();
            $this->dataMap[self::$startDateIdentifier]->fromString( $now );
            $this->dataMap[self::$startDateIdentifier]->store();

            $registro = $this->getSeduta()->registroPresenze();
            $this->dataMap[self::$presentiIdentifier]->fromString( $registro['in'] );
            $this->dataMap[self::$presentiIdentifier]->store();
        }
        else
        {
            throw new ConsiglioApiException( "La seduta non è in corso", ConsiglioApiException::SEDUTA_NOT_IN_PROGRESS );
        }
    }

    public function stop()
    {
        if ( $this->currentState()->attribute( 'identifier' ) == 'in_progress' )
        {
            // registro la data di chiusura votazione
            $now = time();
            $this->dataMap[self::$endDateIdentifier]->fromString( $now );
            $this->dataMap[self::$endDateIdentifier]->store();

            //// notifico tutti che la votazione è chiusa
            //$fakeSerialized = $this->jsonSerialize();
            //$fakeSerialized['stato'] = 'closed';
            //OpenPAConsiglioPushNotifier::instance()->emit(
            //    'stop_votazione',
            //    $fakeSerialized
            //);
            //
            //// attendo 5 secondi per concludere le operazioni di voto
            //sleep( 5 );

            $this->getResultHandler()->store();

            // chiudo la votazione
            $this->setState( 'stato_votazione.closed' );
            OpenPAConsiglioPushNotifier::instance()->emit(
                //'real_stop_votazione',
                'stop_votazione',
                $this->jsonSerialize()
            );
        }
        else
        {
            throw new ConsiglioApiException( "La votazione selezionata non è stata ancora aperta", ConsiglioApiException::VOTAZIONE_NOT_OPEN );
        }
    }

    public function stringAttribute( $identifier, $callback = null )
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

    public function jsonSerialize()
    {
        $result = $this->getResultHandler();
        $data = array(
            'id' => $this->id(),
            'short_text' => $this->stringAttribute( self::$shortTextIdentifier ),
            'text' => $this->stringAttribute( self::$textIdentifier ),
            'seduta_id' => $this->stringAttribute( self::$sedutaIdentifier, 'intval' ),
            'punto_id' => $this->stringAttribute( self::$puntoIdentifier, 'intval' ),
            'tipo' => $this->stringAttribute( self::$typeIdentifier ),
            'stato' => $this->currentState()->attribute( 'identifier' ),
            'presenti' => $this->is( 'closed' ) ? $result->attribute( 'presenti_count' ) : null,
            'votanti' => $this->is( 'closed' ) ? $result->attribute( 'votanti_count' ) : null,
            'favorevoli' => $this->is( 'closed' ) ? $result->attribute( 'favorevoli_count' ) : null,
            'contrari' => $this->is( 'closed' ) ? $result->attribute( 'contrari_count' ) : null,
            'astenuti' => $this->is( 'closed' ) ?$result->attribute( 'astenuti_count' ) : null,
            'timestamp' => $this->getObject()->attribute( 'modified' ),
            '_timestamp_readable' => date( Seduta::DATE_FORMAT, $this->getObject()->attribute( 'modified' ) )
        );
//        $lastChangeHistory = OCEditorialStuffHistory::getLastHistoryByObjectIdAndType( $this->id(), 'updateobjectstate' );
//        if ( $lastChangeHistory instanceof OCEditorialStuffHistory )
//        {
//            $data['timestamp'] = $lastChangeHistory->attribute( 'created_time' );
//            $data['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastChangeHistory->attribute( 'created_time' ) );
//        }
        return $data;
    }

    public function addVoto( $value, $userId = null )
    {
        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
        }
        $alreadyExists = OpenPAConsiglioVoto::userAlreadyVoted( $this, $userId, false );
        if ( $this->currentState()->attribute( 'identifier' ) != 'in_progress' )
        {
            $data = array();
            if ( $alreadyExists instanceof OpenPAConsiglioVoto )
            {
                $data = array(
                    'user_voted' => true,
                    'vote_value' => $alreadyExists->attribute( 'value' )
                );
            }
            $data['stato'] = $this->currentState()->attribute( 'identifier' ) ;
            throw new ConsiglioApiException(
                "La votazione non e' in corso",
                ConsiglioApiException::VOTAZIONE_NOT_OPEN,
                null,
                $data
            );
        }

        $seduta = $this->getSeduta();
        $this->checkAccess( $seduta, $userId );        
        $voto = OpenPAConsiglioVoto::create( $seduta, $this, $value, $userId );
        $voto->store();
        OpenPAConsiglioPushNotifier::instance()->emit(
            'voto',
            $voto->jsonSerialize()
        );
        return $voto;
    }

    public function userAlreadyVoted( $userId )
    {
        return OpenPAConsiglioVoto::userAlreadyVoted( $this, $userId );
    }

    /**
     * @return Seduta
     */
    public function getSeduta()
    {
        $sedutaId = $this->stringAttribute( self::$sedutaIdentifier, 'intval' );
        return OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
    }

    public function checkAccess( $seduta, $userId )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new ConsiglioApiException( 'Seduta non trovata', ConsiglioApiException::NOT_FOUND );
        }
        
        if ( !in_array( $userId, $seduta->partecipanti( false ) ) )
        {
            throw new ConsiglioApiException( 'Politico non abilitato a votare in questa seduta', ConsiglioApiException::POLITICO_NOT_ALLOWED );
        }
        
        //check $userId: se non è un politico viene sollevata eccezione
        try
        {            
            OCEditorialStuffHandler::instance( 'politico' )->fetchByObjectId( $userId );
        }
        catch( Exception $e )
        {
            throw new ConsiglioApiException( 'Politico non trovato', ConsiglioApiException::POLITICO_NOT_FOUND );
        }

        if ( !$this->is( 'in_progress' ) )
        {
            throw new ConsiglioApiException( "La votazione non e' aperta", ConsiglioApiException::VOTAZIONE_NOT_OPEN, null, array( 'stato' => $this->currentState()->attribute( 'identifier' ) ) );
        }
    }

    public static function getByID( $votazioneId )
    {
        return OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $votazioneId );
    }

    public static function removeByID( $votazioneId )
    {
        $votazione = self::getByID( $votazioneId );
        if ( $votazione instanceof Votazione && $votazione->isBefore( 'pending', true ) )
        {
            $object = $votazione->getObject();
            eZContentOperationCollection::deleteObject( array( $object->attribute( 'main_node_id' ) ) );
        }
    }

    public function onCreate()
    {
        //@todo empty cache
    }
}