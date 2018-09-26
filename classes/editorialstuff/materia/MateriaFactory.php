<?php

class MateriaFactory extends OpenPAConsiglioDefaultFactory
{
	public function instancePost($data)
    {
        return new Materia($data, $this);
    }
}