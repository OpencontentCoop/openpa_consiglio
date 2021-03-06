<?php

class OpenPAConsiglioFunctionCollection
{
    public static function fetchNotificationItems($limit, $offset, $conditions, $sort)
    {
        return array(
            'result' => OpenPAConsiglioNotificationItem::fetchList(
                $offset,
                $limit,
                $conditions,
                $sort
            )
        );
    }

    public static function fetchTipiVotazione()
    {
        $result = array();
        $votazioneFactory = OCEditorialStuffHandler::instance('votazione')->getFactory();
        $factoryConfiguration = $votazioneFactory->getConfiguration();
        $availableHandlers = $factoryConfiguration['VotazioneResultHandlers'];
        foreach ($availableHandlers as $identifier => $handlerClassName) {
            if (class_exists($handlerClassName)) {
                $handlerInstance = new $handlerClassName();
                if ($handlerInstance instanceof OpenPAConsiglioVotazioneResultHandlerInterface) {
                    $item = array(
                        'identifier' => $identifier,
                        'description' => $handlerInstance->getDescription()
                    );
                    $result[] = $item;
                }
            }
        }

        return array('result' => $result);
    }

    public static function fetchNextItems()
    {
        $dateTime = new DateTime();
        $dateTime->sub(new DateInterval('P1D'));
        $from = ezfSolrDocumentFieldBase::preProcessValue($dateTime->getTimestamp(), 'date');

        $dateTime = new DateTime();
        $dateTime->add(new DateInterval('P1M'));
        $to = ezfSolrDocumentFieldBase::preProcessValue($dateTime->getTimestamp(), 'date');

        $result = OCEditorialStuffHandler::instance('seduta')->fetchItems(array(
            'filters' => array(
                'attr_data_dt:[' . $from . ' TO ' . $to . ']',
                //'submeta_partecipanti___id_si:' . eZUser::currentUserID()
            ),
            'limit' => 15,
            'offset' => 0
        ));

        return array('result' => $result);
    }

    public static function fetchLatestOsservazioni()
    {
        $result = array();
        try {
            $result = eZContentObjectTreeNode::subTreeByNodeID(array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array(OCEditorialStuffPostFactory::instance('osservazioni')->classIdentifier()),
                'Limit' => 20,
                'AttributeFilter' => array(array('owner', '=', eZUser::currentUserID())),
                'SortBy' => array('modified', false)
            ), 1);
        } catch (Exception $e) {
        }

        return array('result' => $result);
    }

    public static function fetchAlerts()
    {
        $result = array();
        if (OpenPAConsiglioConfiguration::instance()->getAlertsContainerNodeId() > 0) {
            try {
                $result = eZContentObjectTreeNode::subTreeByNodeID(array(
                    'Limit' => 20,
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'SortBy' => array('modified', false)
                ), OpenPAConsiglioConfiguration::instance()->getAlertsContainerNodeId());
            } catch (Exception $e) {
            }
        }

        return array('result' => (array)$result);
    }

    public static function fetchActiveDashboards()
    {
        $activeDashboards = OpenPAConsiglioSettings::instance()->getActiveDashboards();
        return array('result' => array_fill_keys($activeDashboards, true));
    }

    public static function fetchSocketInfo()
    {
        return array('result' => OpenPAConsiglioSettings::instance()->getSocketInfo());
    }

    public function fetchPost($object, $node)
    {
        if ($object == null && $node) {
            if (is_numeric($node)) {
                $object = eZContentObject::fetchByNodeID($node);
            } elseif ($node instanceof eZContentObjectTreeNode) {
                $object = $node->attribute('object');
            }
        }
        if (is_numeric($object)) {
            $object = eZContentObject::fetch($object);
        }
        if ($object instanceof eZContentObject) {
            foreach (OCEditorialStuffHandler::instances() as $instance) {
                if ($object->attribute('class_identifier') == $instance->getFactory()->classIdentifier()) {
                    try {
                        return array(
                            'result' => $instance->getFactory()->instancePost(
                                array('object_id' => $object->attribute('id'))
                            )
                        );
                    } catch (Exception $e) {
                    }
                }
            }
        }

        return array('error' => "Post not found");
    }


    public static function fetchEditorialUrl($object, $do_redirect)
    {
        $factories = eZINI::instance('editorialstuff.ini')->variable('AvailableFactories', 'Identifiers');
        foreach ($factories as $factory) {
            $classIdentifier = eZINI::instance('editorialstuff.ini')->variable($factory, 'ClassIdentifier');
            if ($object->attribute('class_identifier') == $classIdentifier) {
                try {
                    $post = OCEditorialStuffHandler::instance($factory, array())->fetchByObjectId($object->attribute('id'));
                    if ($post instanceof OCEditorialStuffPostInterface) {
                        $editorialUrl = $post->attribute('editorial_url');
                        if ($do_redirect) {
                            eZURI::transformURI($editorialUrl);
                            eZHTTPTool::redirect($editorialUrl);
                            return;
                        }
                        return array(
                            'result' => $editorialUrl
                        );
                    }
                } catch (Exception $e) {
                }
            }
        }

        return array('error' => "Editorial url not found");
    }
}
