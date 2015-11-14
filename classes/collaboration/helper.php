<?php

class OpenPAConsiglioCollaborationHelper
{
    const MAX_FILESIZE_FIELD = 0;

    /**
     * @var eZUser
     */
    protected $referente;

    public $redirectParams;

    public function __construct()
    {
        self::createCollaborationContainerIfNeeded();
    }

    public function setReferente( eZUser $referente )
    {
        $this->referente = $referente;
    }

    public function getArea()
    {
        return self::createCollaborationAreaIfNeeded( $this->referente )->mainNode();
    }

    public function getAreaTags()
    {
        return self::createCollaborationAreaIfNeeded( $this->referente )->mainNode()->subTree( array( 'Depth' => 1,
                                                                                                      'DepthOperator' => 'eq',
                                                                                                      'SortBy' => array( 'name', 'asc' ) ) );
    }

    public function addAreaTag( $name )
    {
        $params =  array(
            'creator_id' => $this->referente->id(),
            'class_identifier' => 'folder',
            'parent_node_id' => $this->getArea()->attribute( 'node_id' ),
            'attributes' => array(
                'name' => $name
            )
        );
        return eZContentFunctions::createAndPublishObject( $params );
    }

    public function addComment( $parentNodeId, $text, $filePath )
    {
        $params =  array(
            'creator_id' => eZUser::currentUserID(),
            'class_identifier' => 'comment',
            'parent_node_id' => $parentNodeId,
            'attributes' => array(
                'subject' => substr( $text, 0, 10 ) . '...',
                'message' => $text,
                'author' => eZUser::currentUser()->contentObject()->attribute( 'name' ),
                'file' => $filePath
            )
        );
        $object = eZContentFunctions::createAndPublishObject( $params );
        $file = eZClusterFileHandler::instance( $filePath );
        if ( $file->exists() )
        {
            $file->delete();
        }
        return $object;
    }

    /**
     * @return eZContentObject[]
     */
    public function getAreaUsers( $asObject = true )
    {
        $users = array( 14 );
        $limitValue = $this->getArea()->attribute( 'path_string' );
        $role = self::createRoleIfNeeded();
        $query = "SELECT * FROM ezuser_role WHERE role_id='{$role->ID}' AND limit_identifier='Subtree' AND limit_value='$limitValue'";
        $db = eZDB::instance();
        $rows = $db->arrayQuery( $query );
        foreach( $rows as $row )
        {
            $users[] = $row['contentobject_id'];
        }
        return $asObject ? eZContentObject::fetchIDArray( $users ) : $users;
    }

    /**
     * @return int[]
     */
    public function getAreaUserIdList()
    {
        return $this->getAreaUsers( false );
    }

    public function canReadArea()
    {
        return $this->getArea()->object()->canRead();
    }

    public function executeAction( $action )
    {
        if ( $action == 'add_tag' && eZHTTPTool::instance()->hasPostVariable( 'NewTagName' ) )
        {
            if ( eZUser::currentUserID() == $this->referente->id() )
            {
                $this->addAreaTag( eZHTTPTool::instance()->postVariable( 'NewTagName' ) );
            }
        }
        elseif ( $action == 'add_comment' && eZHTTPTool::instance()->hasPostVariable( 'PublishComment' ) )
        {
            if ( in_array( eZUser::currentUserID(), $this->getAreaUserIdList() ) || eZUser::currentUserID() == 14 )
            {
                $filePath = $text = $parentNodeId = null;
                if ( eZHTTPTool::instance()->hasPostVariable( 'Tag' ) && ( eZHTTPTool::instance()->hasPostVariable( 'CommentText' ) || eZHTTPTool::instance()->hasPostVariable( 'CommentFile' ) ) )
                {
                    if ( eZHTTPTool::instance()->hasPostVariable( 'Tag' ) )
                    {
                        $parentNodeId = intval( eZHTTPTool::instance()->postVariable( 'Tag' ) );
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
                    if ( eZHTTPTool::instance()->hasPostVariable( 'CommentText' ) )
                    {
                        $text = eZHTTPTool::instance()->postVariable( 'CommentText' );
                    }
                }
                if ( $parentNodeId && ( $filePath || $text ) )
                {
                    $this->addComment( $parentNodeId, $text, $filePath );
                    $this->redirectParams = '/tag-' . $parentNodeId;
                }
            }
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

    /**
     * @return eZContentObject
     */
    protected static function createCollaborationContainerIfNeeded()
    {
        $remoteId = 'openpa_consiglio_collaboration_container';
        $object = eZContentObject::fetchByRemoteID( 'openpa_consiglio_collaboration_container' );
        if ( !$object instanceof eZContentObject )
        {
            $params =  array(
                'remote_id' => $remoteId,
                'class_identifier' => 'folder',
                'parent_node_id' => eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ),
                'attributes' => array(
                    'name' => 'Aree collaborative'
                )
            );
            $object = eZContentFunctions::createAndPublishObject( $params );
        }
        return $object;
    }

    /**
     * @param eZUser $referente
     *
     * @return eZContentObject
     */
    protected static function createCollaborationAreaIfNeeded( eZUser $referente )
    {
        $remoteId = 'openpa_consiglio_collaboration_area_' . $referente->id();
        $object = eZContentObject::fetchByRemoteID( $remoteId );
        if ( !$object instanceof eZContentObject )
        {
            $params =  array(
                'remote_id' => $remoteId,
                'creator_id' => $referente->id(),
                'class_identifier' => 'folder',
                'parent_node_id' => self::createCollaborationContainerIfNeeded()->attribute( 'main_node_id' ),
                'attributes' => array(
                    'name' => 'Area collaborativa di ' . $referente->contentObject()->attribute( 'name' )
                )
            );
            $object = eZContentFunctions::createAndPublishObject( $params );
        }
        return $object;
    }

    /**
     * @return eZRole
     */
    public static function createRoleIfNeeded()
    {
        $roleName = 'Gestione sedute consiglio - Area Collaborativa';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array(); //@todo
            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }

        }
        return $role;
    }

    public static function assignCollaborationRole( eZUser $user, eZUser $referente )
    {
        $subTreeLimitationNodeId = self::createCollaborationAreaIfNeeded( $referente )->attribute( 'main_node_id' );
        $role = self::createRoleIfNeeded();
        $role->assignToUser( $user->id(), 'subtree', $subTreeLimitationNodeId );
    }

    /**
     * @param $currentUserId
     *
     * @return eZContentObjectTreeNode[]
     */
    public static function listAccessAreas()
    {
        $currentUserId = eZUser::currentUserID();
        $role = self::createRoleIfNeeded();
        $query = "SELECT * FROM ezuser_role WHERE role_id='{$role->ID}' AND contentobject_id='{$currentUserId}' AND limit_identifier='Subtree'";
        $db = eZDB::instance();
        $rows = $db->arrayQuery( $query );
        $collaborationAreas = array();
        foreach( $rows as $row )
        {
            $node = eZContentObjectTreeNode::fetchByPath( $rows['limit_value'] );
            if ( $node instanceof eZContentObjectTreeNode )
            {
                $collaborationAreas[] = $node;
            }
        }
        return $collaborationAreas;
    }

}