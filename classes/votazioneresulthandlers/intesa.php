<?php

class OpenPAConsiglioVotazioneResultHandlerIntesa extends OpenPAConsiglioVotazioneResultHandlerOrdinaria
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza assoluta</b>. Il quorum costitutivo e deliberativo sono rappresentati dalla maggioranza degli aventi diritt";
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
