<?php

class OpenPAConsiglioVotazioneResultHandlerDefault implements OpenPAConsiglioVotazioneResultHandlerInterface
{
    /**
     * @var Votazione
     */
    protected $currentVotazione;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $currentVotazioneDataMap;

    protected $startTimestamp;

    protected $endTimestamp;

    protected static $userCache = array();

    protected $data = array();

    protected $fnData = array();

    public function attributes()
    {
        $keys = array_merge( array_keys( $this->data ), array_keys( $this->fnData ) );
        return $keys;
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        if ( isset( $this->data[$key] ) )
        {
            return $this->data[$key];
        }
        elseif ( isset( $this->fnData[$key] ) )
        {
            return call_user_func( array( $this, $this->fnData[$key] ) );
            //return $this->{$this->fnData[$key]}();
        }
        eZDebug::writeNotice( "Attribute $key does not exist", get_called_class() );
        return false;
    }

    public function __construct( $data = null )
    {
        if ( is_array( $data ) )
            $this->data = $data;
    }

    public function getDescription()
    {
        return "Votazione a maggioranza semplice. Il quorum strutturale è rappresentato dalla metà più uno degli aventi diritto. Il quorum funzionale è rappresentato dalla maggiornaza dei votanti";
    }

    public function getTemplateName()
    {
        return 'non_votante_astenuto.tpl';
    }

    public function store()
    {
        $this->currentVotazioneDataMap[Votazione::$presentiIdentifier]->fromString( $this->getPresentiCount() );
        $this->currentVotazioneDataMap[Votazione::$presentiIdentifier]->store();

        $this->currentVotazioneDataMap[Votazione::$favorevoliIdentifier]->fromString( $this->getFavorevoliCount() );
        $this->currentVotazioneDataMap[Votazione::$favorevoliIdentifier]->store();

        $this->currentVotazioneDataMap[Votazione::$votantiIdentifier]->fromString( $this->getVotantiCount() );
        $this->currentVotazioneDataMap[Votazione::$votantiIdentifier]->store();

        $this->currentVotazioneDataMap[Votazione::$contrariIdentifier]->fromString( $this->getContrariCount() );
        $this->currentVotazioneDataMap[Votazione::$contrariIdentifier]->store();

        $this->currentVotazioneDataMap[Votazione::$astenutiIdentifier]->fromString( $this->getAstenutiCount() );
        $this->currentVotazioneDataMap[Votazione::$astenutiIdentifier]->store();
    }

    public function getResult()
    {
        return $this->getFavorevoliCount() >= $this->getQuorumFunzionale();
    }
    
    protected static function forumlaQuorum( $totale )
    {
        $meta = floor( $totale / 2 );
        return $meta + 1;
    }

    protected function getQuorumFunzionale()
    {
        return self::forumlaQuorum( $this->getPresentiCount() );        
    }

    protected function getQuorumStrutturale()
    {
        if ( !isset( $this->data['quorum_strutturale'] ) )
        {
            $aventiDiritto = count( $this->currentVotazione->getSeduta()->partecipanti() );
            $this->data['quorum_strutturale'] = self::forumlaQuorum( $aventiDiritto );
        }
        return $this->data['quorum_strutturale'];
    }

    public function isValid()
    {
        return $this->getPresentiCount() >= $this->getQuorumStrutturale();
    }

    /**
     * @param Votazione $votazione
     *
     * @return OpenPAConsiglioVotazioneResultHandlerInterface
     */
    public function setCurrentVotazione( Votazione $votazione )
    {
        $this->data = array();
        $this->currentVotazione = $votazione;
        $this->currentVotazioneDataMap = $this->currentVotazione->getObject()->attribute( 'data_map' );
        $this->startTimestamp = $this->currentVotazioneDataMap[Votazione::$startDateIdentifier]->toString();
        $this->endTimestamp = $this->currentVotazioneDataMap[Votazione::$endDateIdentifier]->hasContent() ? $this->currentVotazioneDataMap[Votazione::$endDateIdentifier]->toString() : time();
        $this->fnData = array(
            'valida' => 'isValid',
            'approvata' => 'getResult',
            'quorum_funzionale' => 'getQuorumFunzionale',
            'quorum_strutturale' => 'getQuorumStrutturale',
            'presenti' => 'getPresenti',
            'presenti_count' => 'getPresentiCount',
            'assenti' => 'getAssenti',
            'assenti_count' => 'getAssentiCount',
            'votanti' => 'getVotanti',
            'votanti_count' => 'getVotantiCount',
            'non_votanti' => 'getNonVotanti',
            'non_votanti_count' => 'getNonVotantiCount',
            'favorevoli' => 'getFavorevoli',
            'favorevoli_count' => 'getFavorevoliCount',
            'contrari' => 'getContrari',
            'contrari_count' => 'getContrariCount',
            'astenuti' => 'getAstenuti',
            'astenuti_count' => 'getAstenutiCount',
            'anomalie' => 'getAnomalie'
        );
        return $this;
    }

    protected function getAnomalie()
    {
        $data = array();
        $calcoloPresenze = $this->calcolaPresenze();
        /** @var OpenPAConsiglioVoto[] $anomalie */
        $anomalie = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
            null,
            array( 'votazione_id' => $this->currentVotazione->id(), 'anomaly' => 1 ),
            false,
            null
        );
        foreach( $anomalie as $anomalia )
        {
            $userId = $anomalia->attribute( 'user_id' );
            $data[$userId] = $calcoloPresenze[$userId] > 0 ? 'warning' : 'danger';
        }
        return $data;
    }

    /**
     * @return eZUser[]
     */
    protected function getPresenti()
    {
        if ( !isset( $this->data['presenti'] ) )
        {
            $seduta = $this->currentVotazione->getSeduta();
            $calcoloPresenze = $this->calcolaPresenze();
            $this->data['presenti'] = array();
            foreach ( $seduta->partecipanti() as $partecipante )
            {
                if ( $calcoloPresenze[$partecipante->id()] > 0 )
                {
                    $this->data['presenti'][$partecipante->id()] = $partecipante;
                }
            }
        }
        return $this->data['presenti'];
    }

    protected function fetchUser( $id )
    {
        if ( !isset( self::$userCache[$id] ) )
        {
            self::$userCache[$id] = eZUser::fetch( $id );
        }
        return self::$userCache[$id];
    }

    protected function calcolaPresenze()
    {
        $presenzeInVotazione = array();
        $seduta = $this->currentVotazione->getSeduta();
        if ( $this->currentVotazione->is( 'closed' ) )
        {
            $customEvents = array();
            $start = $this->currentVotazione->stringAttribute(
                Votazione::$startDateIdentifier,
                'intval'
            );
            $customEvents[] = new OpenPAConsiglioCustomDetection(
                $start,
                'inizio'
            );
            $end = $this->currentVotazione->stringAttribute(
                Votazione::$endDateIdentifier,
                'intval'
            );
            $customEvents[] = new OpenPAConsiglioCustomDetection(
                $end,
                'fine'
            );
            $presenze = new OpenPAConsiglioPresenzaHelper( $seduta, $customEvents, null, true, "votazione-{$this->currentVotazione->id()}-" );
            $data = $presenze->getData();

            foreach ( $data as $userId => $userData )
            {
                $isIn = false;
                $collect = false;
                $collectIsIn = array();
                foreach ( $userData['events'] as $index => $event )
                {
                    if ( $event['type'] == 'interval' )
                    {
                        $isIn = $event['is_in'];
                    }

                    if ( $event['type'] == 'event' && isset( $event['items'] ) )
                    {
                        foreach ( $event['items'] as $item )
                        {
                            if ( $item instanceof OpenPAConsiglioCustomDetection
                                 && $item->attribute( 'label' ) == 'inizio'
                            )
                            {
                                $collect = true;
                            }
                            if ( $item instanceof OpenPAConsiglioCustomDetection
                                 && $item->attribute( 'label' ) == 'fine'
                            )
                            {
                                $collect = false;
                            }
                        }
                    }

                    if ( $collect )
                    {
                        $collectIsIn[$index] = $isIn;
                    }
                }
                $presenzeInVotazione[$userId] = array_sum( $collectIsIn );
            }
        }
        else
        {
            $registro = $seduta->registroPresenze();
            $presenzeInVotazione = $registro['hash_user_id'];
        }
        return $presenzeInVotazione;
    }

    /**
     * @return int
     */
    protected function getPresentiCount()
    {
        return count( $this->getPresenti() );
    }

    /**
     * @return eZUser[]
     */
    protected function getAssenti()
    {
        $assenti = array();
        $presenti = $this->getPresenti();
        foreach( $this->currentVotazione->getSeduta()->partecipanti() as $partecipante )
        {
            if ( !isset( $presenti[$partecipante->id()] ) )
            {
                $assenti[] = $partecipante;
            }
        }
        return $assenti;
    }

    /**
     * @return int
     */
    protected function getAssentiCount()
    {
        $aventiDiritto = count( $this->currentVotazione->getSeduta()->partecipanti() );
        return $aventiDiritto - $this->getPresentiCount();
    }

    /**
     * @return eZUser[]
     */
    protected function getVotanti()
    {
        if ( !isset( $this->data['votanti'] ) )
        {
            $votanti = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true );
            usort( $votanti, function($a, $b){
                return strcmp( $a->stringAttribute( 'cognome' ), $b->stringAttribute( 'cognome' ) );
            });
            $this->data['votanti'] = $votanti;
        }
        return $this->data['votanti'];
    }

    /**
     * @return int
     */
    protected function getVotantiCount()
    {
        if ( !isset( $this->data['votanti_count'] ) )
        {
            $this->data['votanti_count'] = OpenPAConsiglioVoto::countVotanti( $this->currentVotazione );
        }
        return $this->data['votanti_count'];
    }

    /**
     * @return eZUser[]
     */
    protected function getNonVotanti()
    {
        $nonVotanti = array();
        $votantiIds = array();
        foreach( $this->getVotanti() as $votante )
        {
            $votantiIds[] = $votante->id();
        }
        foreach( $this->getPresenti() as $presente )
        {
            if ( !in_array( $presente->id(), $votantiIds ) )
            {
                $nonVotanti[] = $presente;
            }
        }
        return $nonVotanti;
    }

    /**
     * @return int
     */
    protected function getNonVotantiCount()
    {
        return count( $this->getNonVotanti() );
    }

    /**
     * @return eZUser[]
     */
    protected function getFavorevoli()
    {
        if ( !isset( $this->data['favorevoli'] ) )
        {
            $favorevoli = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::FAVOREVOLE );
            usort( $favorevoli, function($a, $b){
                return strcmp( $a->stringAttribute( 'cognome' ), $b->stringAttribute( 'cognome' ) );
            });
            $this->data['favorevoli'] = $favorevoli;
        }
        return $this->data['favorevoli'];
    }

    /**
     * @return int
     */
    protected function getFavorevoliCount()
    {
        if ( !isset( $this->data['favorevoli_count'] ) )
        {
            $this->data['favorevoli_count'] = OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::FAVOREVOLE );
        }
        return $this->data['favorevoli_count'];
    }

    /**
     * @return eZUser[]
     */
    protected function getContrari()
    {
        if ( !isset( $this->data['contrari'] ) )
        {
            $contrari = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::CONTRARIO );
            usort( $contrari, function($a, $b){
                return strcmp( $a->stringAttribute( 'cognome' ), $b->stringAttribute( 'cognome' ) );
            });
            $this->data['contrari'] = $contrari;
        }
        return $this->data['contrari'];
    }

    /**
     * @return int
     */
    protected function getContrariCount()
    {
        if ( !isset( $this->data['contrari_count'] ) )
        {
            $this->data['contrari_count'] = OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::CONTRARIO );
        }
        return $this->data['contrari_count'];
    }

    /**
     * @return eZUser[]
     */
    protected function getAstenuti()
    {
        if ( !isset( $this->data['astenuti'] ) )
        {
            $astenuti = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::ASTENUTO );
            usort( $astenuti, function($a, $b){
                return strcmp( $a->stringAttribute( 'cognome' ), $b->stringAttribute( 'cognome' ) );
            });
            $this->data['astenuti'] = $astenuti;
        }
        return $this->data['astenuti'];
    }

    /**
     * @return int
     */
    protected function getAstenutiCount()
    {
        if ( !isset( $this->data['astenuti_count'] ) )
        {
            $this->data['astenuti_count'] = OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::ASTENUTO );
        }
        return $this->data['astenuti_count'];
    }
}
