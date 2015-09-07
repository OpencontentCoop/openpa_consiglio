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

    public function getResultHandler()
    {
        $currentType = $this->stringAttribute( self::$typeIdentifier );
        if ( !isset( self::$_resultHandlers[$currentType] ) )
        {
            $factoryConfiguration = $this->getFactory()->getConfiguration();
            $availableHandlers = $factoryConfiguration['VotazioneResultHandlers'];

            $handlerClassName = isset( $availableHandlers[$currentType] ) ? $availableHandlers[$currentType] : 'OpenPAConsiglioVotazioneResultHandlerDefault';
            if ( class_exists( $handlerClassName ) )
            {
                $handlerInstance = new $handlerClassName();
                if ( $handlerInstance instanceof OpenPAConsiglioVotazioneResultHandlerInterface )
                {
                    self::$_resultHandlers[$currentType] = $handlerInstance;
                }
            }
            throw new Exception(
                "Non è stato trovato un gestore valido per le votazioni di tipo $currentType"
            );
        }
        return self::$_resultHandlers[$currentType]->setCurrentVotazione( $this );
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        return array_merge( $this->fnAttributes, $attributes );
    }

    private $fnAttributes = array(
        'presenti',
        'votanti',
        'favorevoli',
        'contrari',
        'astenuti'
    );

    public function attribute( $property )
    {
        if ( in_array( $property, $this->fnAttributes ) )
        {
            /** @return string[] */
            return $this->getUsers( $property );
        }

        return parent::attribute( $property );
    }

    protected function getUsers( $type )
    {
        if ( $type == 'presenti' )
            return $this->getResultHandler()->getPresenti();
        elseif ( $type == 'votanti' )
            return $this->getResultHandler()->getVotanti();
        elseif ( $type == 'favorevoli' )
            return $this->getResultHandler()->getFavorevoli();
        elseif ( $type == 'contrari' )
            return $this->getResultHandler()->getContrari();
        elseif ( $type == 'astenuti' )
            return $this->getResultHandler()->getAstenuti();
        else
            return array();
    }

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
    }

    public static function create( Seduta $seduta, Punto $punto = null, $shortText, $text, $type )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new Exception( "Seduta non trovata" );
        }

        if ( trim( $shortText ) == '' || trim( $text ) == '' || trim( $type ) == '' )
        {
            throw new Exception( "Dati insufficienti" );
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
            throw new Exception( "La seduta non è in corso" );
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

            // notifico tutti che la votazione è chiusa
            $fakeSerialized = $this->jsonSerialize();
            $fakeSerialized['stato'] = 'closed';
            OpenPAConsiglioPushNotifier::instance()->emit(
                'stop_votazione',
                $fakeSerialized
            );

            // attendo 5 secondi per concludere le operazioni di voto
            sleep( 5 );

            $this->getResultHandler()->register();

            // chiudo la votazione
            $this->setState( 'stato_votazione.closed' );
            OpenPAConsiglioPushNotifier::instance()->emit(
                'real_stop_votazione',
                $this->jsonSerialize()
            );

        }
        else
        {
            throw new Exception( "La votazione selezionata non è stata ancora aperta" );
        }
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

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id(),
            'short_text' => $this->stringAttribute( self::$shortTextIdentifier ),
            'text' => $this->stringAttribute( self::$textIdentifier ),
            'seduta_id' => $this->stringAttribute( self::$sedutaIdentifier, 'intval' ),
            'punto_id' => $this->stringAttribute( self::$puntoIdentifier, 'intval' ),
            'tipo' => $this->stringAttribute( self::$typeIdentifier ),
            'stato' => $this->currentState()->attribute( 'identifier' ),
            'presenti' => $this->is( 'closed' ) ? $this->stringAttribute( self::$presentiIdentifier, 'intval' ) : null,
            'votanti' => $this->is( 'closed' ) ? $this->stringAttribute( self::$votantiIdentifier, 'intval' ) : null,
            'favorevoli' => $this->is( 'closed' ) ? $this->stringAttribute( self::$favorevoliIdentifier, 'intval' ) : null,
            'contrari' => $this->is( 'closed' ) ? $this->stringAttribute( self::$contrariIdentifier, 'intval' ) : null,
            'astenuti' => $this->is( 'closed' ) ? $this->stringAttribute( self::$astenutiIdentifier, 'intval' ) : null
        );
    }

    public function addVoto( $value, $userId = null )
    {
        if ( $this->currentState()->attribute( 'identifier' ) != 'in_progress' )
        {
            throw new Exception( "La votazione non e' in corso" );
        }
        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
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
            throw new Exception( 'Seduta non trovata' );
        }
        
        if ( !in_array( $userId, $seduta->partecipanti( false ) ) )
        {
            throw new Exception( 'Politico non abilitato a votare in questa seduta' );
        }
        
        //check $userId: se non è un politico viene sollevata eccezione
        try
        {            
            OCEditorialStuffHandler::instance( 'politico' )->fetchByObjectId( $userId );
        }
        catch( Exception $e )
        {
            throw new Exception( 'Politico non trovato' );
        }

        if ( !$this->is( 'in_progress' ) )
        {
            throw new Exception( "La votazione non e' aperta" );
        }
    }

    public static function removeByID( $votazioneId )
    {
        $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $votazioneId );
        if ( $votazione instanceof Votazione && $votazione->isBefore( 'pending', true ) )
        {
            $object = $votazione->getObject();
            eZContentOperationCollection::deleteObject( array( $object->attribute( 'main_node_id' ) ) );
        }
    }

}