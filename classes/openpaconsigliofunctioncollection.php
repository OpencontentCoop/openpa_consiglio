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
}