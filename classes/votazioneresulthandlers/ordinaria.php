<?php

class OpenPAConsiglioVotazioneResultHandlerOrdinaria extends OpenPAConsiglioVotazioneResultHandlerDefault
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza semplice</b>. Il quorum costitutivo è rappresentato dalla metà più uno degli aventi diritto. Il quorum deliberativo è rappresentato dalla maggioranza dei votanti.";
    }

    public function getTemplateName()
    {
        return 'non_votante_astenuto.tpl';
    }
}
