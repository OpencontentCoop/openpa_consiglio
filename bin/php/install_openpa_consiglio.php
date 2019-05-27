<?php
require_once 'autoload.php';

$script = eZScript::instance(array('description' => ("Install OpenPA Consiglio"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions('[parent_node:][roles][classes][tree][schema][all]',
    '',
    array(
        'parent_node' => 'Root Parent Node'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$roles = $options['roles'];
$classes = $options['classes'];
$tree = $options['tree'];
$schema = $options['schema'];
if ($options['all']){
    $roles = true;
    $classes = true;
    $tree = true;
    $schema = true;
}
try {
    
    $configuration = OpenPAConsiglioConfiguration::instance();

    if ($schema){
        $dbUser = eZINI::instance()->variable('DatabaseSettings', 'User');
        $dbName = eZINI::instance()->variable('DatabaseSettings', 'Database');
        $mysqlCommand = "mysql -u {$dbUser} -p {$dbName} < extension/openpa_consiglio/sql/mysql/schema.sql";
        $cli->warning('Esegui (se non l\'hai ancora fatto) ', false);
        $cli->notice($mysqlCommand);
        $cli->warning();
    }

    if ($classes){
        foreach ($configuration->getAvailableClasses() as $identifier) {
            $remoteUrl = eZSys::rootDir() . '/extension/openpa_consiglio/packages/classes/' . $identifier;
            $cli->warning('Sincronizzo classe ' . $identifier . ' con ' . $remoteUrl);
            $tools = new OCClassTools($identifier, true, array(), $remoteUrl); // creo se non esiste
            $tools->sync(true, true); // forzo e rimuovo attributi in più
        }
        $cli->warning();
    }

    if ($tree){
        $parentNodeId = $options['parent_node'] ? $options['parent_node'] : eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode');
        foreach ($configuration->getContainerDashboards() as $repositoryIdentifier => $containerClassIdentifier) {
            if ($configuration->getRepositoryRootNodeId($repositoryIdentifier) == null) {
                $cli->warning('Creo root node per ' . $repositoryIdentifier);
                $remoteId = $configuration->getRepositoryRootRemoteId($repositoryIdentifier);

                $params = array(
                    'parent_node_id' => $parentNodeId,
                    'remote_id' => $remoteId,
                    'class_identifier' => $containerClassIdentifier,
                    'attributes' => array(
                        'name' => $repositoryIdentifier
                    )
                );
                /** @var eZContentObject $rootObject */
                $rootObject = eZContentFunctions::createAndPublishObject($params);
                if (!$rootObject instanceof eZContentObject) {
                    throw new Exception('Fallita la creazione del root node di ' . $repositoryIdentifier);
                }
            }else{
                $cli->output('Trovata root node per ' . $repositoryIdentifier);
            }
        }
        $cli->warning();  
    }  

    if ($roles){
        $roleHelper = new OpenPAConsiglioRoles();
        foreach ($roleHelper->getRoleNames() as $roleName) {
            $cli->warning('Creo ruolo ' . $roleName);
            $roleHelper->createRoleIfNeeded($roleName);
        }
    }
    $script->shutdown();
} catch (Exception $e) {
    $script->shutdown($e->getCode(), $e->getMessage());
}


