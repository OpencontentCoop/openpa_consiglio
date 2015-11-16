<?php

class ReferenteLocaleFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost( $data )
    {
        return new ReferenteLocale( $data, $this );
    }
}