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
        self::createPoliticoRoleIfNeeded();
    }

    public function setReferente( eZUser $referente )
    {
        $this->referente = $referente;
        self::createCollaborationAreaIfNeeded( $this->referente );
        self::createCollaborationGroupIfNeeded( $this->referente );
    }

    public function getArea()
    {
        return self::createCollaborationAreaIfNeeded( $this->referente )->mainNode();
    }

    public function getAreaGroup()
    {
        return self::createCollaborationGroupIfNeeded( $this->referente )->mainNode();
    }

    public function getAreaTags()
    {
        return self::createCollaborationAreaIfNeeded( $this->referente )->mainNode()->subTree( array( 'Depth' => 1,
                                                                                                      'DepthOperator' => 'eq',
                                                                                                      'ClassFilterType' => 'include',
                                                                                                      'ClassFilterArray' => array( 'folder' ),
                                                                                                      'SortBy' => array( 'name', 'asc' ) ) );
    }

    /**
     * @param $name
     *
     * @return eZContentObject
     */
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
        $object = eZContentFunctions::createAndPublishObject( $params );
        return $object;
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
     * @param bool $asObject
     * @return eZContentObject[]
     */
    public function getAreaUsers( $asObject = true )
    {
        /** @var eZContentObjectTreeNode[] $userNodes */
        $userNodes = $this->getAreaGroup()->children();
        $users = array( $this->referente->contentObject() );
        $userIds = array( $this->referente->id() );
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
                $object = $this->addAreaTag( eZHTTPTool::instance()->postVariable( 'NewTagName' ) );
                $this->redirectParams = '/tag-' . $object->attribute( 'main_node_id' );
            }
        }
        elseif ( $action == 'add_comment' && eZHTTPTool::instance()->hasPostVariable( 'PublishComment' ) )
        {
            if ( in_array( eZUser::currentUserID(), $this->getAreaUserIdList() ) )
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
    public static function createCollaborationAreaIfNeeded( eZUser $referente )
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
     * @param eZUser $referente
     *
     * @return eZContentObject
     */
    public static function createCollaborationGroupIfNeeded( eZUser $referente )
    {
        $remoteId = 'openpa_consiglio_collaboration_group_' . $referente->id();
        $object = eZContentObject::fetchByRemoteID( $remoteId );
        if ( !$object instanceof eZContentObject )
        {
            $params =  array(
                'remote_id' => $remoteId,
                'creator_id' => $referente->id(),
                'class_identifier' => 'user_group',
                'parent_node_id' => self::createCollaborationAreaIfNeeded( $referente )->attribute( 'main_node_id' ),
                'attributes' => array(
                    'name' => 'Utenti area collaborativa di ' . $referente->contentObject()->attribute( 'name' )
                )
            );
            $object = eZContentFunctions::createAndPublishObject( $params );
        }
        self::assignCollaborationRole( $object->attribute( 'id' ), $referente );
        return $object;
    }

    /**
     * @return eZContentObjectTreeNode[]
     */
    public static function listAccessAreas()
    {
        return self::createCollaborationContainerIfNeeded()->mainNode()->subTree(
            array(
                'Depth' => 1,
                'DepthOperator' => 'eq',
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'folder' )
            )
        );
    }

    /**
     * @return eZRole
     */
    protected static function createRoleIfNeeded()
    {
        $roleName = 'Gestione sedute consiglio - Area Collaborativa';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array(
                array(
                    'ModuleName' => 'consiglio',
                    'FunctionName' => 'collaboration',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'comment' ),
                            eZContentClass::classIDByIdentifier( 'folder' ),
                            eZContentClass::classIDByIdentifier( 'user' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'comment' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'folder' )
                        )
                    )
                ),
            ); //@todo
            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }

        }
        return $role;
    }

    protected static function createPoliticoRoleIfNeeded()
    {
        $roleName = 'Gestione sedute consiglio - Area Collaborativa - Politico';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array(
                array(
                    'ModuleName' => 'consiglio',
                    'FunctionName' => 'collaboration',
                    'Limitation' => array()
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'comment' ),
                            eZContentClass::classIDByIdentifier( 'folder' ),
                            eZContentClass::classIDByIdentifier( 'user' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'comment' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'folder' )
                        ),
                        'ParentOwner' => 1
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'folder' )
                        ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'folder' )
                        ),
                        'ParentOwner' => 1
                    )
                )
            ); //@todo
            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }
            $politicoRepositorySettings = OCEditorialStuffHandler::instance( 'politico' )->getFactory()->getConfiguration();
            $nodeId = $politicoRepositorySettings['CreationRepositoryNode'];
            $object = eZContentObject::fetchByNodeID( $nodeId );
            if ( $object instanceof eZContentObject )
                $role->assignToUser( $object->attribute( 'id' ) );
        }
        return $role;
    }


    protected static function assignCollaborationRole( $groupId, eZUser $referente )
    {
        $subTreeLimitationNodeId = self::createCollaborationAreaIfNeeded( $referente )->attribute( 'main_node_id' );
        $role = self::createRoleIfNeeded();
        $role->assignToUser( $groupId, 'subtree', $subTreeLimitationNodeId );
    }

}