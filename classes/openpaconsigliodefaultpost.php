<?php

class OpenPAConsiglioDefaultPost extends OCEditorialStuffPostDefault
{
    public function contentAttributes()
    {
        $data = array();
        foreach( $this->dataMap as $identifier => $attribute )
        {
            $category = $attribute->attribute( 'contentclass_attribute' )->attribute( 'category' );
            if ( $category == 'content' || empty( $category ) )
            {
                $data[$identifier] = $attribute;
            }
        }
        return $data;
    }
}