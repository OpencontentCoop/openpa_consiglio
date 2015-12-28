<?php

class OpenPAConsiglioCollaborationHelper
{
    const MAX_FILESIZE_FIELD = 0;

    /**
     * @var AreaCollaborativa
     */
    protected $area;

    public $redirectParams;

    public function __construct( AreaCollaborativa $area )
    {
        $this->area = $area;
    }

    public function getArea()
    {
        return $this->area->mainNode();
    }

    public function getAreaGroup()
    {
        return $this->area->group()->mainNode();
    }

    public function getAreaRooms()
    {
        return $this->area->rooms( in_array( eZUser::currentUserID(), $this->area->politiciIdList() ) );
    }

    protected function sendNotification( eZContentObject $object )
    {
        if ( $object instanceof eZContentObject )
        {
            $tpl = eZTemplate::factory();
            $tpl->resetVariables();
            $tpl->setVariable( 'area', $this->getArea() );
            $tpl->setVariable( 'object', $object );
            $content = $tpl->fetch( 'design:consiglio/collaboration/mail_notification.tpl' );
            $subject = $tpl->variable( 'subject' );

            $tpl = eZTemplate::factory();
            $tpl->resetVariables();
            $tpl->setVariable( 'content', $content );
            $content = $tpl->fetch( 'design:consiglio/notification/mail_pagelayout.tpl' );

            $ini = eZINI::instance();
            $mail = new eZMail();
            $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
            if ( !$emailSender )
            {
                $emailSender = $ini->variable( "MailSettings", "AdminEmail" );
            }
            $senderName = $ini->variable( 'SiteSettings', 'SiteName' );
            $mail->setSender( $emailSender, $senderName );

            $users = array_merge( $this->getAreaUsers( false ), $this->area->politiciIdList() );
            foreach ( $users as $index => $userId )
            {
                if ( $object->attribute( 'owner_id' ) != $userId )
                {
                    $user = eZUser::fetch( $userId );
                    if ( $user instanceof eZUser )
                    {
                        if ( $index == 0 )
                        {
                            $mail->setReceiver( $user->Email );
                        }
                        else
                        {
                            $mail->addCc( $user->Email );
                        }
                    }
                }
            }

            if ( trim( $subject ) != '' )
            {
                $mail->setSubject( $subject );
                $mail->setBody( $content );
                $mail->setContentType( 'text/html' );
                OpenPAConsiglioMailTransport::send( $mail );
            }
        }
    }

    /**
     * @param $name
     * @param $relationId
     *
     * @return eZContentObject
     */
    public function addAreaRoom( $name, $relationId = null, $expiryTimestamp = null )
    {
        $params =  array(
            'class_identifier' => 'openpa_consiglio_collaboration_room',
            'parent_node_id' => $this->getArea()->attribute( 'node_id' ),
            'attributes' => array(
                'name' => $name,
                'relation' => $relationId,
                'expiry' => $expiryTimestamp
            )
        );
        /** @var eZContentObject $object */
        $object = eZContentFunctions::createAndPublishObject( $params );
        $this->sendNotification( $object );
        return $object;
    }

    public function updateAreaRoomExpiry( eZContentObject $room, $expiryTimestamp )
    {
        if ( $room->attribute( 'class_identifier' ) == 'openpa_consiglio_collaboration_room' )
        {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $room->dataMap();
            if ( isset( $dataMap['expiry'] ) )
            {
                $dataMap['expiry']->fromString( $expiryTimestamp );
                $dataMap['expiry']->store();
                eZSearch::addObject( $room, true );
                $this->sendNotification( $room );
            }
        }
    }

    public function addComment( $parentNodeId, $text, $subject = null )
    {
        $params =  array(
            'creator_id' => eZUser::currentUserID(),
            'class_identifier' => 'openpa_consiglio_collaboration_comment',
            'parent_node_id' => $parentNodeId,
            'attributes' => array(
                'subject' => $subject ? $subject : substr( $text, 0, 10 ) . '...',
                'message' => $text
            )
        );
        /** @var eZContentObject $object */
        $object = eZContentFunctions::createAndPublishObject( $params );
        $this->sendNotification( $object );
        return $object;
    }

    public function addFile( $parentNodeId, $filePath, $subject = null, $relationId = null, $deleteOriginalFile = true )
    {
        $params =  array(
            'creator_id' => eZUser::currentUserID(),
            'class_identifier' => 'openpa_consiglio_collaboration_file',
            'parent_node_id' => $parentNodeId,
            'attributes' => array(
                'subject' => $subject ? $subject : basename( $filePath ),
                'file' => $filePath,
                'relation' => $relationId
            )
        );
        /** @var eZContentObject $object */
        $object = eZContentFunctions::createAndPublishObject( $params );
        $file = eZClusterFileHandler::instance( $filePath );
        if ( $file->exists() && $deleteOriginalFile )
        {
            $file->delete();
        }
        $this->sendNotification( $object );
        return $object;
    }

    /**
     * @param bool $asObject
     * @return eZContentObject[]
     */
    public function getAreaUsers( $asObject = true )
    {
        /** @var eZContentObjectTreeNode[] $userNodes */
        $userNodes = $this->getAreaGroup()->children();
        $users = array();
        $userIds = array();
        foreach( $userNodes as $user )
        {
            $userIds[] = $user->attribute( 'contentobject_id' );
            $users[] = $user->attribute( 'object' );
        }
        return $asObject ? $users : $userIds;
    }

    /**
     * @return int[]
     */
    public function getAreaUserIdList()
    {
        return $this->getAreaUsers( false );
    }

    public function canParticipate()
    {
        return in_array( eZUser::currentUserID(), $this->getAreaUserIdList() ) || in_array( eZUser::currentUserID(), $this->area->politiciIdList() );
    }

    public function canReadArea()
    {
        return $this->getArea()->object()->canRead();
    }

    protected function parseDate( $string, $fallback = null )
    {
        if ( $string )
        {
            $date = explode( '/', $string );
            $time = mktime( 0,0,0, $date[1], $date[0], $date[2] );
        }
        else
        {
            $time = $fallback;
        }
        return $time;
    }

    public function executeAction( $action )
    {
        if ( $action == 'add_room' && eZHTTPTool::instance()->hasPostVariable( 'NewRoomName' ) )
        {
            if ( $this->canParticipate() )
            {
                $name = eZHTTPTool::instance()->postVariable( 'NewRoomName', 'Nuova tematica' );
                $expiry = eZHTTPTool::instance()->postVariable( 'NewRoomExpiry', null );
                $expiryTimestamp = $this->parseDate( $expiry, time() );
                $object = $this->addAreaRoom( $name, null, $expiryTimestamp );
                $this->redirectParams = '/room-' . $object->attribute( 'main_node_id' );
            }
            else
            {
                throw new Exception( "Operazione non consentita" );
            }
        }
        elseif ( $action == 'add_comment' && eZHTTPTool::instance()->hasPostVariable( 'PublishComment' ) )
        {
            if ( $this->canParticipate() )
            {
                $text = $parentNodeId = null;
                if ( eZHTTPTool::instance()->hasPostVariable( 'Room' ) && eZHTTPTool::instance()->hasPostVariable( 'CommentText' ) )
                {
                    if ( eZHTTPTool::instance()->hasPostVariable( 'Room' ) )
                    {
                        $parentNodeId = intval( eZHTTPTool::instance()->postVariable( 'Room' ) );
                        $parentNode = eZContentObjectTreeNode::fetch( $parentNodeId );
                        if ( $parentNode instanceof eZContentObjectTreeNode )
                        {
                            if ( $parentNode->attribute( 'parent_node_id' ) != $this->getArea()->attribute( 'node_id' ) )
                            {
                                $parentNodeId = null;
                            }
                        }

                    }
                    if ( eZHTTPTool::instance()->hasPostVariable( 'CommentText' ) )
                    {
                        $text = eZHTTPTool::instance()->postVariable( 'CommentText' );
                    }

                    $text = trim( $text);
                    if( empty( $text ) )
                    {
                        throw new Exception( "Compila il campo di testo" );
                    }
                }
                if ( $parentNodeId && $text )
                {
                    $this->addComment( $parentNodeId, $text );
                    $this->redirectParams = '/room-' . $parentNodeId;
                }
            }
            else
            {
                throw new Exception( "Operazione non permessa: non sei iscritto come partecipante a quest'area" );
            }
        }
        elseif ( $action == 'add_file' && eZHTTPTool::instance()->hasPostVariable( 'PublishFile' ) )
        {
            if ( $this->canParticipate() )
            {
                $filePath = $parentNodeId = null;
                if ( eZHTTPTool::instance()->hasPostVariable( 'Room' ) && $this->validateFileInput( 'CommentFile' ) )
                {
                    if ( eZHTTPTool::instance()->hasPostVariable( 'Room' ) )
                    {
                        $parentNodeId = intval( eZHTTPTool::instance()->postVariable( 'Room' ) );
                        $parentNode = eZContentObjectTreeNode::fetch( $parentNodeId );
                        if ( $parentNode instanceof eZContentObjectTreeNode )
                        {
                            if ( $parentNode->attribute( 'parent_node_id' ) != $this->getArea()->attribute( 'node_id' ) )
                            {
                                $parentNodeId = null;
                            }
                        }
                    }
                    if ( $this->validateFileInput( 'CommentFile' ) )
                    {
                        /** @var eZHTTPFile $binaryFile */
                        $binaryFile = eZHTTPFile::fetch( 'CommentFile' );
                        $binaryFile->store();
                        $filePath = dirname( $binaryFile->Filename ) . '/' . $binaryFile->attribute( 'original_filename' );
                        eZFile::rename( $binaryFile->Filename, $filePath );
                    }
                    else
                    {
                        throw new Exception( "Errore nel caricamento del file" );
                    }
                }
                if ( $parentNodeId && $filePath )
                {
                    $this->addFile( $parentNodeId, $filePath );
                    $this->redirectParams = '/room-' . $parentNodeId;
                }
                else
                {
                    throw new Exception( "Errore" );
                }
            }
            else
            {
                throw new Exception( "Operazione non permessa: non sei iscritto come partecipante a quest'area" );
            }
        }
        elseif ( strpos( $action, 'hide-' ) === 0 && in_array( eZUser::currentUserID(), $this->area->politiciIdList() ) )
        {
            $nodeId = str_replace( 'hide-', '', $action );
            eZContentOperationCollection::changeHideStatus( $nodeId );

        }
        elseif ( strpos( $action, 'show-' ) === 0 && in_array( eZUser::currentUserID(), $this->area->politiciIdList() ) )
        {
            $nodeId = str_replace( 'show-', '', $action );
            eZContentOperationCollection::changeHideStatus( $nodeId );
        }
        elseif ( $action == 'change_expiry' && in_array( eZUser::currentUserID(), $this->area->politiciIdList() ) )
        {
            $roomId = eZHTTPTool::instance()->postVariable( 'RoomId' );
            $expiry = eZHTTPTool::instance()->postVariable( 'RoomExpiry', null );
            $object = eZContentObject::fetch( intval($roomId) );
            if ( $object instanceof eZContentObject )
            {
                $this->updateAreaRoomExpiry( $object, $this->parseDate( $expiry ) );
            }
            else
            {
                throw new Exception( "Tematica non trovata" );
            }
        }
        else
        {
            throw new Exception( "Operazione non consentita" );
        }
        return true;
    }

    protected function validateFileInput( $httpFileName )
    {
        $isFileUploadsEnabled = ini_get( 'file_uploads' ) != 0;
        if ( !$isFileUploadsEnabled )
        {
            return false;
        }
        $maxSize = 1024 * 1024 * self::MAX_FILESIZE_FIELD;
        $canFetchResult = eZHTTPFile::canFetch( $httpFileName, $maxSize );
        if ( $canFetchResult == eZHTTPFile::UPLOADEDFILE_DOES_NOT_EXIST )
        {
            return false;
        }
        if ( $canFetchResult == eZHTTPFile::UPLOADEDFILE_EXCEEDS_PHP_LIMIT )
        {
            return false;
        }
        if ( $canFetchResult == eZHTTPFile::UPLOADEDFILE_EXCEEDS_MAX_SIZE )
        {
            return false;
        }
        return true;
    }

}