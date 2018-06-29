<?php

class AllegatoFactory extends OpenPAConsiglioDefaultFactory implements OCEditorialStuffPostFileFactoryInterface
{

    public function instancePost($data)
    {
        return new Allegato($data, $this);
    }

    public function editModuleResult($parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module)
    {
        $currentPost = $this->getModuleCurrentPost($parameters, $handler, $module);
        if (!$currentPost instanceof OCEditorialStuffPostInterface) {
            return $currentPost;
        }
        $tpl = $this->editModuleResultTemplate($currentPost, $parameters, $handler, $module);

        $Result = array();
        $contentInfoArray = array('url_alias' => 'editorialstuff/dashboard');
        $contentInfoArray['persistent_variable'] = array('show_path' => true, 'site_title' => 'Dashboard');
        if (is_array($tpl->variable('persistent_variable'))) {
            $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'],
                $tpl->variable('persistent_variable'));
        }
        if (isset( $this->configuration['PersistentVariable'] ) && is_array($this->configuration['PersistentVariable'])) {
            $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'],
                $this->configuration['PersistentVariable']);
        }
        $tpl->setVariable('persistent_variable', false);
        $Result['content_info'] = $contentInfoArray;
        $Result['content'] = $tpl->fetch("design:{$this->getTemplateDirectory()}/edit.tpl");
        $Result['path'] = array();
        if ($currentPost instanceof Allegato) {
            $riferimento = $currentPost->attribute('riferimento');
            if ($riferimento instanceof OCEditorialStuffPostInterface) {
                if ($riferimento instanceof Punto) {
                    $seduta = $riferimento->attribute('seduta');
                    $sedutaFactoryConfiguration = OCEditorialStuffHandler::instance('seduta')->getFactory()->getConfiguration();
                    if ($seduta instanceof Seduta) {
                        $sedutaObject = $seduta->getObject();
                        if ($sedutaObject instanceof eZContentObject) {
                            $Result['path'][] = array(
                                'text' => isset( $sedutaFactoryConfiguration['Name'] ) ? $sedutaFactoryConfiguration['Name'] : 'Dashboard',
                                'url' => 'editorialstuff/dashboard/seduta/'
                            );
                            $Result['path'][] = array(
                                'text' => $sedutaObject->attribute('name'),
                                'url' => 'editorialstuff/edit/seduta/' . $seduta->id()
                            );
                        }
                    }
                }
                $Result['path'][] = array(
                    'text' => $riferimento->getObject()->attribute('name'),
                    'url' => 'editorialstuff/edit/' . $riferimento->getFactory()->identifier() . '/' . $riferimento->id()
                );
            }
            $Result['path'][] = array('url' => false, 'text' => $currentPost->getObject()->attribute('name'));
        }

        return $Result;
    }

    public function fileAttributeIdentifier()
    {
        return 'file';
    }

    public function handleFile($filePath, $properties, $attributes)
    {
        $response = array('errors' => array());
        $params = array_merge(
            $properties,
            array(
                'class_identifier' => $this->classIdentifier(),
                'parent_node_id' => $this->creationRepositoryNode(),
                'attributes' => $attributes
            )
        );
        $params['attributes'][$this->fileAttributeIdentifier()] = $filePath;
        if (!isset( $params['attributes']['name'] ) || trim($params['attributes']['name']) == '') {
            $name = basename($filePath);
            $parts = explode('.', $name);
            $suffix = array_pop($parts);
            $name = implode('.', $parts);
            $params['attributes']['name'] = $name;
        }
        $contentObject = eZContentFunctions::createAndPublishObject($params);
        if ($contentObject instanceof eZContentObject) {
            $allegato = OCEditorialStuffHandler::instanceFromFactory($this)->fetchByObjectId($contentObject->attribute('id'));
            if (isset( $properties['state_identifier'] )) {
                $allegato->setState($properties['state_identifier']);
            }
            $response['contentobject'] = $contentObject;
            $response['contentobject_id'] = $contentObject->attribute('id');
        } else {
            $response['errors'] = array(array('description' => 'Errore nella creazione del nuovo contenuto'));
        }

        return $response;
    }
}
