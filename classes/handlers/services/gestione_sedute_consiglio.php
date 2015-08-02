<?php

class ObjectHandlerServiceGestioneSeduteConsiglio extends ObjectHandlerServiceBase
{

    /**
     * Popola l'array $this->data con chiave => valore
     *
     * @return void
     */
    function run()
    {
        $this->fnData['stuff'] = 'getConsiglioPost';
    }

    protected function getConsiglioPost()
    {
        $object = $this->container->getContentObject();
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
                    }
                }
            }
        }
        return null;
    }
}