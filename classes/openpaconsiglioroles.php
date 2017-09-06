<?php


class OpenPAConsiglioRoles
{
    const ANONIMO = 'OpenConsiglio - Anonimo';
    const AREA_COLLABORATIVA = 'OpenConsiglio - Area Collaborativa';
    const AREA_COLLABORATIVA_POLITICO = 'OpenConsiglio - Area Collaborativa - Politico';
    const POLITICO = 'OpenConsiglio - Politico';
    const SEGRETERIA = 'OpenConsiglio - Segreteria';
    const TECNICO = 'OpenConsiglio - Tecnico';

    /**
     * @return array
     */
    public function getRoleNames()
    {
        return array(
            self::ANONIMO,
            self::AREA_COLLABORATIVA,
            self::AREA_COLLABORATIVA_POLITICO,
            self::POLITICO,
            self::SEGRETERIA,
            //self::TECNICO
        );
    }

    public function getPolicies($roleName)
    {
        switch ($roleName) {
            case self::ANONIMO: {
                return $this->getAnonimoPolicies();
            }
                break;

            case self::POLITICO: {
                return $this->getPoliticoPolicies();
            }
                break;

            case self::SEGRETERIA: {
                return $this->getSegreteriaPolicies();
            }
                break;

            case self::AREA_COLLABORATIVA: {
                return $this->getAreaCollaborativaPolicies();
            }
                break;

            case self::AREA_COLLABORATIVA_POLITICO: {
                return $this->getAreaCollaborativaPoliticoPolicies();
            }
                break;

            default:
                throw new Exception("Role $roleName not defined");
        }
    }

    private function getFactory($identifier)
    {
        return OCEditorialStuffHandler::instance($identifier)->getFactory();
    }

    private function getAnonimoPolicies()
    {
        return array(
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('politico')->classIdentifier())
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('punto')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('punto')->stateGroupIdentifier() => array(
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.published']->attribute('id'),
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.in_progress']->attribute('id'),
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.closed']->attribute('id')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('seduta')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('seduta')->stateGroupIdentifier() => array(
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.in_progress']->attribute('id'),
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.closed']->attribute('id'),
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.sent']->attribute('id')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('allegati_seduta')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('allegati_seduta')->stateGroupIdentifier() => array(
                        $this->getFactory('allegati_seduta')->states()[$this->getFactory('allegati_seduta')->stateGroupIdentifier() . '.pubblico']->attribute('id')
                    )
                )
            ),
        );
    }

    private function getPoliticoPolicies()
    {
        return array(
            array(
                'ModuleName' => 'consiglio',
                'FunctionName' => 'use',
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('rendiconto_spese'),
                    ),
                    'Subtree' => array(
                        OpenPAConsiglioConfiguration::instance()->getRepositoryRootNodePathString('rendiconto_spese')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('osservazioni')->classIdentifier()),
                    ),
                    'Subtree' => array(
                        OpenPAConsiglioConfiguration::instance()->getRepositoryRootNodePathString('osservazioni')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'edit',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('politico')->classIdentifier()),
                    ),
                    'Owner' => 1
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'edit',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('rendiconto_spese'),
                    ),
                    'Owner' => 1
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('punto')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('punto')->stateGroupIdentifier() => array(
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.published']->attribute('id'),
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.in_progress']->attribute('id'),
                        $this->getFactory('punto')->states()[$this->getFactory('punto')->stateGroupIdentifier() . '.closed']->attribute('id')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('osservazioni')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('osservazioni')->stateGroupIdentifier() => array(
                        $this->getFactory('osservazioni')->states()[$this->getFactory('osservazioni')->stateGroupIdentifier() . '.consiglieri']->attribute('id'),
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('osservazioni')->classIdentifier())
                    ),
                    'Owner' => 1
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('allegati_seduta')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('allegati_seduta')->stateGroupIdentifier() => array(
                        $this->getFactory('allegati_seduta')->states()[$this->getFactory('allegati_seduta')->stateGroupIdentifier() . '.consiglieri']->attribute('id')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('seduta')->classIdentifier())
                    ),
                    'StateGroup_' . $this->getFactory('seduta')->stateGroupIdentifier() => array(
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.pending']->attribute('id'),
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.in_progress']->attribute('id'),
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.closed']->attribute('id'),
                        $this->getFactory('seduta')->states()[$this->getFactory('seduta')->stateGroupIdentifier() . '.sent']->attribute('id')
                    )
                )
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => 'dashboard',
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => 'file',
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => 'full_dashboard',
            ),
            array(
                'ModuleName' => 'state',
                'FunctionName' => 'assign',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($this->getFactory('osservazioni')->classIdentifier())
                    ),
                    'Owner' => 1,
                    'StateGroup_' . $this->getFactory('osservazioni')->stateGroupIdentifier() => array(
                        $this->getFactory('osservazioni')->states()[$this->getFactory('osservazioni')->stateGroupIdentifier() . '.consiglieri']->attribute('id'),
                        $this->getFactory('osservazioni')->states()[$this->getFactory('osservazioni')->stateGroupIdentifier() . '.referenti']->attribute('id'),
                    ),
                    'NewState' => array(
                        $this->getFactory('osservazioni')->states()[$this->getFactory('osservazioni')->stateGroupIdentifier() . '.consiglieri']->attribute('id'),
                        $this->getFactory('osservazioni')->states()[$this->getFactory('osservazioni')->stateGroupIdentifier() . '.referenti']->attribute('id'),
                    )
                )
            ),

        );
    }

    private function getSegreteriaPolicies()
    {
        $data =  array(
            array(
                'ModuleName' => 'add',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'consiglio',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'edit',
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'ezflow',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'ezjscore',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'ezoe',
                'FunctionName' => '*',
            ),
            array(
                'ModuleName' => 'ocbtools',
                'FunctionName' => 'editor_tools',
            ),
            array(
                'ModuleName' => 'state',
                'FunctionName' => 'assign', //@todo
            ),
            array(
                'ModuleName' => 'websitetoolbar',
                'FunctionName' => '*',
            ),
        );

        $configuration = OpenPAConsiglioConfiguration::instance();

        $classes = array_fill_keys($configuration->getAvailableClasses(), true);

        foreach ($configuration->getContainerDashboards() as $repositoryIdentifier) {
            try {
                $data[] = array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier($this->getFactory($repositoryIdentifier)->classIdentifier()),
                        ),
                        'Subtree' => array(
                            $configuration->getRepositoryRootNodePathString($repositoryIdentifier)
                        )
                    )
                );
                unset($classes[$this->getFactory($repositoryIdentifier)->classIdentifier()]);
            }catch(Exception $e){

            }
        }

        $data[] = array(
            'ModuleName' => 'content',
            'FunctionName' => 'create',
            'Limitation' => array(
                'Class' => array(
                    eZContentClass::classIDByIdentifier('rendiconto_spese'),
                ),
                'Subtree' => array(
                    $configuration->getRepositoryRootNodePathString('rendiconto_spese')
                )
            )
        );
        unset($classes['rendiconto_spese']);

        foreach(array_keys($classes) as $class){
            $data[] = array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier($class),
                    ),
                )
            );
        }

        return $data;
    }

    private function getAreaCollaborativaPolicies()
    {
        return array(
            array(
                'ModuleName' => 'consiglio',
                'FunctionName' => 'collaboration',
                'Limitation' => array()
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => 'full_dashboard',
                'Limitation' => array()
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_area'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_comment'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_file'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_room'),
                        eZContentClass::classIDByIdentifier('user')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_comment'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_file')
                    ),
                    'ParentClass' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_room')
                    )
                )
            ),
        );
    }

    private function getAreaCollaborativaPoliticoPolicies()
    {
        return array(
            array(
                'ModuleName' => 'consiglio',
                'FunctionName' => 'collaboration',
                'Limitation' => array()
            ),
            array(
                'ModuleName' => 'editorialstuff',
                'FunctionName' => 'full_dashboard',
                'Limitation' => array()
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_area'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_comment'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_room'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_file'),
                        eZContentClass::classIDByIdentifier('user')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_comment'),
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_file')
                    ),
                    'ParentClass' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_room')
                    )
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_room')
                    ),
                    'ParentClass' => array(
                        eZContentClass::classIDByIdentifier('openpa_consiglio_collaboration_area')
                    )
                )
            )
        );
    }

    /**
     * @param $roleName
     * @return eZRole
     * @throws Exception
     */
    public function createRoleIfNeeded($roleName)
    {
        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();
            $policies = $this->getPolicies($roleName);
            foreach ($policies as $policy) {
                $role->appendPolicy($policy['ModuleName'], $policy['FunctionName'], $policy['Limitation']);
            }
        }

        return $role;
    }
}