<?php


class PoliticoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost($data)
    {
        return new Politico($data, $this);
    }

}
