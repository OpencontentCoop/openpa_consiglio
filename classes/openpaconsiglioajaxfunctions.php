<?php

class openpaConsiglioAjaxFunctions extends ezjscServerFunctions
{
    public static function getOptions($args)
    {
        $http = eZHTTPTool::instance();
        if ( $http->hasPostVariable( 'objID' ) && $http->hasPostVariable( 'attribute' ) )
        {
            $objID = $http->postVariable( 'objID' );
            $attribute   = $http->postVariable( 'attribute' );

            $object = eZContentObject::fetch( $objID );


            if ($object instanceof eZContentObject)
            {
                $dataMap = $object->dataMap();
                $content = $dataMap[$attribute]->content();

                //print_r($content[relation_list][0]['contentobject_id']);
                //eZExecution::cleanExit();

                return $content[relation_list][0]['contentobject_id'];

                /*return array(
                    'has_content' => $has_content,
                    'tpl'         => $boxDisponibilita,
                    'map'         => $mappaDisponibilita
                );*/

            }
        }
    }
}
?>
