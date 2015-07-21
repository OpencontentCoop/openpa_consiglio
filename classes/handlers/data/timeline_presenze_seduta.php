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
        $this->format = eZHTTPTool::instance()->getVariable( 'format', 'dhtmlx' );
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

        if ( $this->format == 'jquery' )
            $data = $this->retrieveForJqueryGantt( $presenze );
        elseif ( $this->format == 'dhtmlx' )
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
        /** @var OpenPAConsiglioPresenzaTimelineCollection[] $data */
        $data = array();
        foreach( $presenze as $presenza )
        {
            $userId = $presenza->attribute( 'user_id' );
            if ( !isset( $data[$userId] ) )
            {
                $data[$userId] = OpenPAConsiglioPresenzaTimelineCollection::instance( $userId );
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
        return array(
            'data' => array(
                array(
                    "id" => 1,
                    "start_date" => "2013-04-01 14:30:00",
                    "duration" => 1440,
                    "text" => "Nome",
                    "values" => array(
                        array( 1, 6 ),
                        array( 0, 2.5 ),
                        array( 1, 3.5 ),
                        array( 0, 2 ),
                        array( 1, 10 )
                    )
                ),
                array(
                    "id" => 2,
                    "start_date" => "2013-04-01 14:30:00",
                    "duration" => 1440,
                    "text" => "Nome2",
                    "values" => array(
                        array( 0, 1 ),
                        array( 1, 2.5 ),
                        array( 0, 3.5 ),
                        array( 1, 7 ),
                        array( 0, 10 )
                    )
                )
            )
        );
    }


}