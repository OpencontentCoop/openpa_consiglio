<?php

trait OpenPAConsiglioDiffTrait
{
    /**
     * @return eZContentObject
     */
    abstract public function getObject();

    protected function diff($version)
    {
        $diff = array();
        if ($version >= 1 && $version != $this->getObject()->attribute('current_version')) {
            $current = $this->getObject()->currentVersion();
            $version = $this->getObject()->version($version);

            if ($version instanceof eZContentObjectVersion && $current instanceof eZContentObjectVersion) {
                if ($version->attribute('version') != $current->attribute('version')) {
                    /** @var eZContentObjectAttribute[] $oldAttributes */
                    $oldAttributes = $version->dataMap();
                    /** @var eZContentObjectAttribute[] $newAttributes */
                    $newAttributes = $current->dataMap();
                    foreach ($oldAttributes as $oldAttribute) {
                        $newAttribute = $newAttributes[$oldAttribute->attribute('contentclass_attribute_identifier')];
                        if ($oldAttribute->toString() !== $newAttribute->toString()) {
                            $diff[$oldAttribute->attribute('contentclass_attribute_identifier')] = array(
                                'old' => $oldAttribute,
                                'new' => $newAttribute
                            );
                        }
                    }
                }
            }
        }

        return $diff;
    }
}
