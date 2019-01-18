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

    public function onCreate(){
        $this->createSection();
        $this->assignRoleWithSectionLimitationToMembers();
    }

    public function onUpdate(){
        $this->createSection();
        $this->assignRoleWithSectionLimitationToMembers();
    }

    public function getSection()
    {
        $identifier = 'openpaconsiglio_' . $this->id();
        return eZSection::fetchByIdentifier($identifier);
    }

    private function createSection()
    {
        $identifier = 'openpaconsiglio_' . $this->id();
        $section = eZSection::fetchByIdentifier($identifier);
        if (!$section instanceof eZSection){
            $section = new eZSection(array());
            $section->setAttribute('name', 'OpenConsiglio - ' . $this->getObject()->attribute('name'));
            $section->setAttribute('identifier', $identifier);
            $section->setAttribute('navigation_part_identifier', 'ezcontentnavigationpart');
            $section->store();
        }

        return $section;
    }

    private function assignRoleWithSectionLimitationToMembers()
    {
        foreach ($this->getComponenti(false) as $componente) {
            if ($componente){
                $this->assignRoleWithSectionLimitationToMember($componente);
            }
        }       
    }

    private function assignRoleWithSectionLimitationToMember($memberId)
    {
        $role = eZRole::fetchByName(OpenPAConsiglioRoles::POLITICO_ORGANO); 
        $section = $this->getSection();
        if ($role instanceof eZRole && $section instanceof eZSection){
            $role->assignToUser($memberId, 'section', $section->attribute('id'));  
            eZRole::expireCache();          
        }
    }

    private function removeRoleWithSectionLimitationFromMember($memberId)
    {
        $role = eZRole::fetchByName(OpenPAConsiglioRoles::POLITICO_ORGANO); 
        $section = $this->getSection();
        if ($role instanceof eZRole && $section instanceof eZSection){
            $db = eZDB::instance();
            $userID = (int) $memberId;
            $roleID = (int) $role->attribute('id');
            $limitValue = $section->attribute('id');
            $query = "DELETE FROM ezuser_role WHERE role_id='$roleID' AND contentobject_id='$userID' AND limit_identifier='Section' AND limit_value='$limitValue'";
            $db->query($query);
            eZRole::expireCache();
        }
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'componenti';
        $attributes[] = 'componenti_non_consiglieri_id_list';

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'componenti') {
            return $this->getComponenti();
        }

        if ($property == 'componenti_non_consiglieri_id_list') {
            return $this->getIdListComponentiNonConsiglieri();
        }

        return parent::attribute($property);
    }

    public function getIdListComponentiNonConsiglieri()
    {
        $idList = $this->stringAttribute('membri_non_consiglieri', function($string){
            return explode('-', $string);
        });
        
        return $idList;        
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
        $this->assignRoleWithSectionLimitationToMember($politico->id());
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
        $this->removeRoleWithSectionLimitationFromMember($politico->id());
        $this->setObjectLastModified();
    }
}
