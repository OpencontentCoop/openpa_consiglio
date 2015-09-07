<?php

class OpenPAConsiglioVotazioneResultHandlerOrdinaria extends OpenPAConsiglioVotazioneResultHandlerDefault
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza semplice</b>. Il quorum strutturale è rappresentato dalla metà più uno degli aventi diritto. Il quorum funzionale è rappresentato dalla metà più uno dei votanti. <b>Coloro che non esprimono preferenza vengono conteggiati come astenuti.</b>";
    }

    public function getTemplateName()
    {
        return 'non_votante_astenuto.tpl';
    }
}