<?php

class Allegato extends OCEditorialStuffPost
{

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {

    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'riferimento';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'riferimento' )
            return $this->getFirstReverseRelatedPost();

        return parent::attribute( $property );
    }

    public function onUpdate()
    {
        $post = $this->getFirstReverseRelatedPost();
        if ( $post instanceof Punto )
        {
            $post->createNotificationEvent( 'update_file', $this );
        }
    }

    protected function getFirstReverseRelatedPost()
    {
        $firstPost = null;
        $reverseObjects = $this->getObject()->reverseRelatedObjectList( false, 0, false, array( 'AllRelations' => true, 'AsObject' => false ) );
        foreach( OCEditorialStuffHandler::instances() as $instance )
        {
            foreach ( $reverseObjects as $reverseObject )
            {
                if( $reverseObject['contentclass_identifier'] == $instance->getFactory()->classIdentifier() )
                {
                    try
                    {
                        return $instance->fetchByObjectId( $reverseObject['id'] );
                    }
                    catch( Exception $e )
                    {

                    }
                }
            }
        }
        return $firstPost;
    }

}