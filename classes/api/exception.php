<?php

class ConsiglioApiException extends Exception
{
    const NOT_FOUND = 1000;

    const NOT_VALID = 2000;

    const AUTHENTICATION = 3000;

    const VOTAZIONE_ERROR = 4000;
    const VOTAZIONE_NOT_OPEN = 4001;
    const VOTAZIONE_NOT_ALLOWED = 4002;
    const VOTAZIONE_ALREADY_OPEN = 4003;
    const VOTAZIONE_OPEN_NOT_FOUND = 4003;
    const VOTAZIONE_TRY_CLOSE_NOT_OPEN = 4004;

    const VOTO_ERROR = 5000;
    const VOTO_NOT_ALLOWED = 5001;
    const VOTO_NOT_VALID = 5002;

    const PRESENZA_ERROR = 6000;
    const PRESENZA_NOT_VALID_SEDUTA = 6001;
    const PRESENZA_NOT_VALID_INOUT_PARAMETER = 6002;
    const PRESENZA_NOT_VALID_TYPE_PARAMETER = 6003;

    const POLITICO_ERROR = 7000;
    const POLITICO_NOT_FOUND = 7001;
    const POLITICO_NOT_ALLOWED = 7002;

    const PUNTO_ERROR = 8000;
    const PUNTO_ALREADY_OPEN = 8001;

    const SEDUTA_ERROR = 9000;
    const SEDUTA_NOT_IN_PROGRESS = 9001;
    const SEDUTA_CLOSED = 9002;
    const SEDUTA_NOT_SCHEDULED = 9003;

    protected $errorDetails = array();

    public function __construct( $message, $code = 0, $previous = null, $details = array() )
    {
        parent::__construct( $message, $code, $previous );
        $this->errorDetails = $details;
    }

    public function getErrorDetails()
    {
        return $this->errorDetails;
    }
}