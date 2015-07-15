<?php

/*
$data = array(
    'object_id'          => 1,
    'user_id'            => 14,
    'type'               => 'email',
    'subject'            => 'Subject',
    'body'               => 'Body'
);


$item = OpenPAConsiglioNotificationItem::create($data);

echo '<pre>';
print_r($item);

echo $item->__get('subject');

//$item->send();
*/

/** @var OpenPAConsiglioNotificationItem $items */

$items = OpenPAConsiglioNotificationItem::fetchItemsToSend( OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT);

OpenPAConsiglioNotificationItem::sendByType(OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT);

