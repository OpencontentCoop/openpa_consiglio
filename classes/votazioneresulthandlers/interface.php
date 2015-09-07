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
     * @return string
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return OpenPAConsiglioVotazioneResultHandlerInterface
     */
    public function store();

    /**
     * @return bool
     */
    public function getResult();

    /**
     * @return string
     */
    public function getTemplateName();


}