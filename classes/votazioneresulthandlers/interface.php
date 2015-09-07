<?php

interface OpenPAConsiglioVotazioneResultHandlerInterface
{
    /**
     * @param Votazione $votazione
     *
     * @return OpenPAConsiglioVotazioneResultHandlerInterface
     */
    public function setCurrentVotazione( Votazione $votazione );

    /**
     * @return OpenPAConsiglioVotazioneResultHandlerInterface
     */
    public function register();

    /**
     * @return array
     */
    public function result();

    /**
     * @return eZUser[]
     */
    public function getPresenti();

    /**
     * @return int
     */
    public function getPresentiCount();

    /**
     * @return eZUser[]
     */
    public function getVotanti();

    /**
     * @return int
     */
    public function getVotantiCount();

    /**
     * @return eZUser[]
     */
    public function getFavorevoli();

    /**
     * @return int
     */
    public function getFavorevoliCount();

    /**
     * @return eZUser[]
     */
    public function getContrari();

    /**
     * @return int
     */
    public function getContrariCount();


    /**
     * @return eZUser[]
     */
    public function getAstenuti();

    /**
     * @return int
     */
    public function getAstenutiCount();
}