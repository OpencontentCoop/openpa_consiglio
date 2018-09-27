<?php

class Materia extends OpenPAConsiglioDefaultPost
{
    const STATE_GROUP_IDENTIFIER = 'materia';

    const STATE_PREFIX = 'materia_';

    private $stateGroup;

    private $state;

    private $role;

    public function onCreate()
    {
        $this->assignState($this->getObject());
        $this->assignStateRole();

        eZSearch::addObject($this->getObject());
    }

    public function onUpdate()
    {
        $this->assignState($this->getObject());
        $this->assignStateRole();

        eZSearch::addObject($this->getObject());
    }

    public function assignState(eZContentObject $object)
    {
        $object->assignState($this->getState());
    }

    private function getState()
    {
        if ($this->state === null) {
            $stateIdentifier = self::STATE_PREFIX . $this->id();
            $stateObject = $this->getStateGroup()->stateByIdentifier($stateIdentifier);
            if (!$stateObject instanceof eZContentObjectState) {
                $stateObject = $this->createState();
            }

            $this->state = $stateObject;
        }

        return $this->state;
    }

    private function getStateGroup()
    {
        if ($this->stateGroup === null) {
            $stateGroup = eZContentObjectStateGroup::fetchByIdentifier(self::STATE_GROUP_IDENTIFIER);
            if (!$stateGroup instanceof eZContentObjectStateGroup) {
                $stateGroup = new eZContentObjectStateGroup();
                $stateGroup->setAttribute('identifier', self::STATE_GROUP_IDENTIFIER);
                $stateGroup->setAttribute('default_language_id', 2);

                /** @var eZContentObjectStateLanguage[] $translations */
                $translations = $stateGroup->allTranslations();
                foreach ($translations as $translation) {
                    $translation->setAttribute('name', ucfirst(self::STATE_GROUP_IDENTIFIER));
                    $translation->setAttribute('description', ucfirst(self::STATE_GROUP_IDENTIFIER));
                }

                $messages = array();
                $isValid = $stateGroup->isValid($messages);
                if (!$isValid) {
                    throw new Exception(implode(',', $messages));
                }
                $stateGroup->store();

                $defaultMateriaIdentifier = self::STATE_PREFIX . '0';
                $defaultMateriaName = 'Nessuna materia';

                $stateObject = $stateGroup->stateByIdentifier($defaultMateriaIdentifier);
                if (!$stateObject instanceof eZContentObjectState) {
                    $stateObject = $stateGroup->newState($defaultMateriaIdentifier);
                    $stateObject->setAttribute('default_language_id', 2);
                    /** @var eZContentObjectStateLanguage[] $stateTranslations */
                    $stateTranslations = $stateObject->allTranslations();
                    foreach ($stateTranslations as $translation) {
                        $translation->setAttribute('name', $defaultMateriaName);
                        $translation->setAttribute('description', $defaultMateriaName);
                    }
                    $messages = array();
                    $isValid = $stateObject->isValid($messages);
                    if (!$isValid) {
                        throw new Exception(implode(',', $messages));
                    }
                    $stateObject->store();
                }
            }

            $this->stateGroup = $stateGroup;
        }

        return $this->stateGroup;
    }

    private function createState()
    {
        $stateIdentifier = self::STATE_PREFIX . $this->id();
        $stateName = "Materia #" . $this->id();

        $stateGroup = $this->getStateGroup();
        $stateObject = $stateGroup->newState($stateIdentifier);
        $stateObject->setAttribute('default_language_id', 2);
        /** @var eZContentObjectStateLanguage[] $stateTranslations */
        $stateTranslations = $stateObject->allTranslations();
        foreach ($stateTranslations as $translation) {
            $translation->setAttribute('name', $stateName);
            $translation->setAttribute('description', $stateName);
        }
        $messages = array();
        $isValid = $stateObject->isValid($messages);
        if (!$isValid) {
            throw new Exception(implode(',', $messages));
        }
        $stateObject->store();

        return $stateObject;
    }

    private function getStateRole()
    {
        if ($this->role === null) {
            $roleName = OpenPAConsiglioRoles::RESPONSABILE_MATERIA_PREFIX . $this->id();
            $role = eZRole::fetchByName($roleName);
            if (!$role instanceof eZRole) {
                $role = eZRole::create($roleName);
                $role->store();
                $policies = array(
                    array(
                        'ModuleName' => 'content',
                        'FunctionName' => 'read',
                        'Limitation' => array(
                            // 'Class' => array(
                            //     eZContentClass::classIDByIdentifier(OCEditorialStuffHandler::instance('punto')->getFactory()->classIdentifier())
                            // ),
                            'StateGroup_' . $this->getStateGroup()->attribute('identifier') => array(
                                $this->getState()->attribute('id'),
                            )
                        )
                    ),
                );
                foreach ($policies as $policy) {
                    $role->appendPolicy($policy['ModuleName'], $policy['FunctionName'], $policy['Limitation']);
                }
            }

            $this->role = $role;
        }

        return $this->role;
    }

    private function assignStateRole()
    {
        $role = $this->getStateRole();

        // rimozione delle assegnazioni correnti
        $db = eZDB::instance();
        $query = "DELETE FROM ezuser_role WHERE role_id='$role->ID'";
        $db->query($query);

        if (isset($this->dataMap['referente_tecnico']) && $this->dataMap['referente_tecnico']->hasContent()) {
            $idList = explode('-', $this->dataMap['referente_tecnico']->toString());
            foreach ($idList as $id) {
                $role->assignToUser($id);
            }
        }

        eZRole::expireCache();
    }
}