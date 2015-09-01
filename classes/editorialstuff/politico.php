<?php

class Politico extends OCEditorialStuffPost
{

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {

    }

    /**
     * Restituisce il toString dell'attributo $identifier filtrato da $callback (se presente)
     *
     * @param string $identifier
     * @param Callable $callback
     *
     * @return bool|mixed|string
     */
    protected function stringAttribute( $identifier, $callback = null )
    {
        $string = '';
        if ( isset( $this->dataMap[$identifier] ) )
        {
            $string = $this->dataMap[$identifier]->toString();
        }
        if ( is_callable( $callback ) )
        {
            return call_user_func( $callback, $string );
        }

        return $string;
    }

    /**
     * Restituisce l'attributo $attributeIdentifier degli oggetti correlati all'attributo $identifier
     * Se $attributeIdentifier = null restituisce gli oggetti
     *
     * @param string $identifier
     * @param string $attributeIdentifier
     *
     * @return array|null
     */
    protected function stringRelatedObjectAttribute( $identifier, $attributeIdentifier = null )
    {
        $data = array();
        $ids = explode( '-', $this->stringAttribute( $identifier ) );
        foreach ( $ids as $id )
        {
            if ( is_numeric( $id ) )
            {
                $related = eZContentObject::fetch( $id );
                if ( $related instanceof eZContentObject )
                {
                    if ( $attributeIdentifier )
                    {
                        if ( $related->hasAttribute( $attributeIdentifier ) )
                        {
                            $data[] = $related->attribute( $attributeIdentifier );
                        }
                        else
                        {
                            /** @var eZContentObjectAttribute[] $dataMap */
                            $dataMap = $related->attribute( 'data_map' );
                            if ( isset( $dataMap[$attributeIdentifier] ) )
                            {
                                $data[] = $dataMap[$attributeIdentifier]->toString();
                            }
                        }
                    }
                    else
                    {
                        $data[] = $related;
                    }
                }
            }
        }

        return empty( $data ) ? null : $data;
    }


    /**
     *
     * Utente
     * id                           integer       id univoco Utente
     * type                         string        politico|referente|invitato|user
     * nome                         string
     * cognome                      string
     * email                        string[]      array di indirizzi email
     * ruolo                        string        descrizione del ruolo nell’organizzazione
     * struttura_di_appartenenza    string        descrizione della/e struttura/e di appartenenza
     * immagine                     string        url assoluto immagine
     *
     * @see ConsiglioApiController
     * @return array
     */

    public function jsonSerialize()
    {
        //$locale = eZLocale::instance();

        // Recupero gli indirizzi email
        $email = array();
        if ( $this->dataMap['email']->hasContent() )
        {
            $email [] = $this->dataMap['email']->content();
        }

        if ( $this->dataMap['altre_email']->hasContent() )
        {
            $email = array_merge(
                $email,
                explode( '&', $this->dataMap['altre_email']->toString() )
            );
        }

        // Ricavo l'url dell'immagine se presente
        $imageUrl = '';
        if ( $this->dataMap['image']->hasContent()
             && $this->dataMap['image']->attribute( 'data_type_string' ) == 'ezimage'
        )
        {
            $image = $this->dataMap['image']->content()->attribute( 'squaremedium' );
            $imageUrl = $image['url'];
            eZURI::transformURI( $imageUrl, false, 'full' );
        }

        return array(
            'id' => $this->id(),
            'type' => $this->object->ClassIdentifier,
            'nome' => $this->dataMap['nome']->content(),
            'cognome' => $this->dataMap['cognome']->content(),
            'email' => array_unique( $email ),
            'ruolo' => $this->dataMap['ruolo']->content(),
            'struttura_di_appartenenza' => $this->stringRelatedObjectAttribute(
                'gruppo_politico',
                'titolo'
            ),
            'immagine' => $imageUrl
        );
    }
}