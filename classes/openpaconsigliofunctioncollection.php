<?php

class OpenPAConsiglioFunctionCollection
{
    public static function fetchNotificationItems( $limit, $offset, $conditions, $sort )
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
        $votazioneFactory = OCEditorialStuffHandler::instance( 'votazione' )->getFactory();
        $factoryConfiguration = $votazioneFactory->getConfiguration();
        $availableHandlers = $factoryConfiguration['VotazioneResultHandlers'];
        foreach( $availableHandlers as $identifier => $handlerClassName )
        {
            if ( class_exists( $handlerClassName ) )
            {
                $handlerInstance = new $handlerClassName();
                if ( $handlerInstance instanceof OpenPAConsiglioVotazioneResultHandlerInterface )
                {
                    $item = array(
                        'identifier' => $identifier,
                        'description' => $handlerInstance->getDescription()
                    );
                    $result[] = $item;
                }
            }
        }
        return array( 'result' => $result );
    }
}