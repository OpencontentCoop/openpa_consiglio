<?php

class OpenPAConsiglioVotazioneResultHandlerOrdinariaAlternativa extends OpenPAConsiglioVotazioneResultHandlerOrdinaria
{
    public function getDescription()
    {
        return "Votazione a <b>maggioranza semplice</b>. Il quorum strutturale è rappresentato dalla metà più uno degli aventi diritto. Il quorum funzionale è rappresentato dalla metà più uno dei votanti. <b>Coloro che non esprimono preferenza vengono conteggiati come assenti</b>";
    }

    protected function getQuorumFunzionale()
    {
        $metaVotanti = ceil( $this->getVotantiCount() / 2 );
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
        if ( $this->currentVotazione->isBefore( 'in_progress', true ) )
            return ( $this->getPresentiCount() - $this->getNonVotantiCount() ) > $this->getQuorumStrutturale();
        else
            return $this->getVotantiCount() > $this->getQuorumStrutturale();
    }

    public function getTemplateName()
    {
        return 'non_votante_assente.tpl';
    }
}