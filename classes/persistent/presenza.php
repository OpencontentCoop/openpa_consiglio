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
                    'datatype' => 'integer',
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
            'name' => 'openpa_consiglio_presenza'
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

    static function fetch( $id )
    {
        return eZPersistentObject::fetchObject(
            self::definition(),
            null,
            array( 'id' => $id )
        );
    }

    /**
     * @param Seduta $seduta
     * @param null $startTime
     * @param null $inOut
     * @param null $type
     * @param null $userId
     *
     * @return OpenPAConsiglioPresenza[]
     */
    static function fetchBySeduta( Seduta $seduta, $startTime = null, $inOut = null, $type = null, $userId = null )
    {
        $conds = array( 'seduta_id' => $seduta->id() );
        if ( $startTime !== null )
        {
            $startTimestamp = $endTimestamp = false;
            if ( is_numeric( $startTime ) )
            {
                $startTimestamp = $startTime;
                $endTimestamp = time();
            }
            elseif ( is_array( $startTime ) )
            {
                $startTimestamp = $startTime[0];
                $endTimestamp = $startTime[1];
            }
            if ( $startTimestamp && $endTimestamp )
            {
                $conds['created_time'] = array( '', array( $startTimestamp, $endTimestamp ) );
            }
        }
        if ( $inOut !== null )
        {
            $conds['in_out'] = (int) $inOut;
        }
        if ( $type !== null )
        {
            $conds['type'] = $type;
        }
        if ( $userId !== null )
        {
            $conds['user_id'] = (int)$userId;
        }
        return parent::fetchObjectList(
            self::definition(),
            null,
            $conds,
            array( 'created_time' => 'asc' )
        );
    }

    //@todo gestire anomalie manuale | checkIn | beacon
    static function getUserInOutInSeduta( Seduta $seduta, $userId )
    {
        /** @var OpenPAConsiglioPresenza[] $presenze */
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            array(
                'seduta_id' => $seduta->id(),
                'user_id' => (int) $userId
            ),
            array( 'created_time' => 'desc' ),
            array( 'limit' => 1, 'offset' => 0 )
        );
        if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
        {
            return $presenze[0]->attribute( 'in_out' );
        }
        return false;
    }

    function getUser()
    {

    }

    function setUser( eZUser $id )
    {
        $this->UserID = $id;
    }

    public function jsonSerialize()
    {
        $data = array();
        foreach( $this->attributes() as $identifier )
        {
            if ( $identifier == 'created_time' )
            {
                $dateTime = DateTime::createFromFormat( 'U', $this->attribute( $identifier ) );
                $data[$identifier] = $dateTime->format( Seduta::DATE_FORMAT );
            }
            elseif( in_array( $identifier, array( 'id', 'user_id', 'seduta_id') ) )
            {
                $data[$identifier] = (int) $this->attribute( $identifier );
            }
            elseif ( $identifier == 'in_out' )
            {
                $data[$identifier] = (bool) $this->attribute( $identifier );
            }
            else
            {
                $data[$identifier] = $this->attribute( $identifier );
            }
        }
        return $data;
    }
}