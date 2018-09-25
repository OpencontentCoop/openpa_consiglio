<?php

class CdaDocumentoFactory extends OpenPAConsiglioDefaultFactory
{
    use OpenPAConsiglioConfigurableTrait;

    public function instancePost($data)
    {
        return new CdaDocumento($data, $this);
    }
}