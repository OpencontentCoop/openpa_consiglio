<?php

class ConsiglioApiHttpErrorResponse implements ezcMvcResultStatusObject
{
    public $code;
    public $message;
    public $errorCode;
    public $errorDetails;

    public function __construct( $code = null, $message = null, $errorCode = null, $errorDetails = array() )
    {
        $this->code = $code;
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
    }

    public function process( ezcMvcResponseWriter $writer )
    {
        if ( $writer instanceof ezcMvcHttpResponseWriter )
        {
            $writer->headers["HTTP/1.1 " . $this->code] = $this->message;
        }

        if ( $this->message !== null )
        {
            $writer->headers['Content-Type'] = 'application/json; charset=UTF-8';
            $writer->response->body = json_encode( array_merge( array(
                'error_code' => $this->errorCode,
                'error_message' => $this->message
            ), $this->errorDetails ) );
        }
    }
}