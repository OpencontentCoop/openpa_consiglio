<?php

class OpenPAConsiglioPassthroughHandler extends eZFilePassthroughHandler
{
	const HANDLER_ID = 'openpaconsigliopassthrough';

    function __construct()
    {
        parent::__construct( self::HANDLER_ID, "PHP passthrough", eZBinaryFileHandler::HANDLE_DOWNLOAD );
    }

    function handleFileDownload( $contentObject, $contentObjectAttribute, $type, $fileInfo )
    {
    	if ($contentObject instanceof eZContentObject && $contentObjectAttribute instanceof eZContentObjectAttribute){
            if ($contentObject->attribute('class_identifier') == 'seduta' 
                && $contentObjectAttribute->attribute('contentclass_attribute_identifier') == 'convocazione'){
                
                $userId = 0;
                $userName = '?';

                $userObject = eZUser::currentUser()->contentObject();
                if ($userObject instanceof eZContentObject){
                    $userId = eZUser::currentUserID();
                    $userName = eZUser::currentUser()->contentObject()->attribute('name');
                }

                OCEditorialStuffHistory::addHistoryToObjectId(
                    $contentObject->attribute('id'),
                    'download_convocazione',
                    array(
                        'user_id' => $userId,
                        'user_name' => $userName                        
                    )
                );
            }
        }
    	return parent::handleFileDownload( $contentObject, $contentObjectAttribute, $type, $fileInfo );
    }
}