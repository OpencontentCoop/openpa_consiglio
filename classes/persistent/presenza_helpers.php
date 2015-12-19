<?php

class OpenPAConsiglioPresenzaHelper
{
    public static $countStrategy = 'first_punto';

    /**
     * @vat OpenPAConsiglioPresenza[]|OpenPAConsiglioPresenzaCached[]
     */
    protected $presenze = array();

    protected $seduta;

    /**
     * @var array
     */
    protected $customDetections;

    protected $userId;

    /**
     * @var int[]
     */
    protected $partecipantiIds = array();

    /**
     * @var array[]
     */
    protected $data;

    /**
     * @var array[]
     */
    protected $inIntervalsData;

    protected static $puntiDetections = array();

    /**
     * @param Seduta $seduta
     * @param array $customDetections
     * @param int $userId
     * @param bool|false $useCache
     * @param bool|false $prefixFileCache
     */
    public function __construct( Seduta $seduta, $customDetections = null, $userId = null, $useCache = false, $prefixFileCache = false )
    {
        $this->data = array();

        $this->seduta = $seduta;

        $this->partecipantiIds = $this->seduta->partecipanti( false );

        $this->customDetections = $customDetections;

        $this->userId = $userId;


        if ( $this->seduta->is( 'closed' ) )
            $useCache = true;
        if ( $useCache )
        {
            $cacheFilePath = self::presenzeCacheFilePath( $this->seduta, $prefixFileCache );
            $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
            $presenzeCached = $cacheFile->processCache(
                array( 'OpenPAConsiglioPresenzaHelper', 'presenzeCacheRetrieve' ),
                array( 'OpenPAConsiglioPresenzaHelper', 'presenzeCacheGenerate' ),
                null,
                null,
                $seduta->id()
            );
            foreach( $presenzeCached as $presenzaCached )
            {
                $this->presenze[] = new OpenPAConsiglioPresenzaCached( $presenzaCached );
            }
        }
        else
        {
            $this->presenze = OpenPAConsiglioPresenza::fetchBySeduta(
                $this->seduta,
                null,
                null,
                null,
                $this->userId
            );
        }

        foreach ( $this->partecipantiIds as $userId )
        {
            $userDetections = array();
            foreach ( $this->presenze as $detection )
            {
                if ( $detection->attribute( 'user_id' ) == $userId )
                {
                    $userDetections[] = $detection;
                }
            }
            $puntiDetections = $this->getPuntiDetections( $this->seduta, $useCache );
            //@todo se $puntiDetections è vuoto passare a strategia di voto full?
            $userDetections = $this->appendCustomDetections( $puntiDetections, $userDetections );
            if ( is_array( $this->customDetections ) )
            {
                $userDetections = $this->appendCustomDetections( $this->customDetections, $userDetections );
            }
            $this->data[$userId] = $this->getEventsAndIntervals( $userId, $userDetections );
        }
    }

    public static function presenzeCacheFilePath( Seduta $seduta, $prefixFileCache = false )
    {
        $cacheFile = $prefixFileCache . $seduta->id() . '.cache';
        $cachePath = eZDir::path( array( eZSys::cacheDirectory(), 'openpa_consiglio', 'presenze', $cacheFile ) );
        return $cachePath;
    }

    public static function presenzeCacheGenerate( $file, $sedutaId )
    {
        /** @var Seduta $seduta */
        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
        $presenze = OpenPAConsiglioPresenza::fetchBySeduta( $seduta );
        $data = array();
        foreach( $presenze as $presenza )
        {
            $data[] = $presenza->jsonSerialize(false);
        }
        return array( 'content'  => $data,
                      'scope'    => 'consiglio-presenze-seduta-cache',
                      'datatype' => 'php',
                      'store'    => true );
    }

    public static function presenzeCacheRetrieve( $file, $mtime, $args )
    {
        $Result = include( $file );
        return $Result;
    }

    protected function getPuntiDetections( Seduta $seduta, $useCache )
    {
        if ( !isset( self::$puntiDetections[$seduta->id()] ) )
        {
            if ( $useCache )
            {
                $cacheFilePath = self::presenzeCacheFilePath( $this->seduta, 'punti-' );
                $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
                self::$puntiDetections[$seduta->id()] = $cacheFile->processCache(
                    array( 'OpenPAConsiglioPresenzaHelper', 'puntiDetectionCacheRetrieve' ),
                    array( 'OpenPAConsiglioPresenzaHelper', 'puntiDetectionCacheGenerate' ),
                    null,
                    null,
                    $seduta
                );
            }
            else
            {
                self::$puntiDetections[$seduta->id()] = self::fetchPuntiDetection( $seduta );
            }
        }

        return self::$puntiDetections[$seduta->id()];
    }

    public static function puntiDetectionCacheGenerate( $file, Seduta $seduta )
    {
        return array( 'content'  => self::fetchPuntiDetection( $seduta ),
                      'scope'    => 'consiglio-presenze-seduta-cache',
                      'datatype' => 'php',
                      'store'    => true );
    }

    public static function puntiDetectionCacheRetrieve( $file, $mtime, $args )
    {
        $Result = include( $file );
        return $Result;
    }

    protected static function fetchPuntiDetection( Seduta $seduta )
    {
        $puntiDetections = array();
        $ids = array();
        $labels = array();
        $states = array();
        foreach ( $seduta->odg() as $punto )
        {
            $ids[] = $punto->id();
            $labels[$punto->id()] = $punto->attribute( 'numero' );
            if ( empty( $states ) )
            {
                $states = $punto->getFactory()->states();
            }
        }
        if ( !empty( $ids ) && !empty( $states ) )
        {
            $startBeforeStateId = $states['punto.published']->attribute( 'id' );
            $startAfterStateId = $states['punto.in_progress']->attribute( 'id' );
            $endBeforeStateId = $states['punto.in_progress']->attribute( 'id' );
            $endAfterStateId = $states['punto.closed']->attribute( 'id' );

            /** @var OCEditorialStuffHistory[] $histories */
            $histories = OCEditorialStuffHistory::fetchObjectList(
                OCEditorialStuffHistory::definition(),
                null,
                array( 'handler' => 'history', 'type' => "updateobjectstate", 'object_id' => array( $ids ) ),
                array( 'created_time' => 'asc' )
            );

            foreach ( $histories as $history )
            {
                $params = $history->attribute( 'params' );
                if ( $params['before_state_id'] == $startBeforeStateId && $params['after_state_id'] == $startAfterStateId )
                {
                    $puntiDetections[] = new OpenPAConsiglioCustomDetection(
                        $history->attribute( 'created_time' ),
                        'Apertura punto ' . $labels[$history->attribute( 'object_id' )],
                        'fa-file-o',
                        'start-punto'
                    );
                }
                if ( $params['before_state_id'] == $endBeforeStateId && $params['after_state_id'] == $endAfterStateId )
                {
                    $puntiDetections[] = new OpenPAConsiglioCustomDetection(
                        $history->attribute( 'created_time' ),
                        'Chiusura punto ' . $labels[$history->attribute( 'object_id' )],
                        'fa-file',
                        'end-punto'
                    );
                }
                if ( $params['before_state_id'] == $endAfterStateId && $params['after_state_id'] == $startAfterStateId )
                {
                    $puntiDetections[] = new OpenPAConsiglioCustomDetection(
                        $history->attribute( 'created_time' ),
                        'Riapertura punto ' . $labels[$history->attribute( 'object_id' )],
                        'fa-file-o',
                        'restart-punto'
                    );
                }

            }
        }
        return $puntiDetections;
    }

    protected function appendCustomDetections( $newDetections, $detections )
    {
        foreach ( $newDetections as $newDetection )
        {
            if ( $newDetection instanceof OpenPAConsiglioCustomDetection )
            {
                $detections[] = $newDetection;
            }
            else
            {
                $detections[] = new OpenPAConsiglioCustomDetection(
                    $newDetection,
                    'CustomEvent-' . $newDetection
                );
            }
        }
        usort( $detections, function( $a, $b ){
            return ( $a->attribute( 'created_time' ) < $b->attribute( 'created_time' ) ) ? -1 : 1;
        });
        return $detections;
    }

    public function getPercent()
    {
        if ( $this->userId !== null )
        {
            $data = $this->data[$this->userId]['in_percent'];
        }
        else
        {
            $data = array();
            foreach ( $this->partecipantiIds as $userId )
            {
                $data[$userId] = $this->data[$userId]['in_percent'];
            }
        }

        return $data;
    }

    public function getData()
    {
        if ( $this->userId !== null )
        {
            $data = isset( $this->data[$this->userId] ) ? $this->data[$this->userId] : $this->emptyValue();
        }
        else
        {
            $data = $this->data;
        }

        return $data;
    }

    protected function emptyValue()
    {
        return array(
            'detections' => array(),
            'events' => array(),
            'time' => 0,
            'in_percent' => 0,
            'out_percent' => 0,
            'checkin' => false,
            'checkout' => false
        );
    }

    /**
     * @param int $userId
     * @param OpenPAConsiglioPresenza[]|OpenPAConsiglioPresenzaCached[]|OpenPAConsiglioCustomDetection[] $userDetections
     *
     * @return array
     */
    protected function getEventsAndIntervals( $userId, $userDetections )
    {
        $events = array();
        $totalTime = null;
        $inPercent = array();
        $outPercent = array();
        $totalTimeCount = array();
        $checkin = false;
        $checkout = false;
        $pureDetections = array();

        $inArray = array();
        $outArray = array();

        $timeStampInizioSeduta = $this->seduta->dataOraEffettivaInizio();

        if ( $this->seduta->is( 'in_progress' ) )
        {
            $timeStampFineSeduta = time();
        }
        else
        {
            $timeStampFineSeduta = $this->seduta->dataOraFine();
        }

        $events[] = array(
            'type' => 'event',
            'timestamp' => $timeStampInizioSeduta,
            'name' => "Inizio seduta"
        );

        $startCountTimestamp = $timeStampInizioSeduta;
        $endCountTimestamp = $timeStampFineSeduta;

        $intervals = array();
        $startInterval = $timeStampInizioSeduta;

        if ( self::$countStrategy == 'first_punto' )
            $firstPuntoStarted = false;
        else
            $firstPuntoStarted = true;

        foreach ( $userDetections as $detection )
        {
            if ( $detection instanceof OpenPAConsiglioPresenza || $detection instanceof OpenPAConsiglioPresenzaCached )
            {
                $pureDetections[] = $detection;
            }

            if ( $detection->attribute( 'created_time' ) > $startInterval )
            {
                $intervals[] = array(
                    $startInterval,
                    $detection->attribute( 'created_time' ),
                    $firstPuntoStarted
                );
                $startInterval = $detection->attribute( 'created_time' );

                if ( !$firstPuntoStarted && $detection->attribute( 'type' ) == 'start-punto' )
                {
                    $startCountTimestamp = $detection->attribute( 'created_time' );
                    $firstPuntoStarted = true;
                }

            }
        }

        if ( $endCountTimestamp > $startInterval )
        {
            $intervals[] = array(
                $startInterval,
                $endCountTimestamp,
                true
            );
        }

        $isIn = 0;
        $totalTime = $endCountTimestamp - $startCountTimestamp;

        foreach ( $intervals as $interval )
        {
            list( $startInterval, $endInterval, $doCount ) = $interval;

            if ( $this->seduta->is( 'closed' ) && $endInterval > $endCountTimestamp )
            {
                $endInterval = $endCountTimestamp;
            }

            $duration = $endInterval - $startInterval;
            if ( $doCount )
            {
                $totalTimeCount[] = $duration;
                $percent = $this->calculatePercent( $duration, $totalTime );
            }
            else
            {
                $percent = 0;
            }

            $newInterval = array(
                'type' => 'interval',
                'duration' => $duration,
                'duration_in_minutes' => number_format( $duration/60, 2 ),
                'start' => $startInterval,
                'end' => $endInterval,
                'do_count' => $doCount,
                'is_in' => $isIn,
                'raw_percent' => $percent > 0 ? $percent : 0,
                'percent' => $percent > 0 ? number_format( $percent, 2 ) : 0
            );

            if ( $newInterval['is_in'] == 1 )
            {
                $inPercent[] = $newInterval['raw_percent'];
            }
            elseif( $newInterval['is_in'] == 0 )
            {
                $outPercent[] = $newInterval['raw_percent'];
            }

            $events[] = $newInterval;
            $tempDetections = array();

            foreach ( $userDetections as $detection )
            {
                if ( ( $detection->attribute( 'created_time' ) > $startInterval
                       && $detection->attribute( 'created_time' ) <= $endInterval )
                     || ( $detection->attribute( 'created_time' ) > $endCountTimestamp
                          && $endInterval == $endCountTimestamp )
                )
                {
                    if ( !isset( $tempDetections[$detection->attribute( 'created_time' )] ) )
                    {
                        $tempDetections[$detection->attribute( 'created_time' )] = array();
                    }

                    $tempDetections[$detection->attribute( 'created_time' )][] = $detection;
                    if ( $detection instanceof OpenPAConsiglioPresenza || $detection instanceof OpenPAConsiglioPresenzaCached )
                    {
                        $isIn = intval( $detection->attribute( 'is_in' ) );

                        if ( $detection->attribute( 'is_in' ) == 1 )
                        {
                            if ( $detection->attribute( 'type' ) == 'checkin'
                                 || $detection->attribute( 'type' ) == 'manual' )
                            {
                                $inArray[] = $detection;
                            }
                            $outArray = array();
                        }

                        if ( $detection->attribute( 'is_in' ) == 0
                             && ( $detection->attribute( 'type' ) == 'checkin'
                                  || $detection->attribute( 'type' ) == 'manual' )
                        )
                        {
                            $outArray[] = $detection;
                        }
                    }
                }
            }

            foreach ( $tempDetections as $key => $values )
            {
                $event = array(
                    'type' => 'event',
                    'timestamp' => $key,
                    'name' => array(),
                    'items' => $values
                );
                $events[] = $event;
            }
        }

        $endText = "Fine seduta";

        if ( $this->seduta->is( 'in_progress' ) )
        {
            $endText = "Adesso (fine seduta prevista alle ore " . $this->seduta->dataOraFine(
                    'H:i'
                ) . ")";
        }

        $events[] = array(
            'type' => 'event',
            'timestamp' => $timeStampFineSeduta,
            'name' => $endText
        );

        if ( !empty( $inArray ) )
        {
            $in = array_shift( $inArray );
            if ( $in instanceof OpenPAConsiglioPresenza || $in instanceof OpenPAConsiglioPresenzaCached )
            {
                $checkin = $in->attribute( 'created_time' );
            }
        }

        if ( !empty( $outArray ) )
        {
            $out = array_shift( $outArray );
            if ( $out instanceof OpenPAConsiglioPresenza || $out instanceof OpenPAConsiglioPresenzaCached )
            {
                $checkout = $out->attribute( 'created_time' );
            }
        }

        $inPercentSum = array_sum( $inPercent );
        $outPercentSum = array_sum( $outPercent );

        return array(
            'detections' => $pureDetections,
            'events' => $events,
            'time' => $totalTime,
            'control' => array_sum( $totalTimeCount ),
            'in_percent' => number_format( $inPercentSum, 2 ),
            'out_percent' => number_format( $outPercentSum, 2 ),
            'checkin' => $checkin,
            'checkout' => $checkout
        );
    }

    protected function calculatePercent( $duration, $totalTime )
    {
        $percent = $duration * 100 / $totalTime;

        return $percent;
    }
}

class OpenPAConsiglioCustomDetection extends OpenPATempletizable
{
    public function __construct( $timestamp = null, $label = null, $icon = null, $type = 'custom' )
    {
        $data = array(
            'id' => 'CutstomEvent-' . $timestamp,
            'type' => $type,
            'created_time' => $timestamp,
            'label' => $label,
            '_timestamp_readable' => date( DateTime::ISO8601, $timestamp )
        );
        if ( $icon )
            $data['icon'] = $icon;
        parent::__construct( $data );
    }
    public static function __set_state( $data )
    {
        $new = new static();
        $new->data = $data['data'];
        return $new;
    }
}

class OpenPAConsiglioPresenzaCached extends OpenPATempletizable
{
    public function __construct( $data )
    {
        $data['created_time'] = $data['timestamp'];
        parent::__construct( $data );
    }
}

class OpenPAConsiglioPresenzaArrayAccess implements ArrayAccess
{
    public static $baseGettone = 120;

    /**
     * @var Politico
     */
    protected $politico;

    protected $politicoId;

    protected $functionName;

    /**
     * @var OpenPAConsiglioPresenzaHelper[]
     */
    private static $data = array();

    public static function setData( $id )
    {
        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $id );
        if ( $seduta instanceof Seduta )
        {
            $helper = new OpenPAConsiglioPresenzaHelper( $seduta );
            self::$data[$id] = $helper->getData();
        }
        else
        {
            self::$data[$id] = array();
        }
    }

    public static function getData( $id )
    {
        if ( isset( self::$data[$id] ) )
        {
            return self::$data[$id];
        }
        return null;
    }

    public function __construct( Politico $politico, $functionName = null )
    {
        $this->politico = $politico;
        $this->politicoId = $politico->id();
        $this->functionName = $functionName;
    }

    public function offsetExists( $sedutaId )
    {
        if ( !self::getData( $sedutaId ) )
        {
            self::setData( $sedutaId );
        }
        return self::getData( $sedutaId ) !== null;
    }

    public function offsetGet( $sedutaId )
    {
        $allData = self::getData( $sedutaId );
        $data = ( isset( $allData[$this->politicoId] ) ) ? $allData[$this->politicoId] : null;

        if ( $data )
        {
            if ( $this->functionName == 'percent' )
            {
                return $data['in_percent'];
            }
            elseif ( $this->functionName == 'importo' )
            {
                return $this->calcolaImportoGettone( $data['in_percent'] );
            }
            elseif ( $this->functionName == 'presenze' )
            {
                return $data['detections'];
            }
        }

        return null;
    }

    protected function calcolaImportoGettone( $percent )
    {
        $base = 0;
        if ( $percent > 75 )
        {
            $base = 100;
        }
        elseif ( $percent < 75 && $percent > 25 )
        {
            $base = 50;
        }
        return number_format( ( intval( $base ) * self::$baseGettone/ 100 ), 2 );
    }

    public function offsetSet( $offset, $value ){}

    public function offsetUnset( $offset ){}

    public function attributes(){}

    public function attribute( $name )
    {
        return $this->offsetGet( $name );
    }

    public function hasAttribute( $name )
    {
        return $this->offsetExists( $name );
    }
}