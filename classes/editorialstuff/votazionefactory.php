<?php

class VotazioneFactory extends OCEditorialStuffPostDefaultFactory
{
    public function instancePost( $data )
    {
        return new Votazione( $data, $this );
    }

}