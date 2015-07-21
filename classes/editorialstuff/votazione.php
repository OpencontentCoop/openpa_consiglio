<?php

class Votazione extends OCEditorialStuffPost
{
    protected static $classIdentifier = 'votazione';

    protected static $sedutaIdentifier = 'seduta';
    protected static $puntoIdentifier = 'punto';
    protected static $shortTextIdentifier = 'short_text';
    protected static $textIdentifier = 'text';
    protected static $typeIdentifier = 'type';

    protected static $startDateIdentifier = 'start_datetime';
    protected static $endDateIdentifier = 'end_datetime';

    protected static $presentiIdentifier = 'presenti';
    protected static $votantiIdentifier = 'votanti';
    protected static $astenutiIdentifier = 'astenuti';
    protected static $favorevoliIdentifier = 'favorevoli';
    protected static $contrariIdentifier = 'contrari';

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
        $this->setState( 'stato_votazione.in_progress' );
        OpenPAConsiglioPushNotifier::instance()->emit( 'start_votazione', $this->jsonSerialize() );

        $now = time();
        $this->dataMap[self::$startDateIdentifier]->fromString( $now );
        $this->dataMap[self::$startDateIdentifier]->store();

        $registro = $this->getSeduta()->registroPresenze();
        $this->dataMap['presenti']->fromString( $registro['in'] );
        $this->dataMap['presenti']->store();
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

            // chiudo la votazione
            $this->setState( 'stato_votazione.closed' );

            // registro le statistiche
            $votanti = OpenPAConsiglioVoto::countVotanti( $this );
            $contrari = OpenPAConsiglioVoto::countContrari( $this );
            $favorevoli = OpenPAConsiglioVoto::countFavorevoli( $this );
            $astenuti = OpenPAConsiglioVoto::countAstenuti( $this );

            $this->dataMap[self::$votantiIdentifier]->fromString( $votanti );
            $this->dataMap[self::$votantiIdentifier]->store();

            $this->dataMap[self::$contrariIdentifier]->fromString( $contrari );
            $this->dataMap[self::$contrariIdentifier]->store();

            $this->dataMap[self::$favorevoliIdentifier]->fromString( $favorevoli );
            $this->dataMap[self::$favorevoliIdentifier]->store();

            $this->dataMap[self::$astenutiIdentifier]->fromString( $astenuti );
            $this->dataMap[self::$astenutiIdentifier]->store();

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
            'stato' => $this->currentState()->attribute( 'identifier' )
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
        $this->checkAccess( $userId );
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

    public function checkAccess( $userId )
    {
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
}