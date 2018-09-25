<?php
/** @var eZModule $module */
$module = $Params['Module'];
$objectID = $Params['ObjectID'];

$redirect = '/';
$editorialUrl = false;

if ( $objectID ){
    $object = eZContentObject::fetch((int)$objectID);
    if ( $object instanceof eZContentObject ){
        
        $editorialUrl = eZFunctionHandler::execute('consiglio', 'editorial_url', array('object' => $object));

        if ($editorialUrl){
            $redirect = $editorialUrl;
        }else{
            $node = $object->attribute( 'main_node' );
            if ( $node instanceof eZContentObjectTreeNode ){
                $redirect = $node->attribute( 'url_alias' );        
            }
        }
    }
}

return $module->redirectTo( $redirect );