<?php

class OpenPAConsiglioNotificationItem extends eZPersistentObject
{

    /**
     * @var eZUser
     */
    protected $user;


    function OpenPAConsiglioNotificationItem( $row )
    {
        $this->PersistentDataDirty = false;
        if ( !empty( $row ) )
        {
            $this->fill( $row );
        }
    }

    public static function definition()
    {
        return array(
            'fields' => array(
                'id' => array(
                    'name' => 'ID',
                    'datatype' => 'integer',
                    'default' => 0,
                    'required' => true
                ),
                'object_id' => array(
                    'name' => 'object_id',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true
                ),
                'user_id' => array(
                    'name' => 'user_id',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true
                ),
                'created_time' => array(
                    'name' => 'created_time',
                    'datatype' => 'integer',
                    'default' => time(),
                    'required' => false
                ),
                'type' => array(
                    'name' => 'type',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'subject' => array(
                    'name' => 'subject',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true
                ),
                'body' => array(
                    'name' => 'body',
                    'datatype' => 'text',
                    'default' => null,
                    'required' => true
                ),
                'expected_send_time' => array(
                    'name' => 'expected_send_time',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => false
                ),
                'sent' => array(
                    'name' => 'sent',
                    'datatype' => 'integer',
                    'default' => 0,
                    'required' => true
                ),
                'sent_time' => array(
                    'name' => 'sent_time',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => false
                ),
            ),
            'keys' => array( 'id' ),
            'increment_key' => 'id',
            'class_name' => 'OpenPAConsiglioNotificationItem',
            'name' => 'openpaconsiglionotificationitem',
            'function_attributes' => array(
                'user' => 'getUser',
                'post_object' => 'getPostObject'
            ),
            'set_functions' => array(
                'params' => 'setParams',
                'user' => 'setUser',
                'sent' => 'setSent'
            )
        );
    }

    public static function create( $row )
    {
        if ( !empty( $row['subject'] ) )
        {
            $notification = new OpenPAConsiglioNotificationItem( $row );
            $notification->store();

            return $notification;
        }
        else
        {
            eZDebug::writeError( "Empty subject " . var_export( $row, 1 ), __METHOD__ );
        }
        return null;
    }

    public function send()
    {
        $transport = OpenPAConsiglioNotificationTransport::instance( $this->attribute( 'type' ) );
        if ( $transport->send( $this ) )
        {
            $this->setSent();
            return true;
        }
        return false;
    }

    public function setSent()
    {
        $this->setAttribute( 'sent', 1 );
        $this->setAttribute( 'sent_time', time() );
        $this->store();
    }

    public function getUser()
    {
        if ( !$this->user instanceof eZUser )
        {
            $this->user = eZUser::fetch( $this->attribute( 'user_id' ) );
        }

        return $this->user;
    }

    public function setUser( eZUser $user )
    {
        $this->user = $user;
        $this->setAttribute( 'user_id', $user->attribute( 'contentobject_id' ) );
    }


    /**
     * @param int $offset
     * @param int $limit
     * @param null $conds
     * @param array $sort
     *
     * @return OpenPAConsiglioNotificationItem[]
     */
    public static function fetchList( $offset = null, $limit = null, $conds = null, $sort = null )
    {
        if ( !$limit )
        {
            $aLimit = null;
        }
        else
        {
            $aLimit = array( 'offset' => $offset, 'length' => $limit );
        }
        if ( !$sort )
        {
            $sort = array( 'created_time' => 'asc' );
        }
        $aImports = self::fetchObjectList( self::definition(), null, $conds, $sort, $aLimit );

        return $aImports;
    }

    public static function fetchListByUserType( $userID, $type )
    {
        return self::fetchList( null, null, array( 'user_id' => $userID, 'type' => $type ) );
    }

    public static function fetchListByUserID( $userID )
    {
        return self::fetchList( null, null, array( 'user_id' => $userID ) );
    }

    public static function fetchItemsToSend( $type = null )
    {
        $conds = array();
        if ( $type )
        {
            $conds ['type'] = $type;
        }
        $conds['sent'] = 0;
        $conds['expected_send_time'] = array( '<=', time() );
        return self::fetchList( null, null, $conds );
    }

    public function canSend()
    {
        return $this->attribute( 'expected_send_time' ) <= time();
    }

    public static function sendByType( $type = null )
    {
        $transport = OpenPAConsiglioNotificationTransport::instance( $type );
        $transport->sendMassive();
    }

    public function getPostObject()
    {
        $object = eZContentObject::fetch( $this->attribute( 'object_id' ) );
        if ( $object instanceof eZContentObject )
        {
            foreach ( OCEditorialStuffHandler::instances() as $instance )
            {
                if ( $object->attribute( 'class_identifier' ) == $instance->getFactory()->classIdentifier() )
                {
                    try
                    {
                        return $instance->getFactory()->instancePost(
                            array( 'object_id' => $object->attribute( 'id' ) )
                        );
                    }
                    catch ( Exception $e )
                    {
                        eZDebug::writeError( $e->getMessage(), __METHOD__ );
                    }
                }
            }
        }
        return null;
    }

}