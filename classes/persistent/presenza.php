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

    public $CreatedTime;

    public $UserID;

    public $SedutaID;

    public $Type;

    public $InOut;

    public $isIn;

    public $hasCheckin;

    public $hasManual;

    public $hasBeacons;

    function OpenPAConsiglioPresenza( $row = array() )
    {
        $this->PersistentDataDirty = false;
        if ( !empty( $row ) )
        {
            $this->fill( $row );
            $this->CreatedTime = intval( $this->CreatedTime );
            $this->UserID = intval( $this->UserID );
            $this->SedutaID = intval( $this->SedutaID );
            $this->InOut = intval( $this->InOut );
            $this->IsIn = intval( $this->isIn() );
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
            throw new ConsiglioApiException( "Non posso registrare una presenza senza una Seduta valida", ConsiglioApiException::PRESENZA_NOT_VALID_SEDUTA );
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

    static function fetchLastByUserID( $userId, $type = 'checkin' )
    {
        $conds = array(
            'user_id' => (int)$userId,
            'type' => $type,
        );
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            $conds,
            array( 'created_time' => 'desc' ),
            array( 'limit' => 1, 'offset' => 0 )
        );
        if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
        {
            return $presenze[0];
        }
        return false;
    }

    static function fetchLastByUserIDAndSedutaID( $userId, $sedutaId, $type = 'checkin' )
    {
        $conds = array(
            'user_id' => intval( $userId ),
            'seduta_id' => intval( $sedutaId ),
            'type' => $type,
        );
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            $conds,
            array( 'created_time' => 'desc' ),
            array( 'limit' => 1, 'offset' => 0 )
        );
        if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
        {
            return $presenze[0];
        }
        return false;
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

    static function getUserInOutInSeduta( Seduta $seduta, $userId, $timestampsIntervals = array() )
    {
        $conds = array(
            'seduta_id' => intval( $seduta->id() ),
            'user_id' => (int) $userId
        );
        if ( is_array( $timestampsIntervals ) && count( $timestampsIntervals ) == 2 )
        {
            $conds['created_time'] = array( false, array( intval( $timestampsIntervals[0] ), intval( $timestampsIntervals[1] ) ) );
        }

        /** @var OpenPAConsiglioPresenza[] $presenze */
        $presenze = parent::fetchObjectList(
            self::definition(),
            null,
            $conds,
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
        return eZUser::fetch( $this->attribute( 'user_id' ) );
    }

    function setUser( eZUser $id )
    {
        $this->UserID = $id;
    }

    /**
     * L'ultimo intervento del segretario in senso positivo (marca come presente) fa in modo che il sistema ignori i beacons negativi
     * Per disattivare questo stato è necessario un intervento negativo del segretario
     */
    public function isIn()
    {
        if ( $this->isIn === null )
        {
            if ( $this->attribute( 'type' ) == 'beacons' )
            {
                $hasCheckout = false;
                $hasCheckin = $this->hasCheckin( true );
                if ( $hasCheckin instanceof OpenPAConsiglioPresenza
                     && intval( $hasCheckin->attribute( 'in_out' ) ) == 0
                )
                {
                    $hasCheckout = true; // ha già eseguito il checkout
                }
                if ( $hasCheckout )
                {
                    $this->isIn = false;
                }
                else
                {
                    $hasManual = $this->hasManual( true );
                    if ( $hasManual instanceof OpenPAConsiglioPresenza )
                    {
                        if ( intval( $hasManual->attribute( 'in_out' ) ) == 0
                             && intval( $this->attribute( 'in_out' ) ) == 1
                        )
                        {
                            $this->isIn = false; // beacons postivo ma e manual negativo
                        }
                        elseif ( intval( $hasManual->attribute( 'in_out' ) ) == 1
                                 && intval( $this->attribute( 'in_out' ) ) == 0
                        )
                        {
                            $this->isIn = true; // beacons negativo ma e manual positivo
                        }
                    }
                }
            }

            $this->isIn = $this->attribute( 'in_out' );
        }
        return $this->isIn;
    }

    public function hasCheckin( $asObject = false )
    {
        if ( $this->hasCheckin == null )
        {
            /** @var OpenPAConsiglioPresenza[] $presenze */
            $presenze = parent::fetchObjectList(
                self::definition(),
                null,
                array(
                    'seduta_id' => intval( $this->attribute( 'seduta_id' ) ),
                    'user_id' => (int)$this->attribute( 'user_id' ),
                    'type' => 'checkin',
                    'created_time' => array( '<=', intval( $this->attribute( 'created_time' ) ) )
                ),
                array( 'created_time' => 'desc' ),
                array( 'limit' => 1, 'offset' => 0 )
            );
            if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
            {
                $this->hasCheckin = $presenze[0];
            }
        }
        if ( !$asObject && $this->hasCheckin instanceof OpenPAConsiglioPresenza )
            return $this->hasCheckin->attribute( 'in_out' );
        return $this->hasCheckin;
    }

    public function hasManual( $asObject = false )
    {
        if ( $this->hasManual === null )
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
                    $this->hasManual = $presenze[0];
                }
            }
        }
        if ( !$asObject && $this->hasManual instanceof OpenPAConsiglioPresenza )
            return $this->hasManual->attribute( 'in_out' );
        return $this->hasManual;
    }

    public function hasBeacons( $asObject = false )
    {
        if ( $this->hasBeacons === null )
        {
            /** @var OpenPAConsiglioPresenza[] $presenze */
            $presenze = parent::fetchObjectList(
                self::definition(),
                null,
                array(
                    'seduta_id' => intval( $this->attribute( 'seduta_id' ) ),
                    'user_id' => (int)$this->attribute( 'user_id' ),
                    'type' => 'beacons',
                    'created_time' => array( '<=', intval( $this->attribute( 'created_time' ) ) )
                ),
                array( 'created_time' => 'desc' ),
                array( 'limit' => 1, 'offset' => 0 )
            );
            if ( isset( $presenze[0] ) && $presenze[0] instanceof OpenPAConsiglioPresenza )
            {
                $this->hasBeacons = $presenze[0];
            }
        }
        if ( !$asObject && $this->hasBeacons instanceof OpenPAConsiglioPresenza )
            return $this->hasBeacons->attribute( 'in_out' );
        return $this->hasBeacons;
    }

    public function jsonSerialize( $includeFunctionAttributes = true )
    {
        $data = array();

        $def = $this->definition();
        $attributes = $includeFunctionAttributes ? $this->attributes() : array_keys( $def["fields"] );
        $attributes[] = 'is_in';

        foreach( $attributes as $identifier )
        {
            if ( $identifier == 'created_time' )
            {
                $data['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $this->attribute( $identifier ) );
                $data['timestamp'] = $this->attribute( $identifier );
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