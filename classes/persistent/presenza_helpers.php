<?php

class OpenPAConsiglioPresenzaHelper
{
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

    /**
     * @param Seduta $seduta
     * @param array $customDetections
     * @param int $userId
     */
    public function __construct( Seduta $seduta, $customDetections = null, $userId = null )
    {
        $this->data = array();

        $this->seduta = $seduta;

        $this->partecipantiIds = $this->seduta->partecipanti( false );

        $this->customDetections = $customDetections;

        $this->userId = $userId;

        if ( $this->seduta->is( 'closed' ) )
        {
            $cacheFilePath = self::presenzeCacheFilePath( $this->seduta );
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

            if ( is_array( $this->customDetections ) )
            {
                $userDetections = $this->appendCustomDetections( $this->customDetections, $userDetections );
            }
            $this->data[$userId] = $this->getEventsAndIntervals( $userId, $userDetections );
        }
    }

    public static function presenzeCacheFilePath( Seduta $seduta )
    {
        $cacheFile = $seduta->id() . '.cache';
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
            $data[] = $presenza->jsonSerialize();
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

        $totalTime = $timeStampFineSeduta - $timeStampInizioSeduta;
        $isIn = 0;

        $events[] = array(
            'type' => 'event',
            'timestamp' => $timeStampInizioSeduta,
            'name' => "Inizio seduta"
        );

        $intervals = array();
        $startInterval = $timeStampInizioSeduta;
        foreach ( $userDetections as $detection )
        {
            if ( $detection->attribute( 'created_time' ) > $startInterval )
            {
                $intervals[] = array(
                    $startInterval,
                    $detection->attribute( 'created_time' )
                );
                $startInterval = $detection->attribute( 'created_time' );
            }
        }

        if ( $timeStampFineSeduta > $startInterval )
        {
            $intervals[] = array(
                $startInterval,
                $timeStampFineSeduta
            );
        }

        foreach ( $intervals as $interval )
        {
            list( $startInterval, $endInterval ) = $interval;

            if ( $this->seduta->is( 'closed' ) && $endInterval > $timeStampFineSeduta )
            {
                $endInterval = $timeStampFineSeduta;
            }

            $duration = $endInterval - $startInterval;
            $totalTimeCount[] = $duration;
            $percent = $this->calculatePercent( $duration, $totalTime );

            $newInterval = array(
                'type' => 'interval',
                'duration' => $duration,
                'start' => $startInterval,
                'end' => $endInterval,
                'is_in' => $isIn,
                'percent' => number_format( $percent, 2 )
            );

            if ( $newInterval['is_in'] )
            {
                $inPercent[] = $newInterval['percent'];
            }
            else
            {
                $outPercent[] = $newInterval['percent'];
            }

            $events[] = $newInterval;

            $tempDetections = array();
            foreach ( $userDetections as $detection )
            {
                if ( ( $detection->attribute(
                            'created_time'
                        ) > $startInterval
                       && $detection->attribute( 'created_time' ) <= $endInterval )
                     || ( $detection->attribute(
                            'created_time'
                        ) > $timeStampFineSeduta
                          && $endInterval == $timeStampFineSeduta )
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
                            if ( $detection->attribute( 'type' ) == 'checkin' )
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
            'detections' => $userDetections,
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
    public function __construct( $timestamp, $label, $icon = null )
    {
        $data = array(
            'id' => 'CutstomEvent-' . $timestamp,
            'type' => 'custom',
            'created_time' => $timestamp,
            'label' => $label
        );
        if ( $icon )
            $data['icon'] = $icon;
        parent::__construct( $data );
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
            $percent = $helper->getPercent();

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
                $percent = $data['in_percent'];
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