<?php


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

exit;