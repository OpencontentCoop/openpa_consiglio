<?php

class OpenPAConsiglioPresenzaHelper
{
    /**
     * @vat OpenPAConsiglioPresenza[] $presenze
     */
    protected $presenze = array();

    protected $seduta;

    protected $startTime;

    protected $userId;

    /**
     * @var OpenPAConsiglioPresenzaTimelineCollection[]
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
        $this->startTime = $startTime;
        $this->userId = $userId;
        $presenze = OpenPAConsiglioPresenza::fetchBySeduta(
            $this->seduta,
            $this->startTime,
            null,
            null,
            $this->userId
        );
        $this->presenze = $presenze;
    }

    /**
     * @return OpenPAConsiglioPresenzaTimelineCollection[]
     */
    public function run()
    {
        /** @var OpenPAConsiglioPresenzaTimelineCollection[] $data */
        $data = array();

        $allUserData = array();
        foreach ( $this->seduta->partecipanti( false ) as $userId )
        {
            /** @var int $userId */
            $allUserData[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId, $this->seduta->id() );
        }

        if ( empty( $this->presenze ) )
        {
            $data = $allUserData;
        }
        else
        {
            foreach ( $this->presenze as $presenza )
            {
                $userId = $presenza->attribute( 'user_id' );
                if ( !isset( $data[$userId] ) )
                {
                    $data[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId, $this->seduta->id() );
                }
                $data[$userId]->add( $presenza );
            }

            foreach ( array_keys( $allUserData ) as $userId )
            {
                if ( !isset( $data[$userId] ) )
                {
                    $data[$userId] = $allUserData[$userId];
                }
            }
        }
        $this->data = array();
        foreach ( $data as $item )
        {
            //$item->appendToArray( $returnValues );
            $this->data[] = $item->get( $this->seduta, $this->startTime );
        }

        return $this->data;
    }

    public function getPercent()
    {
        $data = array();
        $total = $this->seduta->dataOraFine() - $this->seduta->dataOra();
        foreach ( $this->data as $item )
        {
            $currentValue = 0;
            foreach ( $item->values as $value )
            {
                $currentValue += intval( $value->diff );
            }
            $percent = 100 * $currentValue / $total;
            if ( $percent > 100 )
            {
                $data[$item->userId] = 100;
            }
            elseif ( $percent < 0 || $percent == 0 )
            {
                $data[$item->userId] = 0;
            }
            else
            {
                $data[$item->userId] = number_format( $percent, 2 );
            }
        }

        if ( $this->userId !== null )
        {
            return isset( $data[$this->userId] ) ? $data[$this->userId] : 0;
        }

        return $data;
    }

    public function getValues()
    {
        return $this->data;
    }

    public function getEventsAndIntervals()
    {
        $detections = array();
        $events = array();
        $totalTime = null;
        $totalPercent = null;

        if ( $this->userId !== null )
        {
            $politico = OCEditorialStuffHandler::instance( 'politico' )->fetchByObjectId(
                $this->userId
            );
            $values = $this->run();
            $totalPercent = $this->getPercent();

            $current = false;
            foreach ( $values as $value )
            {
                if ( $value->userId == $politico->id() )
                {
                    $current = $value;
                    break;
                }
            }
            if ( $current instanceof OpenPAConsiglioPresenzaTimelineCollection )
            {
                $events[] = array(
                    'type' => 'event',
                    'timestamp' => $this->seduta->dataOra(),
                    'name' => "Inizio seduta"
                );
                $detections = $current->detections->toArray();
                $totalTime = $this->seduta->dataOraFine() - $this->seduta->dataOra();
                $isIn = false;

                $intervals = array();
                $startInterval = $this->seduta->dataOra();
                foreach ( $detections as $detection )
                {
                    if ( $detection->timestamp > $startInterval )
                    {
                        $intervals[] = array(
                            $startInterval,
                            $detection->timestamp
                        );
                        $startInterval = $detection->timestamp;
                    }
                }

                if ( $this->seduta->dataOraFine() > $startInterval )
                {
                    $intervals[] = array(
                        $startInterval,
                        $this->seduta->dataOraFine()
                    );
                }

                foreach ( $intervals as $interval )
                {
                    list( $startInterval, $endInterval ) = $interval;
                    $duration = $endInterval - $startInterval;
                    $percent = $duration * 100 / $totalTime;
                    $percent = $percent > 100 ? 100 : $percent;
                    $percent = $percent < 0 ? 0 : $percent;
                    $events[] = array(
                        'type' => 'interval',
                        'duration' => $duration,
                        'is_in' => $isIn,
                        'percent' => number_format( $percent, 2 )
                    );

                    $tempDetections = array();
                    foreach ( $detections as $detection )
                    {
                        if ( $detection->timestamp > $startInterval && $detection->timestamp <= $endInterval )
                        {
                            if ( !isset( $tempDetections[$detection->timestamp] ) )
                            {
                                $tempDetections[$detection->timestamp] = array();
                            }
                            $tempDetections[$detection->timestamp][] = $detection;
                            $isIn = $detection->is_in;
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
                    'timestamp' => $this->seduta->dataOraFine(),
                    'name' => "Fine seduta"
                );
            }
        }

        return array(
            'detections' => $detections,
            'events' => $events,
            'time' => $totalTime,
            'percent' => $totalPercent
        );
    }
}


class OpenPAConsiglioPresenzaTimelineCollection
{
    private static $instances = array();

    public $start;

    public $startTimeStamp;

    public $end;

    public $endTimeStamp;

    public $duration;

    public $intervals;

    public $userId;

    /**
     * @var OpenPAConsiglioPresenzaTimelineValue
     */
    protected $current;

    /**
     * @var OpenPAConsiglioPresenza
     */
    protected $currentPresenza;

    public $name;

    /**
     * @var OpenPAConsiglioPresenzaTimelineValue[]
     */
    public $values = array();

    /**
     * @var OpenPAConsiglioPresenzaTimelineDetectionCollection
     */
    public $detections;

    public static function instance( $userId, $sedutaId )
    {
        if ( !isset( self::$instances[$sedutaId][$userId] ) )
        {
            self::$instances[$sedutaId][$userId] = new OpenPAConsiglioPresenzaTimelineCollection( $userId, $sedutaId );
        }

        return self::$instances[$sedutaId][$userId];
    }

    protected function __construct( $userId, $sedutaId )
    {
        $this->userId = $userId;
        $this->detections = OpenPAConsiglioPresenzaTimelineDetectionCollection::instance( $userId, $sedutaId );

        $userName = false;
        $object = eZContentObject::fetch( $userId );
        if ( $object instanceof eZContentObject )
        {
            $userName = $object->attribute( 'name' );
        }
        $this->name = $userName;
    }

    public function add( OpenPAConsiglioPresenza $presenza )
    {
        $this->closeValue( $presenza );
        if ( $this->current === null && $presenza->attribute( 'is_in' ) )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current = new OpenPAConsiglioPresenzaTimelineValue();
            $this->current->set( 'from', (int)$createdTime );
        }
        $this->detections->addValue( $presenza );
    }

    protected function closeValue( OpenPAConsiglioPresenza $presenza = null )
    {
        if ( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && $presenza instanceof OpenPAConsiglioPresenza
             && !$presenza->attribute(
                'is_in'
            )
        )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current->set( 'to', (int)$createdTime );
            $this->values[] = $this->current;
            $this->current = null;
        }
        elseif ( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && !$presenza instanceof OpenPAConsiglioPresenza )
        {
            $this->current->set( 'to', time() );
            $this->values[] = $this->current;
            $this->current = null;
        }

        return $this;
    }

    public function get( Seduta $seduta, $startTime = null )
    {
        $this->closeValue();

        $this->startTimeStamp = $seduta->dataOra();
        if ( $this->endTimeStamp === null )
        {
            $this->endTimeStamp = $seduta->dataOraFine() + 60 * 15;
        }

        $timeline = array();
        $currentStart = $this->startTimeStamp;
        foreach ( $this->values as $index => $value )
        {
            $fromTimeStamp = $value->fromTimeStamp;
            $toTimeStamp = $value->toTimeStamp;
            if ( $toTimeStamp > $this->endTimeStamp && !$seduta->is( 'closed' ) )
            {
                $this->endTimeStamp = $toTimeStamp + 60;
            }

            $diff = $fromTimeStamp - $currentStart;
            if ( $diff > 0 )
            {
                $interval = round( $diff / 60 / 15, 2 );
                $timeline[] = array(
                    0,
                    floatval( $interval ),
                    $currentStart . ' - ' . $fromTimeStamp,
                    date( "Y-m-d H:i:s", $currentStart ) . ' - ' . date(
                        "Y-m-d H:i:s",
                        $fromTimeStamp
                    )
                );
                $interval = round( ( $toTimeStamp - $fromTimeStamp ) / 60 / 15, 2 );
                $timeline[] = array(
                    1,
                    floatval( $interval ),
                    $fromTimeStamp . ' - ' . $toTimeStamp,
                    date( "Y-m-d H:i:s", $fromTimeStamp ) . ' - ' . date(
                        "Y-m-d H:i:s",
                        $toTimeStamp
                    )
                );
                $currentStart = $toTimeStamp;
            }
        }
        $this->intervals = $timeline;

        $this->start = date( "Y-m-d H:i:s", $this->startTimeStamp );
        $this->end = date( "Y-m-d H:i:s", $this->endTimeStamp );
        $this->duration = ( $this->endTimeStamp - $this->startTimeStamp ) / 60;

        return $this;
    }
}

class OpenPAConsiglioPresenzaTimelineDetectionCollection
{
    private static $instances = array();

    protected $userId;
    /**
     * @var OpenPAConsiglioPresenzaTimelineValue[]
     */
    public $values = array();

    /**
     * @param $userId
     * @param $sedutaId
     *
     * @return OpenPAConsiglioPresenzaTimelineDetectionCollection
     */
    public static function instance( $userId, $sedutaId )
    {
        if ( !isset( self::$instances[$sedutaId][$userId] ) )
        {
            self::$instances[$sedutaId][$userId] = new OpenPAConsiglioPresenzaTimelineDetectionCollection(
                $userId
            );
        }

        return self::$instances[$sedutaId][$userId];
    }

    protected function __construct( $userId )
    {
        $this->userId = $userId;
    }

    public function addValue( OpenPAConsiglioPresenza $presenza )
    {
        $createdTime = $presenza->attribute( 'created_time' );
        $value = new OpenPAConsiglioPresenzaTimelineDetectionValue();
        $value->timestamp = (int)$createdTime;
        $value->time = date( "Y-m-d H:i:s", $createdTime );
        $value->label = $presenza->attribute( 'type' );
        $value->id = $presenza->attribute( 'id' );
        $value->in_out = $presenza->attribute( 'in_out' );
        $value->is_in = $presenza->attribute( 'is_in' );
        $this->values[] = $value;
    }

    /**
     * @return OpenPAConsiglioPresenzaTimelineDetectionValue[]
     */
    public function toArray()
    {
        return $this->values;
    }
}

class OpenPAConsiglioPresenzaTimelineDetectionValue implements ArrayAccess
{
    public $time;
    public $timestamp;
    public $label;
    public $id;
    public $in_out;
    public $is_in;

    public function offsetExists( $offset )
    {
        return isset( $this->{$offset} );
    }

    public function offsetGet( $offset )
    {
        return $this->{$offset};
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

class OpenPAConsiglioPresenzaTimelineValue
{
    public $from;
    public $to;
    public $fromTimeStamp;
    public $toTimeStamp;
    public $diff;
    //public $label;

    // @todo indagare perchè __set non funziona
    function set( $name, $value )
    {
        //$this->{$name} = $value;
        if ( $name == 'from' || $name == 'to' )
        {
            $this->{$name} = date( "Y-m-d H:i:s", $value );
            $this->{$name . 'TimeStamp'} = $value;
            $this->set( 'diff', null );
        }
        elseif ( $name == 'diff' )
        {
            $this->diff = $this->toTimeStamp - $this->fromTimeStamp;
        }
        else
        {
            $this->{$name} = $value;
        }
    }
}

class OpenPAConsiglioPresenzaData
{
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
            $values = $helper->run();
            $percent = $helper->getPercent();

            self::$data[$id] = array(
                'values' => $values,
                'percent' => $percent
            );
        }
        else
        {
            self::$data[$id] = array(
                'values' => array(),
                'percent' => array()
            );
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

    public function __construct( Politico $politico, $functionName = null )
    {
        $this->politico = $politico;
        $this->politicoId = $politico->id();
        $this->functionName = $functionName;
    }

    public function offsetExists( $sedutaId )
    {
        if ( !OpenPAConsiglioPresenzaData::getData( $sedutaId ) )
        {
            OpenPAConsiglioPresenzaData::setData( $sedutaId );
        }
        return OpenPAConsiglioPresenzaData::getData( $sedutaId ) !== null;
    }

    public function offsetGet( $sedutaId )
    {
        $data = OpenPAConsiglioPresenzaData::getData( $sedutaId );
        if ( $data )
        {
            if ( $this->functionName == 'percent' )
            {
                $percent = $data['percent'];
                if ( isset( $percent[$this->politicoId] ) )
                {
                    return $percent[$this->politicoId];
                }
                return 0;
            }
            elseif ( $this->functionName == 'importo' )
            {
                $percent = $data['percent'];
                if ( isset( $percent[$this->politicoId] ) )
                {
                    if ( $percent[$this->politicoId] > 75 )
                    {
                        return $this->calcolaImportoGettone( 100 );
                    }
                    elseif ( $percent[$this->politicoId] < 75 && $percent[$this->politicoId] > 25 )
                    {
                        return $this->calcolaImportoGettone( 50 );
                    }
                }
                return 0;
            }
            else
            {
                foreach ( $data['values'] as $item )
                {
                    if ( $item->userId == $this->politicoId )
                    {
                        return $item;
                    }
                }
            }
        }

        return null;
    }

    protected function calcolaImportoGettone( $percent )
    {
        return number_format( (intval( $percent ) * self::$baseGettone/ 100 ), 2 );
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