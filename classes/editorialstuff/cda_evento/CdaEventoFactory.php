<?php

class CdaEventoFactory extends OpenPAConsiglioDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function instancePost($data)
    {
        return new CdaEvento($data, $this);
    }
}