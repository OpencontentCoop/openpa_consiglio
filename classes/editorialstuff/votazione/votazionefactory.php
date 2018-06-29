<?php

class VotazioneFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost($data)
    {
        return new Votazione($data, $this);
    }

}
