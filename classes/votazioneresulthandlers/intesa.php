<?php

class OpenPAConsiglioVotazioneResultHandlerIntesa extends OpenPAConsiglioVotazioneResultHandlerOrdinaria
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza assoluta</b>. Il quorum strutturale e funzionale sono rappresentati dalla metà più uno degli aventi diritto. <b>Coloro che non esprimono preferenza vengono conteggiati come astenuti.</b>";
    }

    protected function getQuorumFunzionale()
    {
        return $this->getQuorumStrutturale();
    }

    public function getTemplateName()
    {
        return 'non_votante_astenuto.tpl';
    }

}