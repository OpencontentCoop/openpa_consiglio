<?php

class Organo extends OpenPAConsiglioDefaultPost implements OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {

    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'componenti';

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'componenti') {
            return $this->getComponenti();
        }

        return parent::attribute($property);
    }

    public function getComponenti($asObject = true)
    {
        $idList = $this->stringAttribute('membri', function($string){
            return explode('-', $string);
        });
        if (!$asObject){
            return $idList;
        }

        $filterList = array('or');
        foreach($idList as $id){
            $filterList[] = 'meta_id_si:' . $id;
        }

        return OCEditorialStuffHandler::instance('politico')->fetchItems(array(
            'filters' => $filterList,
            'limit' => 100,
            'offset' => 0,
            'sort' => array('attr_cognome_s' => 'asc')
        ));
    }

    public function addComponente(Politico $politico)
    {
        $idList = $this->stringAttribute('membri', function($string){
            return explode('-', $string);
        });
        $idList[] = $politico->id();
        $idList = array_unique($idList);
        $this->dataMap['membri']->fromString(implode('-', $idList));
        $this->dataMap['membri']->store();
        $this->setObjectLastModified();
    }

    public function removeComponente(Politico $politico)
    {
        $idList = $this->stringAttribute('membri', function($string){
            return explode('-', $string);
        });
        $newIdList = array();
        foreach ($idList as $id) {
            if ($id != $politico->id()){
                $newIdList[] = $id;
            }
        }

        $newIdList = array_unique($newIdList);
        $this->dataMap['membri']->fromString(implode('-', $newIdList));
        $this->dataMap['membri']->store();
        $this->setObjectLastModified();
    }
}
