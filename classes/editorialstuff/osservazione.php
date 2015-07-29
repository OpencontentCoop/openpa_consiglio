<?php

class Osservazione extends OCEditorialStuffPost
{
    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        // TODO: Implement onChangeState() method.
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