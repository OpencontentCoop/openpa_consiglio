<?php

class OpenPAConsiglioPresenzaHelper
{
    /**
     * @vat OpenPAConsiglioPresenza[] $presenze
     */
    protected $presenze = array();

    protected $seduta;

    protected $startTime;

    /**
     * @param Seduta $seduta
     * @param int $startTime
     * @param int $userId
     */
    public function __construct( Seduta $seduta, $startTime = null, $userId = null )
    {
        $this->seduta = $seduta;
        $this->startTime = $startTime;
        $presenze = OpenPAConsiglioPresenza::fetchBySeduta( $this->seduta, $this->startTime, null, null, $userId );
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
        foreach( $this->seduta->partecipanti( false ) as $userId )
        {
            $allUserData[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId );
        }
        
        if ( empty( $this->presenze ) )
        {
            $data = $allUserData;
        }
        else
        {
            foreach( $this->presenze as $presenza )
            {
                $userId = $presenza->attribute( 'user_id' );
                if ( !isset( $data[$userId] ) )
                {
                    $data[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId );
                }
                $data[$userId]->add( $presenza );
            }
            
            foreach( array_keys( $allUserData ) as $userId )
            {
                if ( !isset( $data[$userId] ) )
                {
                    $data[$userId] = $allUserData[$userId];
                }
            }
        }        
        $returnValues = array();        
        foreach( $data as $item )
        {
            //$item->appendToArray( $returnValues );
            $returnValues[] = $item->get( $this->seduta, $this->startTime );
        }
        return $returnValues;
    }

}


class OpenPAConsiglioPresenzaTimelineCollection
{
    private static $instances = array();

    public $start;

    public $startTimeStamp;

    public $end;

    public static $endTimeStamp;

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

    public static function instance( $userId )
    {
        if ( !isset( self::$instances[$userId] ) )
        {
            self::$instances[$userId] = new OpenPAConsiglioPresenzaTimelineCollection( $userId );
        }
        return self::$instances[$userId];
    }

    protected function __construct( $userId )
    {
        $this->userId = $userId;
        $this->detections = OpenPAConsiglioPresenzaTimelineDetectionCollection::instance( $userId );

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
            $this->current->set( 'from', (int) $createdTime );
        }
        $this->detections->addValue( $presenza );
    }

    protected function closeValue( OpenPAConsiglioPresenza $presenza = null )
    {
        if ( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && $presenza instanceof OpenPAConsiglioPresenza && !$presenza->attribute( 'is_in' ) )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current->set( 'to', (int) $createdTime );
            $this->values[] = $this->current;
            $this->current = null;
        }
        elseif( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && !$presenza instanceof OpenPAConsiglioPresenza )
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
        if ( self::$endTimeStamp === null )
            self::$endTimeStamp = $seduta->dataOraFine() + 60 * 15;

        $timeline = array();
        $currentStart = $this->startTimeStamp;
        foreach( $this->values as $index => $value )
        {
            $fromTimeStamp = $value->fromTimeStamp;
            $toTimeStamp = $value->toTimeStamp;
            if ( $toTimeStamp > self::$endTimeStamp )
            {
                self::$endTimeStamp = $toTimeStamp + 60;
            }

            $diff = $fromTimeStamp - $currentStart;
            if ( $diff > 0 )
            {
                $interval = round( $diff / 60 / 15, 2 );
                $timeline[] = array(
                    0,
                    floatval( $interval ),
                    $currentStart . ' - ' . $fromTimeStamp,
                    date("Y-m-d H:i:s", $currentStart ) . ' - ' . date("Y-m-d H:i:s", $fromTimeStamp )
                );
                $interval = round( ( $toTimeStamp - $fromTimeStamp ) / 60 / 15, 2 );
                $timeline[] = array(
                    1,
                    floatval( $interval ),
                    $fromTimeStamp . ' - ' . $toTimeStamp,
                    date("Y-m-d H:i:s", $fromTimeStamp ) . ' - ' . date("Y-m-d H:i:s", $toTimeStamp )
                );
                $currentStart = $toTimeStamp;
            }
        }
        $this->intervals = $timeline;
        
        $this->start = date("Y-m-d H:i:s", $this->startTimeStamp );
        $this->end = date("Y-m-d H:i:s", self::$endTimeStamp );
        $this->duration = ( self::$endTimeStamp - $this->startTimeStamp ) / 60;
        
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

    public static function instance( $userId )
    {
        if ( !isset( self::$instances[$userId] ) )
        {
            self::$instances[$userId] = new OpenPAConsiglioPresenzaTimelineDetectionCollection( $userId );
        }
        return self::$instances[$userId];
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
        $value->time = date("Y-m-d H:i:s", $createdTime );
        $value->label = $presenza->attribute( 'type' );
        $value->id = $presenza->attribute( 'id' );
        $value->in_out = $presenza->attribute( 'in_out' );
        $value->is_in = $presenza->attribute( 'is_in' );
        $this->values[] = $value;
    }
}

class OpenPAConsiglioPresenzaTimelineDetectionValue
{
    public $time;
    public $timestamp;
    public $label;
    public $id;
    public $in_out;
    public $is_in;
}

class OpenPAConsiglioPresenzaTimelineValue
{
    public $from;
    public $to;
    public $fromTimeStamp;    
    public $toTimeStamp;
    //public $label;

    // @todo indagare perchè __set non funziona
    function set( $name, $value )
    {
        //$this->{$name} = $value;
        if ( $name == 'from' || $name == 'to' )
        {
            $this->{$name} = date("Y-m-d H:i:s", $value );
            $this->{$name . 'TimeStamp'} = $value;
        }
        else
        {
            $this->{$name} = $value;
        }
    }
}