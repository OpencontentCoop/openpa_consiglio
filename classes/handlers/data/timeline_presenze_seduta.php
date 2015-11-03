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
        $sedutaId = eZHTTPTool::instance()->getVariable(
            'seduta',
            isset( $Params['seduta'] ) ? $Params['seduta'] : 0
        );
        $this->seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
        $this->startTime = eZHTTPTool::instance()->getVariable( 'startTime', null );
        $this->userId = eZHTTPTool::instance()->getVariable( 'userId', null );
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
        //        return array(
        //            'data' => array(
        //                array(
        //                    "id" => 1,
        //                    "start_date" => "2013-04-01 11:30:00",
        //                    "end_date" => "2013-04-01 18:00:00",
        //                    "duration" => 240,
        //                    "text" => "Nome",
        //                    "values" => array(
        //                        array( 1, 1 ),
        //                        array( 0, 1 ),
        //                        array( 1, 1 ),
        //                        array( 0, 1 ),
        //                        array( 1, 1 )
        //                    )
        //                ),
        //                array(
        //                    "id" => 2,
        //                    "start_date" => "2013-04-01 14:30:00",
        //                    "duration" => 1440,
        //                    "text" => "Nome2",
        //                    "values" => array(
        //                        array( 0, 1 ),
        //                        array( 1, 2.5 ),
        //                        array( 0, 3.5 ),
        //                        array( 1, 7 ),
        //                        array( 0, 10 )
        //                    )
        //                )
        //            )
        //        );

        $data = array();
        $helper = new OpenPAConsiglioPresenzaHelper( $this->seduta, $this->startTime, $this->userId );
        $values = $helper->run();

        if ( eZHTTPTool::instance()->hasGetVariable( 'debug' ) )
        {
            return $values;
        }

        foreach ( $values as $value )
        {
            $link = 'editorialstuff/action/seduta/' . $this->seduta->id() . "?=ActionIdentifier=GetAttestatoPresenza&ActionParameters[presente]={$value->userId}";
            eZURI::transformURI( $link );
            $downloadText = "<a class=\"btn btn-success btn-xs\" herf=\"{$link}\"><i class=\"fa fa-download\"></i> Attestato</a>";
            $item = array(
                'id' => $value->userId,
                'start_date' => $value->start,
                'end_date' => $value->end,
                'duration' => $value->duration,
                'text' => $value->name,
                'values' => array(),
                'detections' => $this->userId == null ? array() : $value->detections->values
            );
            foreach ( $value->intervals as $interval )
            {
                $item['values'][] = array( $interval[0], $interval[1], $interval[3] );
            }            
            $data[] = $item;
        }

        return array( 'data' => $data );

    }

}