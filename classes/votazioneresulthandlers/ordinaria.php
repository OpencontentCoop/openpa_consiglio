<?php

class OpenPAConsiglioVotazioneResultHandlerOrdinaria extends OpenPAConsiglioVotazioneResultHandlerDefault
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza semplice</b>. Il quorum strutturale è rappresentato dalla metà più uno degli aventi diritto. Il quorum funzionale è rappresentato damaggioranza dei votanti.";
    }

    public function getTemplateName()
    {
        return 'non_votante_astenuto.tpl';
    }
}
