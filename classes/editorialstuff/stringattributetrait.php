<?php

trait OpenPAConsiglioStringAttributeTrait
{

    public function stringAttribute($identifier, $callback = null)
    {
        if (isset( $this->dataMap[$identifier] )) {
            $string = $this->dataMap[$identifier]->toString();
            if (is_callable($callback)) {
                return call_user_func($callback, $string);
            }

            return $string;
        }

        return '';
    }

    public function stringRelatedObjectAttribute($identifier, $attributeIdentifier = null)
    {
        $data = array();
        $ids = explode('-', $this->stringAttribute($identifier));
        foreach ($ids as $id) {
            $related = eZContentObject::fetch($id);
            if ($related instanceof eZContentObject) {
                if ($attributeIdentifier) {
                    if ($related->hasAttribute($attributeIdentifier)) {
                        $data[] = $related->attribute($attributeIdentifier);
                    } else {
                        /** @var eZContentObjectAttribute[] $dataMap */
                        $dataMap = $related->attribute('data_map');
                        if (isset( $dataMap[$attributeIdentifier] )) {
                            $data[] = $dataMap[$attributeIdentifier]->toString();
                        }
                    }
                } else {
                    $data[] = $related;
                }
            }
        }

        return empty( $data ) ? null : $data;
    }
}
