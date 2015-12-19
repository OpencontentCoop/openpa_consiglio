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

    protected $userId;

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
        $presenze = OpenPAConsiglioPresenza::fetchBySeduta(
            $this->seduta,
            $this->startTime,
            null,
            null,
            $this->userId
        );
        $timeStampInizioSeduta = $this->seduta->dataOraEffettivaInizio();
        $timeStampFineSeduta =  $this->seduta->dataOraFine();
        $totalTime = $timeStampFineSeduta - $timeStampInizioSeduta;
        return array( 'data' => array(
            'start' => intval( $timeStampInizioSeduta ),
            'end' => intval( $timeStampFineSeduta ),
            'status' => $this->seduta->currentState()->attribute( 'identifier' ),
            'total' => intval( $totalTime ),
            'presenze' => $presenze
        ) );

    }

}