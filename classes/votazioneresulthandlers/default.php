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

    public function register()
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

    public function result()
    {
        return $this->getFavorevoliCount() > $this->getMaggioranzaDeiPresentiValue();
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
        $this->endTimestamp = $this->currentVotazioneDataMap[Votazione::$endDateIdentifier]->toString();
    }

    /**
     * @return eZUser[]
     */
    public function getPresenti()
    {
        $seduta = $this->currentVotazione->getSeduta();
        $intervals = array( 0, $this->endTimestamp );
        $presenti = array();
        foreach( $seduta->partecipanti() as $partecipante )
        {
            $presenza = OpenPAConsiglioPresenza::getUserInOutInSeduta( $seduta, $partecipante->id(), $intervals );
            if ( $presenza->isIn() )
            {
                $presenti[$presenza->attribute( 'user_id' )] = eZUser::fetch( intval( $presenza->attribute( 'user_id' ) ) );
            }
        }
        return $presenti;
    }

    /**
     * @return int
     */
    public function getPresentiCount()
    {
        return count( $this->getPresenti() );
    }

    public function getMaggioranzaDeiPresentiValue()
    {
        
    }

    /**
     * @return eZUser[]
     */
    public function getVotanti()
    {
        return OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true );
    }

    /**
     * @return int
     */
    public function getVotantiCount()
    {
        return OpenPAConsiglioVoto::countVotanti( $this->currentVotazione );
    }

    public function getMaggioranzaDeiVotantiValue()
    {

    }

    /**
     * @return eZUser[]
     */
    public function getFavorevoli()
    {
        return OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::FAVOREVOLE );
    }

    /**
     * @return int
     */
    public function getFavorevoliCount()
    {
        return OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::FAVOREVOLE );
    }

    /**
     * @return eZUser[]
     */
    public function getContrari()
    {
        return OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::CONTRARIO );
    }

    /**
     * @return int
     */
    public function getContrariCount()
    {
        return OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::CONTRARIO );
    }

    /**
     * @return eZUser[]
     */
    public function getAstenuti()
    {
        return OpenPAConsiglioVoto::votanti( $this->currentVotazione, true, true, OpenPAConsiglioVoto::ASTENUTO );
    }

    /**
     * @return int
     */
    public function getAstenutiCount()
    {
        return OpenPAConsiglioVoto::countVotanti( $this->currentVotazione, OpenPAConsiglioVoto::ASTENUTO );
    }
}