<?php

class OpenPAConsiglioGettone extends eZPersistentObject
{

    function __construct( $row = array() )
    {
        $this->PersistentDataDirty = false;
        if ( !empty( $row ) )
        {
            $this->fill( $row );
        }
    }

    public static function definition()
    {
        return array(
            'fields' => array(
                'id' => array(
                    'name' => 'ID',
                    'datatype' => 'integer',
                    'default' => 0,
                    'required' => true
                ),
                'user_id' => array(
                    'name' => 'UserID',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true
                ),
                'seduta_id' => array(
                    'name' => 'SedutaID',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true
                ),
                'created_time' => array(
                    'name' => 'CreatedTime',
                    'datatype' => 'integer',
                    'default' => time(),
                    'required' => false
                )
            ),
            'keys' => array( 'id' ),
            'increment_key' => 'id',
            'class_name' => 'OpenPAConsiglioGettone',
            'name' => 'openpa_consiglio_gettone'
        );
    }

    /**
     * @param $userId
     *
     * @return OpenPAConsiglioGettone[]
     */
    static function fetchByUserID( $userId )
    {
        return parent::fetchObjectList(
            self::definition(),
            null,
            array( 'user_id' => (int)$userId ),
            array( 'created_time' => 'desc' )
        );
    }
}