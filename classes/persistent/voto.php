<?php

class OpenPAConsiglioVoto extends eZPersistentObject
{

    const CONTRARIO = 0;
    const FAVOREVOLE = 1;
    const ASTENUTO = 2;


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

    static function fetch( $id )
    {
        return eZPersistentObject::fetchObject(
            self::definition(),
            null,
            array( 'id' => $id )
        );
    }

    public static function create( Seduta $seduta, Votazione $votazione, $value, $userId = null )
    {
        if ( !$seduta instanceof Seduta )
        {
            throw new Exception( "Non posso votare senza una Seduta valida" );
        }

        if ( !$votazione instanceof Votazione )
        {
            throw new Exception( "Non posso votare senza una Votazione valida" );
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

        $alreadyExists = eZPersistentObject::fetchObject(
            self::definition(),
            null,
            $row
        );

        if ( $alreadyExists instanceof OpenPAConsiglioVoto )
        {
            throw new Exception( "Esiste gia' un voto per user {$row['user_id']} nella votazione {$row['votazione_id']} della seduta {$row['seduta_id']}" );
        }

        if ( trim( $value ) == '' )
        {
            throw new Exception( "Valore del voto ($value) non valido" );
        }

        $row['value'] = (string) $value;
        $row['created_time'] = $createdTime;

        $votazione = new OpenPAConsiglioVoto( $row );
        return $votazione;
    }

    public static function countVotanti( Votazione $votazione )
    {
        $result = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            array(),
            array( 'votazione_id' => $votazione->id() ),
            false,
            null,
            false,
            false,
            array( array( 'operation' => 'count( * )',
                          'name' => 'count' ) ) );
        return $result[0]['count'];
    }

    public static function countFavorevoli( Votazione $votazione )
    {
        $result = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            array(),
            array( 'votazione_id' => $votazione->id(), 'value' => self::FAVOREVOLE ),
            false,
            null,
            false,
            false,
            array( array( 'operation' => 'count( * )',
                          'name' => 'count' ) ) );
        return $result[0]['count'];
    }

    public static function countContrari( Votazione $votazione )
    {
        $result = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            array(),
            array( 'votazione_id' => $votazione->id(), 'value' => self::CONTRARIO ),
            false,
            null,
            false,
            false,
            array( array( 'operation' => 'count( * )',
                          'name' => 'count' ) ) );
        return $result[0]['count'];
    }

    public static function countAstenuti( Votazione $votazione )
    {
        $result = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            array(),
            array( 'votazione_id' => $votazione->id(), 'value' => self::ASTENUTO ),
            false,
            null,
            false,
            false,
            array( array( 'operation' => 'count( * )',
                          'name' => 'count' ) ) );
        return $result[0]['count'];
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