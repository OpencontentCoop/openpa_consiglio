<?php

class OpenPAConsiglioNotificationItem extends eZPersistentObject
{

    /**
     * @var eZUser
     */
    protected $user;


    function OpenPAConsiglioNotificationItem( $row )
    {
        $this->eZPersistentObject( $row );
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
                    'name'     => 'object_id',
                    'datatype' => 'integer',
                    'default'  => null,
                    'required' => true
                ),
                'user_id' => array(
                    'name'     => 'user_id',
                    'datatype' => 'integer',
                    'default'  => null,
                    'required' => true
                ),
                'created_time'=> array(
                    'name'     => 'created_time',
                    'datatype' => 'integer',
                    'default'  => time(),
                    'required' => false
                ),
                'type' => array(
                    'name'     => 'type',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
                'subject' => array(
                    'name'     => 'subject',
                    'datatype' => 'string',
                    'default'  => null,
                    'required' => true
                ),
                'body' => array(
                    'name'     => 'body',
                    'datatype' => 'text',
                    'default'  => null,
                    'required' => true
                ),
                'expected_send_time'   => array(
                    'name'     => 'expected_send_time',
                    'datatype' => 'integer',
                    'default'  => null,
                    'required' => true
                ),
                'sent'   => array(
                    'name'     => 'sent',
                    'datatype' => 'integer',
                    'default'  => 0,
                    'required' => true
                ),
                'sent_time'   => array(
                    'name'     => 'sent_time',
                    'datatype' => 'integer',
                    'default'  => null,
                    'required' => false
                ),
            ),
            'keys'                 => array('id'),
            'class_name'           => 'OpenPAConsiglioNotificationItem',
            'name'                 => 'openpaconsiglionotificationitem',
            'function_attributes'  => array(
                'user' => 'getUser'
            ),
            'set_functions'        => array()
        );
    }

    public static function create ( $data )
    {
        $time = time();
        $row = array(
            'object_id'          => $data['object_id'],
            'user_id'            => $data['user_id'],
            'created_time'       => $time,
            'type'               => $data['type'],
            'subject'            => $data['subject'],
            'body'               => $data['body']
        );
        $notification = new OpenPAConsiglioNotificationItem( $row );
        $notification->store();
        return $notification;
    }

    public function __get( $name )
    {
        $ret = null;
        if( $this->hasAttribute( $name ) )
            $ret = $this->attribute( $name );

        return $ret;
    }


    public function getUser()
    {
        if ( !$this->user instanceof eZUser )
            $this->user = eZUser::fetch( $this->attribute( 'user_id' ) );

        return $this->user;
    }

    public function setUser( eZUser $user )
    {
        $this->user = $user;
        $this->setAttribute( 'user_id', $user->attribute( 'contentobject_id' ) );
    }

    public static function fetchList( $offset = 0, $limit = 0, $conds = null )
    {
        if( !$limit )
            $aLimit = null;
        else
            $aLimit = array( 'offset' => $offset, 'length' => $limit );

        $sort = array( 'created_time' => 'asc' );
        $aImports = self::fetchObjectList( self::definition(), null, $conds, $sort, $aLimit );

        return $aImports;
    }

}