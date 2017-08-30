<?php

trait OpenPAConsiglioConfigurableTrait
{
    private $configurationProvider;

    private $settingsProvider;

    /**
     * @return OpenPAConsiglioConfiguration
     */
    public function getConfigurationProvider()
    {
        return $this->configurationProvider ? $this->configurationProvider : OpenPAConsiglioConfiguration::instance();
    }

    /**
     * @param mixed $configurationProvider
     */
    public function setConfigurationProvider($configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @return OpenPAConsiglioSettings
     */
    public function getSettingsProvider()
    {
        return $this->settingsProvider ? $this->settingsProvider : OpenPAConsiglioSettings::instance();
    }

    /**
     * @param mixed $settingsProvider
     */
    public function setSettingsProvider($settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function overrideConfiguration(&$configuration)
    {
        $needRootFactories = $this->getConfigurationProvider()->getContainerDashboards();
        $factoryIdentifier = $configuration['identifier'];
        if(empty($configuration['CreationRepositoryNode']) && in_array($factoryIdentifier, $needRootFactories)) {
            $configuration['CreationRepositoryNode'] = $this->getConfigurationProvider()->getRepositoryRootNodeId($factoryIdentifier);
        }
        if(empty($configuration['RepositoryNodes'])) {
            if (in_array($factoryIdentifier, $needRootFactories)){
                $configuration['RepositoryNodes'] = array($configuration['CreationRepositoryNode']);
            }else{
                $configuration['RepositoryNodes'] = array(1);
            }
        }
        $configuration['PersistentVariable'] = $this->getConfigurationProvider()->getRepositoryPersistentVariable($factoryIdentifier);
    }
}
