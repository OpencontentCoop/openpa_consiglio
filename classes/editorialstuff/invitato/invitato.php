<?php

class Invitato extends OCEditorialStuffPost implements OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {

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
     * @see ConsiglioApiController
     * @return array
     */

    public function jsonSerialize()
    {
        //$locale = eZLocale::instance();

        // Ricavo l'ulr dell'immagine se presente
        $imageUrl = '';
        if ( $this->dataMap['image']->hasContent()
            && $this->dataMap['image']->attribute( 'data_type_string' ) == 'ezimage' )
        {
            $image = $this->dataMap['image']->content()->attribute( 'original' );
            $imageUrl = $image['url'];
            eZURI::transformURI( $imageUrl, false, 'full' );
        }

        return array(
            'id'                         => $this->id(),
            'type'                       => $this->object->ClassIdentifier,
            'nome'                       => $this->dataMap['nome']->content(),
            'cognome'                    => $this->dataMap['cognome']->content(),
            'email'                      => $this->dataMap['email']->content(),
            'ruolo'                      => $this->dataMap['ruolo']->content(),
            'struttura_di_appartenenza'  => '',
            'immagine'                   => $imageUrl
        );
    }
}
