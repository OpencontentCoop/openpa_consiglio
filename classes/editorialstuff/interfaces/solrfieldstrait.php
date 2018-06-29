<?php

trait SolrFieldsTrait
{
    public static function generateSolrField( $identifier, $type )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName( ezfSolrDocumentFieldBase::ATTR_FIELD_PREFIX . $identifier, $type );
    }

    public static function generateSolrSubMetaField( $identifier, $subIdentifier )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName(
            ezfSolrDocumentFieldBase::SUBMETA_FIELD_PREFIX . $identifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . $subIdentifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR,
            eZSolr::getMetaAttributeType( $subIdentifier ) );
    }

    public static function generateSolrSubField( $identifier, $subIdentifier, $type )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName(
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_PREFIX . $identifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . $subIdentifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR,
            $type );
    }
}
