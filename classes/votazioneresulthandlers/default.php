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

    public function __construct( $data = null )
    {
    }

    public function getDescription()
    {
        return "Votazione a maggioranza semplice. Il quorum strutturale è rappresentato dalla metà più uno degli aventi diritto. Il quorum funzionale è rappresentato dalla metà più uno dei votanti. Coloro che non esprimono preferenza vengono conteggiati come astenuti";
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
        return $this->getFavorevoliCount() > $this->getQuorumFunzionale();
    }

    protected function getQuorumFunzionale()
    {
        $metaVotanti = ceil( ( $this->getVotantiCount() + $this->getNonVotantiCount() ) / 2 );
        return $metaVotanti + 1;
    }

    protected function getQuorumStrutturale()
    {
        if ( !isset( $this->data['quorum_strutturale'] ) )
        {
            $aventiDiritto = count( $this->currentVotazione->getSeduta()->partecipanti() );
            $this->data['quorum_strutturale'] = ceil( $aventiDiritto / 2 ) + 1;
        }
        return $this->data['quorum_strutturale'];
    }

    public function isValid()
    {
        if ( $this->currentVotazione->is( 'pending' ) )
            return $this->getPresentiCount() > $this->getQuorumStrutturale();
        else
            return ( $this->getVotantiCount() + $this->getNonVotantiCount() ) > $this->getQuorumStrutturale();
    }

    /**
     * @param Votazione $votazione
     *
     * @return OpenPAConsiglioVotazioneResultHandlerInterface
     */
    public function setCurrentVotazione( Votazione $votazione )
    {
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
            $intervals = array( 0, $this->endTimestamp );
            $presenti = array();
            foreach ( $seduta->partecipanti() as $partecipante )
            {
                $presenza = OpenPAConsiglioPresenza::getUserInOutInSeduta(
                    $seduta,
                    $partecipante->id(),
                    $intervals
                );
                if ( $presenza instanceof OpenPAConsiglioPresenza && $presenza->isIn() )
                {
                    $presenti[$presenza->attribute( 'user_id' )] = eZUser::fetch(
                        intval( $presenza->attribute( 'user_id' ) )
                    );
                }
            }

            $this->data['presenti'] = $presenti;
        }
        return $this->data['presenti'];
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
        $presentiIds = array();
        foreach( $this->getPresenti() as $presente )
        {
            $presentiIds[] = $presente->id();
        }
        foreach( $this->currentVotazione->getSeduta()->partecipanti() as $partecipante )
        {
            if ( !in_array( $partecipante->id(), $presentiIds ) )
            {
                $assenti[] = eZUser::fetch( $partecipante->id() );
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
        return $this->getPresentiCount() - $this->getVotantiCount();
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