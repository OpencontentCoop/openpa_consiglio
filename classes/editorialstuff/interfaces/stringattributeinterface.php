<?php


interface OpenPAConsiglioStringAttributeInterface
{
    /**
     * Restituisce il toString dell'attributo $identifier filtrato da $callback (se presente)
     *
     * @param string $identifier
     * @param Callable $callback
     *
     * @return bool|mixed|string
     */
    public function stringAttribute($identifier, $callback = null);

    /**
     * Restituisce l'attributo $attributeIdentifier degli oggetti correlati all'attributo $identifier
     * Se $attributeIdentifier = null restituisce gli oggetti
     *
     * @param string $identifier
     * @param string $attributeIdentifier
     *
     * @return array|null
     */
    public function stringRelatedObjectAttribute($identifier, $attributeIdentifier = null);
}
