<?php

class DataHandlerTimeLinePresenzeSeduta implements OpenPADataHandlerInterface
{

    /**
     * @var Seduta
     */
    protected $seduta;

    /**
     * @var string
     */
    protected $startTime;


    /**
     * @var string
     */
    protected $format;

    public function __construct( array $Params )
    {
        $module = isset( $Params['Module'] ) ? $Params['Module'] : false;
        $sedutaId = eZHTTPTool::instance()->getVariable( 'seduta', isset( $Params['seduta'] ) ? $Params['seduta'] : 0 );
        $this->seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
        $this->startTime = eZHTTPTool::instance()->getVariable( 'startTime', null );
        $this->format = eZHTTPTool::instance()->getVariable( 'format', 'jquery' );
        if ( $module instanceof eZModule )
        {
            $module->setTitle( "Timeline Presenze seduta" );
        }
    }

    /**
     * @return string|array|object
     */
    public function getData()
    {
        $data = array();
        $presenze = OpenPAConsiglioPresenza::fetchBySeduta( $this->seduta, $this->startTime );

        if ( $this->format = 'jquery' )
            $data = $this->retrieveForJqueryGantt( $presenze );
        elseif ( $this->format = 'dhtmlx' )
            $data = $this->retrieveForDhtmlxGantt( $presenze );

        //echo '<pre>';print_r($data);die();
        return $data;
    }

    /**
     * @param OpenPAConsiglioPresenza[] $presenze
     * @return array
     */
    protected function retrieveForJqueryGantt( $presenze )
    {
        /** @var ConsiglioTimelineCollection[] $data */
        $data = array();
        foreach( $presenze as $presenza )
        {
            $userId = $presenza->attribute( 'user_id' );
            if ( !isset( $data[$userId] ) )
            {
                $data[$userId] = ConsiglioTimelineCollection::instance( $userId );
            }
            $data[$userId]->add( $presenza );
        }
        $returnValues = array();
        foreach( $data as $item )
        {
            $item->appendTo( $returnValues );
        }
        return $returnValues;
    }

    /**
     * @param OpenPAConsiglioPresenza[] $presenze
     * @return array
     */
    protected function retrieveForDhtmlxGantt( $presenze )
    {
        return array();
    }


}

class ConsiglioTimelineCollection
{
    private static $instances = array();

    protected $userId;

    /**
     * @var ConsiglioTimelineValue
     */
    protected $current;

    public $name;

    public $desc;

    /**
     * @var ConsiglioTimelineValue[]
     */
    public $values = array();

    /**
     * @var ConsiglioTimelineDetectionCollection
     */
    protected $detectionCollection;

    public static function instance( $userId )
    {
        if ( !isset( self::$instances[$userId] ) )
        {
            self::$instances[$userId] = new ConsiglioTimelineCollection( $userId );
        }
        return self::$instances[$userId];
    }

    protected function __construct( $userId )
    {
        $this->userId = $userId;
        $this->detectionCollection = ConsiglioTimelineDetectionCollection::instance( $userId );

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
            $this->current = new ConsiglioTimelineValue();
            $this->current->set( 'from', $createdTime );
        }
        return $this;
    }

    protected function closeValue( OpenPAConsiglioPresenza $presenza = null )
    {
        if ( $this->current instanceof ConsiglioTimelineValue && $presenza == null )
        {
            $this->values[] = $this->current;
            $this->current->set( 'to', time() );
            $this->current = null;
        }
        elseif ( $this->current instanceof ConsiglioTimelineValue && $presenza->attribute( 'in_out' ) == 0 )
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

class ConsiglioTimelineDetectionCollection
{
    private static $instances = array();

    protected $userId;

    public $name = ' ';

    public $desc = 'Rilevazioni';

    public $customClass = "ganttRed";

    /**
     * @var ConsiglioTimelineValue[]
     */
    public $values = array();

    public static function instance( $userId )
    {
        if ( !isset( self::$instances[$userId] ) )
        {
            self::$instances[$userId] = new ConsiglioTimelineDetectionCollection( $userId );
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
        $value = new ConsiglioTimelineValue();
        $value->set( 'from', $createdTime );
        $value->set( 'to', $createdTime + 1 );
        $value->label = $presenza->attribute( 'type' );
        $this->values[] = $value;
    }
}

class ConsiglioTimelineValue
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