<?php

class OpenPAConsiglioVoto extends eZPersistentObject
{

    const CONTRARIO = '0';
    const FAVOREVOLE = '1';
    const ASTENUTO = '2';


    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var Seduta
     */
    protected $seduta;

    function OpenPAConsiglioVoto( $row = array() )
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
                'votazione_id' => array(
                    'name' => 'VotazioneID',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true
                ),
                'value' => array(
                    'name' => 'Value',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'anomaly' => array(
                    'name' => 'Anomaly',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => false
                ),
                'presenza_id' => array(
                    'name' => 'PresenzaID',
                    'datatype' => 'integer',
                    'default' => 0,
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
            'class_name' => 'OpenPAConsiglioVoto',
            'name' => 'openpa_consiglio_voto'
        );
    }

    /**
     * @param $id
     *
     * @return OpenPAConsiglioVoto
     */
    static function fetch( $id )
    {
        return eZPersistentObject::fetchObject(
            self::definition(),
            null,
            array( 'id' => $id )
        );
    }

    public static function userAlreadyVoted( Votazione $votazione, $userId, $returnBool = true )
    {
        $row = array(
            'user_id' => $userId,
            'votazione_id' => $votazione->id()
        );

        $alreadyExists = eZPersistentObject::fetchObject(
            self::definition(),
            null,
            $row
        );

        if ( $returnBool )
            return $alreadyExists instanceof OpenPAConsiglioVoto;
        else
            return $alreadyExists;
    }

    public static function create( Seduta $seduta, Votazione $votazione, $value, $userId = null )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new ConsiglioApiException( "Non posso votare senza una Seduta valida", ConsiglioApiException::NOT_FOUND );
        }

        if ( !$votazione instanceof Votazione )
        {
            throw new ConsiglioApiException( "Non posso votare senza una Votazione valida", ConsiglioApiException::NOT_FOUND );
        }

        $createdTime = time();

        if ( $userId === null )
        {
            $userId = eZUser::currentUserID();
        }

        $row = array(
            'user_id' => $userId,
            'seduta_id' => $seduta->id(),
            'votazione_id' => $votazione->id()            
        );

        $alreadyExists = self::userAlreadyVoted( $votazione, $userId, false );
        if ( $alreadyExists instanceof OpenPAConsiglioVoto )
        {
            throw new ConsiglioApiException(
                "Esiste gia' un voto per user {$userId} nella votazione {$votazione->id()} della seduta {$seduta->id()}",
                ConsiglioApiException::VOTO_NOT_ALLOWED,
                null,
                array(
                    'user_voted' => true,
                    'vote_value' => $alreadyExists->attribute( 'value' )
                )
            );
        }

        if ( trim( $value ) == '' )
        {
            throw new ConsiglioApiException( "Valore del voto ($value) non valido", ConsiglioApiException::VOTO_NOT_VALID );
        }
        $presenza = OpenPAConsiglioPresenza::getUserInOutInSeduta( $seduta, $userId );
        $anomaly = $presenza instanceof OpenPAConsiglioPresenza ? $presenza->attribute( 'is_in' ) == false : true;

        $row['value'] = (string) $value;
        $row['created_time'] = $createdTime;
        $row['anomaly'] = intval( $anomaly );
        $row['presenza_id'] = $presenza instanceof OpenPAConsiglioPresenza ? intval( $presenza->attribute( 'id' ) ) : 0;

        $votazione = new OpenPAConsiglioVoto( $row );
        return $votazione;
    }

    public static function countVotanti( Votazione $votazione, $valueCondition = null )
    {
        $conds = array( 'votazione_id' => $votazione->id() );
        if ( $valueCondition !== null )
        {
            $conds['value'] = trim( $valueCondition );
        }
        $result = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            array(),
            $conds,
            false,
            null,
            false,
            false,
            array( array( 'operation' => 'count( * )',
                          'name' => 'count' ) ) );
        return $result[0]['count'];
    }

    public static function votanti( Votazione $votazione, $asObjects = false, $checkConsistency = false, $valueCondition = null )
    {
        $partecipanti = array();
        if ( $asObjects || $checkConsistency )
        {
            $partecipanti = $votazione->getSeduta()->partecipanti();
        }
        $conds = array( 'votazione_id' => $votazione->id() );
        if ( $valueCondition !== null )
        {
            $conds['value'] = $valueCondition;
        }
        $users = array();
        /** @var OpenPAConsiglioVoto[] $voti */
        $voti = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            null,
            $conds,
            false,
            null
        );
        foreach( $voti as $voto )
        {
            if ( $asObjects || $checkConsistency )
            {
                $user = false;
                foreach( $partecipanti as $partecipante )
                {
                    if ( $partecipante->id() == $voto->attribute( 'user_id' ) )
                    {
                        $user = $partecipante;
                        break;
                    }
                }
            }
            else
            {
                $user = $voto->attribute( 'user_id' );
            }
            if ( $user )
                $users[] = $user;
        }
        return $users;
    }

    public static function countFavorevoli( Votazione $votazione )
    {
        return self::countVotanti( $votazione, self::FAVOREVOLE );
    }

    public static function favorevoli( Votazione $votazione, $asObjects = false, $checkConsistency = false )
    {
        return self::votanti( $votazione, $asObjects, $checkConsistency, self::FAVOREVOLE );
    }

    public static function countContrari( Votazione $votazione )
    {
        return self::countVotanti( $votazione, self::CONTRARIO );
    }

    public static function contrari( Votazione $votazione, $asObjects = false, $checkConsistency = false )
    {
        return self::votanti( $votazione, $asObjects, $checkConsistency, self::CONTRARIO );
    }

    public static function countAstenuti( Votazione $votazione )
    {
        return self::countVotanti( $votazione, self::ASTENUTO );
    }

    public static function astenuti( Votazione $votazione, $asObjects = false, $checkConsistency = false )
    {
        return self::votanti( $votazione, $asObjects, $checkConsistency, self::ASTENUTO );
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
            elseif( in_array( $identifier, array( 'id', 'user_id', 'seduta_id', 'votazione_id' ) ) )
            {
                $data[$identifier] = (int) $this->attribute( $identifier );
            }
            else
            {
                $data[$identifier] = $this->attribute( $identifier );
            }
        }
        return $data;
    }

}