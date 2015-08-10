<?php


class InvitatoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost( $data )
    {
        return new Invitato( $data, $this );
    }
}