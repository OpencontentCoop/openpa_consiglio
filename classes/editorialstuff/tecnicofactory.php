<?php


class TecnicoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost( $data )
    {
        return new Tecnico( $data, $this );
    }
}