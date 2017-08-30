<?php


class OrganoFactory extends OpenPAConsiglioDefaultFactory
{
    public function instancePost($data)
    {
        return new Organo($data, $this);
    }
}
