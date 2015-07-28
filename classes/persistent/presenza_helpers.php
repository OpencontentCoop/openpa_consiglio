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
        if ( empty( $this->presenze ) )
        {
            foreach( $this->seduta->partecipanti( false ) as $userId )
            {
                $data[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId );
            }
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

    public $endTimeStamp;

    public $duration;

    public $intervals;

    public $userId;

    /**
     * @var OpenPAConsiglioPresenzaTimelineValue
     */
    protected $current;

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
        $this->openValue( $presenza)->closeValue( $presenza );
        $this->detections->addValue( $presenza );
    }

    protected function openValue( OpenPAConsiglioPresenza $presenza )
    {
        if ( $this->current === null && $presenza->attribute( 'in_out' ) == 1 )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current = new OpenPAConsiglioPresenzaTimelineValue();
            $this->current->set( 'from', (int) $createdTime );
        }
        return $this;
    }

    protected function closeValue( OpenPAConsiglioPresenza $presenza = null )
    {
        if ( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && $presenza == null )
        {
            $this->values[] = $this->current;
            $this->current->set( 'to', time() );
            $this->current = null;
        }
        elseif ( $this->current instanceof OpenPAConsiglioPresenzaTimelineValue && $presenza->attribute( 'in_out' ) == 0 )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current->set( 'to', (int) $createdTime );
            $this->values[] = $this->current;
            $this->current = null;
        }
        return $this;
    }

    public function get( Seduta $seduta, $startTime = null )
    {
        $this->closeValue();

        $this->startTimeStamp = $seduta->dataOra();
        $this->endTimeStamp = $seduta->dataOraFine();

        $this->start = $seduta->dataOra( 'Y-m-d H:i:s' );
        $this->end = $seduta->dataOraFine( 'Y-m-d H:i:s' );
        $this->duration = ( $this->endTimeStamp - $this->startTimeStamp ) / 60;

        $timeline = array();
        $currentStart = $this->startTimeStamp;
        foreach( $this->values as $index => $value )
        {
            $fromTimeStamp = $value->fromTimeStamp;
            $toTimeStamp = $value->toTimeStamp;
            if ( $toTimeStamp > $this->endTimeStamp )
            {
                $toTimeStamp = $this->endTimeStamp;
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
        $value = new OpenPAConsiglioPresenzaTimelineValue();
        $value->set( 'from', (int)$createdTime );
        $value->set( 'to', (int)$createdTime + 1 );
        $value->label = $presenza->attribute( 'type' );
        $value->id = $presenza->attribute( 'id' );
        $value->in_out = $presenza->attribute( 'in_out' );
        $this->values[] = $value;
    }
}

class OpenPAConsiglioPresenzaTimelineValue
{
    public $from;
    public $fromTimeStamp;
    public $to;
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