<?php

class OpenPAConsiglioPresenza extends eZPersistentObject
{

    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var Seduta
     */
    protected $seduta;

    function Presenza( $row = array() )
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
                'type' => array(
                    'name' => 'Type',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'in_out' => array(
                    'name' => 'InOut',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => false
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
            'class_name' => 'OpenPAConsiglioPresenza',
            'name' => 'openpa_consiglio_presenza',
            'function_attributes' => array(
                'user' => 'getUser'
            ),
            'set_functions' => array(
                'user' => 'setUser'
            )
        );
    }

    public static function create( Seduta $seduta, $inOut = false, $type = 'manual', $userId = null )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new Exception( "Can not create Presenza without a valid Seduta" );
        }

        $createdTime = time();

        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
        }

        $presenza = new OpenPAConsiglioPresenza( array(
            'user_id' => $userId,
            'seduta_id' => $seduta->id(),
            'type' => (string) $type,
            'in_out' => (int) $inOut,
            'created_time' => $createdTime
        ));
        return $presenza;
    }

    /**
     * @param Seduta $seduta
     *
     * @return OpenPAConsiglioPresenza[]
     */
    static function fetchBySeduta( Seduta $seduta, $startTime = null )
    {
        return parent::fetchObjectList(
            self::definition(),
            null,
            array( 'seduta_id' => $seduta->id() ),
            array( 'created_time' => 'asc' )
        );
    }

    function getUser()
    {

    }

    function setUser( eZUser $id )
    {
        $this->UserID = $id;
    }
}