<?php

class DataHandlerPercentualePresenzeSeduta implements OpenPADataHandlerInterface
{

    public function __construct( array $Params )
    {
    }

    /**
     * @return string|array|object
     */
    public function getData()
    {
        return array(
            array( 'Presente', 58.33 ),
            array( 'Assente', 21.67 )
        );
    }
}