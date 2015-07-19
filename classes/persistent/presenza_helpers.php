<?php

class OpenPAConsiglioPresenzaTimelineCollection
{
    private static $instances = array();

    protected $userId;

    /**
     * @var OpenPAConsiglioPresenzaTimelineValue
     */
    protected $current;

    public $name;

    public $desc;

    /**
     * @var OpenPAConsiglioPresenzaTimelineValue[]
     */
    public $values = array();

    /**
     * @var OpenPAConsiglioPresenzaTimelineDetectionCollection
     */
    protected $detectionCollection;

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
        $this->detectionCollection = OpenPAConsiglioPresenzaTimelineDetectionCollection::instance( $userId );

        $userName = false;
        $object = eZContentObject::fetch( $userId );
        if ( $object instanceof eZContentObject )
        {
            $userName = $object->attribute( 'name' );
        }
        $this->name = $userName;

        $this->desc = 'Presenza';
    }

    public function add( OpenPAConsiglioPresenza $presenza )
    {
        $this->openValue( $presenza)->closeValue( $presenza );
        $this->detectionCollection->addValue( $presenza );
    }

    protected function openValue( OpenPAConsiglioPresenza $presenza )
    {
        if ( $this->current === null && $presenza->attribute( 'in_out' ) == 1 )
        {
            $createdTime = $presenza->attribute( 'created_time' );
            $this->current = new OpenPAConsiglioPresenzaTimelineValue();
            $this->current->set( 'from', $createdTime );
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
            $this->current->set( 'to', $createdTime );
            $this->values[] = $this->current;
            $this->current = null;
        }
        return $this;
    }

    public function appendTo( array &$returnValues )
    {
        $returnValues[] = $this->closeValue();
        $returnValues[] = $this->detectionCollection;
    }
}

class OpenPAConsiglioPresenzaTimelineDetectionCollection
{
    private static $instances = array();

    protected $userId;

    public $name = ' ';

    public $desc = 'Rilevazioni';

    public $customClass = "ganttRed";

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
        $value->set( 'from', $createdTime );
        $value->set( 'to', $createdTime + 1 );
        $value->label = $presenza->attribute( 'type' );
        $this->values[] = $value;
    }
}

class OpenPAConsiglioPresenzaTimelineValue
{
    public $from;
    public $to;
    public $label;

    // @todo indagare perchè __set non funziona
    function set( $name, $value )
    {
        if ( $name == 'from' || $name == 'to' )
        {
            $value = $value * 1000;
            $this->{$name} = "/Date({$value})/";
        }
        else
        {
            $this->{$name} = $value;
        }
    }
}