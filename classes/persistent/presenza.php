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

    protected $CreatedTime;

    protected $UserID;

    protected $SedutaID;

    protected $Type;

    function OpenPAConsiglioPresenza( $row = array() )
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
            'name' => 'openpa_consiglio_presenza',
            "function_attributes" => array(
                "has_checkin" => "hasCheckin",
                "has_manual" => "hasManual",
                "has_beacons" => "hasBeacons",
                "is_in" => "isIn"
            )
        );
    }

    public static function create( Seduta $seduta, $inOut = false, $type = 'manual', $userId = null )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new Exception( "Non posso registrare una presenza senza una Seduta valida" );
        }

        $createdTime = time();

        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
        }

        $presenza = new OpenPAConsiglioPresenza( array(
            'user_id' => $userId,
            'seduta_id' => intval( $seduta->id() ),
            'type' => (string) $type,
            'in_out' => intval( $inOut ),
            'created_time' => intval( $createdTime )
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
        $conds = array( 'seduta_id' => intval( $seduta->id() ) );
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

    static function getUserInOutInSeduta( Seduta $seduta, $userId )
    {
        /** @var OpenPAConsiglioPresenza[] $presenze */
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            array(
                'seduta_id' => intval( $seduta->id() ),
                'user_id' => (int) $userId
            ),
            array( 'created_time' => 'desc' ),
            array( 'limit' => 1, 'offset' => 0 )
        );
        if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
        {
            return $presenze[0];
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

    //@todo gestire priorità manuale | checkin | beacons
    public function isIn()
    {
        return $this->attribute( 'in_out' );
    }

    public function hasCheckin()
    {
        /** @var OpenPAConsiglioPresenza[] $presenze */
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            array(
                'seduta_id' => intval( $this->attribute( 'seduta_id' ) ),
                'user_id' => (int) $this->attribute( 'user_id' ),
                'type' => 'checkin',
                'created_time' => array( '<=', intval( $this->attribute( 'created_time' ) ) )
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

    public function hasManual()
    {
        $sedutaId = $this->attribute( 'seduta_id' );
        if ( !empty( $sedutaId ) )
        {
            /** @var OpenPAConsiglioPresenza[] $presenze */
            $presenze = parent::fetchObjectList(
                self::definition(),
                null,
                array(
                    'seduta_id' => $this->attribute( 'seduta_id' ),
                    'user_id' => (int)$this->attribute( 'user_id' ),
                    'type' => 'manual',
                    'created_time' => array( '<=', $this->attribute( 'created_time' ) )
                ),
                array( 'created_time' => 'desc' ),
                array( 'limit' => 1, 'offset' => 0 )
            );
            if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
            {
                return $presenze[0]->attribute( 'in_out' );
            }
        }
        return false;
    }

    public function hasBeacons()
    {
        /** @var OpenPAConsiglioPresenza[] $presenze */
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            array(
                'seduta_id' => intval( $this->attribute( 'seduta_id' ) ),
                'user_id' => (int) $this->attribute( 'user_id' ),
                'type' => 'beacons',
                'created_time' => array( '<=', intval( $this->attribute( 'created_time' ) ) )
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
    
    public function jsonSerialize()
    {
        $data = array();
        foreach( $this->attributes() as $identifier )
        {
            if ( $identifier == 'created_time' )
            {
                $dateTime = DateTime::createFromFormat( 'U', $this->attribute( $identifier ) );
                $data[$identifier] = $dateTime->format( Seduta::DATE_FORMAT );
                $data[$identifier . 'stamp'] = $this->attribute( $identifier );
            }
            elseif( in_array( $identifier, array( 'id', 'user_id', 'seduta_id', "has_checkin", "has_manual", "has_beacons", "is_in") ) )
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
//        $data['has_checkin'] = (int) $this->hasCheckin();
//        $data['has_manual'] = (int) $this->hasManual();
//        $data['has_beacons'] = (int) $this->hasBeacons();
        return $data;
    }
}