<?php


class PropostaFactory extends OpenPAConsiglioDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function instancePost($data)
    {
        return new Proposta($data, $this);
    }
}