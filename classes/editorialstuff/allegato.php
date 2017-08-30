<?php

class Allegato extends OCEditorialStuffPost
{
    use OpenPAConsiglioDiffTrait;

    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $firstPost;

    protected $canShare;

    protected $isShared;

    protected $sharedUrl;

    public function onChangeState(eZContentObjectState $beforeState, eZContentObjectState $afterState)
    {
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'riferimento';
        $attributes[] = 'sostituito';
        $attributes[] = 'can_share';
        $attributes[] = 'is_shared';
        $attributes[] = 'shared_url';

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'riferimento') {
            return $this->getFirstReverseRelatedPost();
        }

        if ($property == 'sostituito') {
            return $this->isSostituito();
        }

        if ($property == 'can_share') {
            return $this->canShare();
        }

        if ($property == 'is_shared') {
            return $this->isShared();
        }

        if ($property == 'shared_url') {
            return $this->sharedUrl();
        }

        return parent::attribute($property);
    }

    protected function canShare()
    {
        if ($this->canShare === null) {
            $this->canShare = false;
            $post = $this->getFirstReverseRelatedPost();
            if ($post instanceof Punto) {
                $this->canShare = $post->canShare() && $post->isShared();
            }
        }

        return $this->canShare;
    }

    protected function isShared()
    {
        if ($this->isShared === null) {
            $this->isShared = false;
            $post = $this->getFirstReverseRelatedPost();
            if ($post instanceof Punto) {
                if ($post->isShared()) {
                    $aree = AreaCollaborativaFactory::fetchByPolitico(eZUser::currentUser());
                    foreach ($aree as $area) {
                        $this->isShared = $area->fetchCountFilesByRelation($this->id()) > 0;
                        break;
                    }
                }
            }
        }

        return $this->isShared;
    }

    protected function sharedUrl()
    {
        if ($this->sharedUrl === null) {
            $this->sharedUrl = false;
            $post = $this->getFirstReverseRelatedPost();
            if ($post instanceof Punto) {
                $this->sharedUrl = $post->sharedUrl();
            }
        }

        return $this->sharedUrl;
    }

    public function share()
    {
        $file = $this->attributeFile();
        $fileInfo = $file->storedFileInformation(
            $file->attribute('object'),
            $file->attribute('version'),
            $file->attribute('language_code')
        );
        if (isset( $fileInfo['filepath'] )) {
            $post = $this->getFirstReverseRelatedPost();
            if ($post instanceof Punto) {
                if ($this->canShare()) {
                    try {
                        $aree = AreaCollaborativaFactory::fetchByPolitico(eZUser::currentUser());
                        foreach ($aree as $area) {
                            $helper = new OpenPAConsiglioCollaborationHelper($area);
                            $rooms = $area->fetchRoomsByRelation($post->id());
                            foreach ($rooms as $room) {
                                return $helper->addFile(
                                    $room->attribute('node_id'),
                                    $fileInfo['filepath'], //@todo make it cluster safe
                                    $this->object->attribute('name'),
                                    $this->id(),
                                    false
                                );
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        return false;
    }

    public function isSostituito()
    {
        if (isset( $this->dataMap['sostituito'] )) {
            return (bool)$this->dataMap['sostituito']->attribute('data_int');
        }

        return false;
    }

    public function onUpdate()
    {
        $post = $this->getFirstReverseRelatedPost();
        if ($post instanceof Punto) {
            $lastVersion = $this->getObject()->attribute('current_version') - 1;
            $diff = $this->diff($lastVersion);
            if (isset( $diff['sostituito'] )) {
                $post->createNotificationEvent('change_allegati', $this);
            }
        }
    }

    protected function getFirstReverseRelatedPost()
    {
        if ($this->firstPost == null) {
            $reverseObjects = $this->getObject()->reverseRelatedObjectList(
                false,
                0,
                false,
                array('AllRelations' => true, 'AsObject' => false)
            );
            foreach (OCEditorialStuffHandler::instances() as $instance) {
                foreach ($reverseObjects as $reverseObject) {
                    if ($reverseObject['contentclass_identifier'] == $instance->getFactory()->classIdentifier()) {
                        try {
                            $this->firstPost = $instance->fetchByObjectId($reverseObject['id']);
                        } catch (Exception $e) {

                        }
                    }
                }
            }
        }

        return $this->firstPost;
    }

    /**
     * @return eZBinaryFile
     */
    public function binaryFile()
    {
        $factory = $this->getFactory();
        if ($factory instanceof OCEditorialStuffPostFileFactoryInterface) {
            $fileIdentifier = $factory->fileAttributeIdentifier();
            if (isset( $this->dataMap[$fileIdentifier] ) && $this->dataMap[$fileIdentifier]->hasContent()) {
                return $this->dataMap[$fileIdentifier]->content();
            }
        }

        return null;
    }

    /**
     * @return eZContentObjectAttribute
     */
    public function attributeFile()
    {
        $factory = $this->getFactory();
        if ($factory instanceof OCEditorialStuffPostFileFactoryInterface) {
            $fileIdentifier = $factory->fileAttributeIdentifier();
            if (isset( $this->dataMap[$fileIdentifier] )) {
                return $this->dataMap[$fileIdentifier];
            }
        }

        return null;
    }

    public function downloadFileUrl()
    {
        $binaryFile = $this->binaryFile();
        if ($binaryFile instanceof eZBinaryFile) {
            $url = 'content/download/' . $this->id() . '/' . $binaryFile->attribute('contentobject_attribute_id');
            eZURI::transformURI($url, false, 'full');

            return $url;
        }

        return null;
    }

    public function apiDownloadFileUrl()
    {
        $binaryFile = $this->binaryFile();
        if ($binaryFile instanceof eZBinaryFile) {
            if (OpenPAConsiglioSettings::instance()->localFileServerIsEnabled()) {
                $url = OpenPAConsiglioSettings::instance()->localFileServerDownloadUrl($binaryFile);
            } else {
                $router = new ezpRestRouter(new ezcMvcRequest());
                $url = $router->generateUrl(
                    'consiglioApiSedutaDownloadAllegato',
                    array('Id' => $this->id())
                );
                eZURI::transformURI($url, false, 'full');
            }

            return $url;
        }

        return null;
    }

    /**
     *
     * Allegato
     * id                   integer    id univoco Allegato
     * data_pubblicazione   string     data in formato 'Y-m-d H:i:s'
     * data_ultima_modifica string     data in formato 'Y-m-d H:i:s'
     * visibilita           string     consiglieri|referenti
     * file_name            string     nome del file
     * file_mime_type       string     myme del file
     * file_size            integer    dimensione del file
     * file_download_url    string     url per il download
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = array(
            'id' => $this->id(),
            'data_pubblicazione' => DateTime::createFromFormat('U',
                $this->getObject()->attribute('published'))->format(self::DATE_FORMAT),
            'data_ultima_modifica' => DateTime::createFromFormat('U',
                $this->getObject()->attribute('modified'))->format(self::DATE_FORMAT),
            'visibilita' => $this->currentState()->attribute('identifier'),
            'title' => $this->dataMap['name']->content(),
            'type' => $this->dataMap['tipo']->toString(),
            'file_name' => null,
            'file_mime_type' => null,
            'file_size' => null,
            'file_download_url' => null
        );
        $binaryFile = $this->binaryFile();
        if ($binaryFile instanceof eZBinaryFile) {
            $data['file_name'] = $binaryFile->attribute('original_filename');
            $data['file_mime_type'] = $binaryFile->attribute('mime_type');
            $data['file_size'] = $binaryFile->attribute('filesize');
            $data['file_download_url'] = $this->apiDownloadFileUrl();
        }

        return $data;
    }

}
