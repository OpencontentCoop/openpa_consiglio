<?php

class OsservazioneFactory extends AllegatoFactory
{
    public function instancePost( $data )
    {
        return new Osservazione( $data, $this );
    }

    public function fileAttributeIdentifier()
    {
        return 'allegato';
    }

}