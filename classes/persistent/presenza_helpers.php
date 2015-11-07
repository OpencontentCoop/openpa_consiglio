<?php

class OpenPAConsiglioPresenzaHelper
{
    /**
     * @vat OpenPAConsiglioPresenza[]
     */
    protected $presenze = array();

    protected $seduta;

    protected $startTime;

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
     * @param Seduta $seduta
     * @param int $startTime
     * @param int $userId
     */
    public function __construct( Seduta $seduta, $startTime = null, $userId = null )
    {
        $this->data = array();

        $this->seduta = $seduta;

        $this->partecipantiIds = $this->seduta->partecipanti( false );

        $this->startTime = $startTime;

        $this->userId = $userId;

        $this->presenze = OpenPAConsiglioPresenza::fetchBySeduta(
            $this->seduta,
            $this->startTime,
            null,
            null,
            $this->userId
        );

        foreach ( $this->partecipantiIds as $userId )
        {
            $this->data[$userId] = $this->getEventsAndIntervals( $userId );
        }
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
            'out_percent' => 0
        );
    }

    protected function getEventsAndIntervals( $userId )
    {
        if ( !$this->seduta->is( 'closed' ) )
            return $this->emptyValue();

        /** @var OpenPAConsiglioPresenza[] $userDetections */
        $userDetections = array();
        $events = array();
        $totalTime = null;
        $inPercent = array();
        $outPercent = array();

        $timeStampInizioSeduta = $this->seduta->dataOraEffettivaInizio();
        $timeStampFineSeduta = $this->seduta->dataOraFine();
        $totalTime = $timeStampFineSeduta - $timeStampInizioSeduta;
        $isIn = false;

        $events[] = array(
            'type' => 'event',
            'timestamp' => $timeStampInizioSeduta,
            'name' => "Inizio seduta"
        );

        $intervals = array();
        $startInterval = $timeStampInizioSeduta;
        foreach ( $this->presenze as $detection )
        {
            if ( $detection->attribute( 'user_id' ) == $userId )
            {
                $userDetections[] = $detection;

                if ( $detection->attribute( 'created_time' ) > $startInterval )
                {
                    $intervals[] = array(
                        $startInterval,
                        $detection->attribute( 'created_time' )
                    );
                    $startInterval = $detection->attribute( 'created_time' );
                }
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
            $duration = $endInterval - $startInterval;
            $percent = $this->calculatePercent( $duration, $totalTime );

            $newInterval = array(
                'type' => 'interval',
                'duration' => $duration,
                'is_in' => $isIn,
                'percent' => $percent
            );

            if ( $newInterval['is_in'] )
                $inPercent[] = $newInterval['percent'];
            else
                $outPercent[] = $newInterval['percent'];

            $events[] = $newInterval;

            $tempDetections = array();
            foreach ( $userDetections as $detection )
            {
                if ( $detection->attribute( 'created_time' ) > $startInterval && $detection->attribute( 'created_time' ) <= $endInterval )
                {
                    if ( !isset( $tempDetections[$detection->attribute( 'created_time' )] ) )
                        $tempDetections[$detection->attribute( 'created_time' )] = array();

                    $tempDetections[$detection->attribute( 'created_time' )][] = $detection;
                    $isIn = $detection->attribute( 'is_in' );
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

        $events[] = array(
            'type' => 'event',
            'timestamp' => $timeStampFineSeduta,
            'name' => "Fine seduta"
        );

        $inPercentSum = array_sum( $inPercent );
        $outPercentSum = array_sum( $outPercent );

        return array(
            'detections' => $userDetections,
            'events' => $events,
            'time' => $totalTime,
            'in_percent' => number_format( $inPercentSum, 2 ),
            'out_percent' => number_format( $outPercentSum, 2 )
        );
    }

    protected function calculatePercent( $duration, $totalTime )
    {
        $percent = $duration * 100 / $totalTime;
        return number_format( $percent, 2 );
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