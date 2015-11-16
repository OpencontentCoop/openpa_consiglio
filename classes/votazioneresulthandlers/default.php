<?php

class OpenPAConsiglioVotazioneResultHandlerDefault extends OpenPATempletizable implements OpenPAConsiglioVotazioneResultHandlerInterface
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

    public function __construct( $data = null )
    {
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
        );        
        return $this;
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
            $presenti = array();
            foreach ( $seduta->partecipanti() as $partecipante )
            {
                if ( $calcoloPresenze[$partecipante->id()] > 0 )
                {
                    $presenti[$partecipante->id()] = $this->fetchUser(
                        intval( $partecipante->id() )
                    );
                }
            }

            $this->data['presenti'] = $presenti;
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
        $customEvents = array();
        $start = $this->currentVotazione->stringAttribute( Votazione::$startDateIdentifier, 'intval' );
        $customEvents[] = new OpenPAConsiglioCustomDetection(
            $start,
            'inizio'
        );
        $end = $this->currentVotazione->stringAttribute( Votazione::$endDateIdentifier, 'intval' );
        $customEvents[] = new OpenPAConsiglioCustomDetection(
            $end,
            'fine'
        );
        $seduta = $this->currentVotazione->getSeduta();
        $presenze = new OpenPAConsiglioPresenzaHelper( $seduta, $customEvents );
        $data = $presenze->getData();

        foreach( $data as $userId => $userData )
        {
            $isIn = false;
            $collect = false;
            $collectIsIn = array();
            foreach( $userData['events'] as $index => $event )
            {
                if ( $event['type'] == 'interval' )
                {
                    $isIn = $event['is_in'];
                }

                if ( $event['type'] == 'event' && isset( $event['items'] ) )
                {
                    foreach( $event['items'] as $item )
                    {
                        if ( $item instanceof OpenPAConsiglioCustomDetection && $item->attribute( 'label' ) == 'inizio' )
                        {
                            $collect = true;
                        }
                        if ( $item instanceof OpenPAConsiglioCustomDetection && $item->attribute( 'label' ) == 'fine' )
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
                $assenti[] = $this->fetchUser( $partecipante->id() );
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
            $this->data['votanti'] = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true );
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
            $this->data['favorevoli'] = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::FAVOREVOLE );
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
            $this->data['contrari'] = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::CONTRARIO );
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
            $this->data['astenuti'] = OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::ASTENUTO );
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
