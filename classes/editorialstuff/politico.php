<?php

class Politico extends OCEditorialStuffPost implements OCEditorialStuffPostInputActionInterface, OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;
    use SolrFieldsTrait;

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    ) {

    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'emails';
        $attributes[] = 'organi';
        $attributes[] = 'is_in';
        $attributes[] = 'percentuale_presenza';
        $attributes[] = 'importo_gettone';
        $attributes[] = 'livello_gettone';
        $attributes[] = 'rilevazioni_presenze';

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'emails') {
            return $this->emails();
        }

        if ($property == 'organi') {
            return $this->availableOrgani();
        }

        if ($property == 'is_in') {
            return $this->currentOrgani();
        }

        if ($property == 'percentuale_presenza') {
            return $this->getPercentualePresenza();
        }

        if ($property == 'importo_gettone') {
            return $this->getImportoGettone();
        }

        if ($property == 'livello_gettone') {
            return $this->getLivelloGettone();
        }

        if ($property == 'rilevazioni_presenze') {
            return $this->getRilevazioniPresenze();
        }

        return parent::attribute($property);
    }

    protected function emails()
    {
        $emails = array();
        $user = eZUser::fetch($this->object->attribute('id'));
        if ($user){
            $emails[] = $user->attribute('email');
        }        
        if (isset($this->dataMap['email']) && $this->dataMap['email']->hasContent()) {
            $emails[] = $this->dataMap['email']->content();
        }

        if (isset($this->dataMap['altre_email']) && $this->dataMap['altre_email']->hasContent()) {
            $emails = array_merge($emails, explode('&', $this->dataMap['altre_email']->toString()));
        }

        return array_unique($emails);
    }

    protected function availableOrgani()
    {
        $data = array();
        /** @var Organo[] $organi */
        $organi = OCEditorialStuffHandler::instance('organo')->fetchItems(array('limit' => 50, 'offset' => 0));
        foreach($organi as $organo) {
            $data[$organo->getObject()->attribute('name')] = $organo->id();
        }

        return $data;
    }

    protected function currentOrgani()
    {
        /** @var Organo[] $organi */
        $organi = OCEditorialStuffHandler::instance('organo')->fetchItems(array('limit' => 50, 'offset' => 0));

        $data = array();
        foreach ($organi as $organo) {
            $data[$organo->getObject()->attribute('name')] = in_array($this->id(), $organo->getComponenti(false));
        }

        return $data;
    }

    protected function addToOrgano($id)
    {
        /** @var Organo $organo */
        $organo = OCEditorialStuffHandler::instance('organo')->fetchByObjectId($id);

        if($organo instanceof Organo){
            $organo->addComponente($this);
        }
    }

    protected function removeFromOrgano($id)
    {
        /** @var Organo $organo */
        $organo = OCEditorialStuffHandler::instance('organo')->fetchByObjectId($id);

        if($organo instanceof Organo){
            $organo->removeComponente($this);
        }
    }

    public function executeAction($actionIdentifier, $actionParameters, eZModule $module = null)
    {
        if ($actionIdentifier == 'AddToOrgano' && isset( $actionParameters['organo'] )) {
            $this->addToOrgano($actionParameters['organo']);
        }

        if ($actionIdentifier == 'RemoveFromOrgano' && isset( $actionParameters['organo'] )) {
            $this->removeFromOrgano($actionParameters['organo']);
        }
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
        if ($this->dataMap['email']->hasContent()) {
            $email [] = $this->dataMap['email']->content();
        }

        if ($this->dataMap['altre_email']->hasContent()) {
            $email = array_merge(
                $email,
                explode('&', $this->dataMap['altre_email']->toString())
            );
        }

        // Ricavo l'url dell'immagine se presente
        $imageUrl = '';
        if ($this->dataMap['image']->hasContent()
            && $this->dataMap['image']->attribute('data_type_string') == 'ezimage'
        ) {
            $image = $this->dataMap['image']->content()->attribute('squaremedium');
            $imageUrl = $image['url'];
            eZURI::transformURI($imageUrl, false, 'full');
        }

        $rappresentante = '';
        if ($this->dataMap['rappresentante']->hasContent()
            && $this->dataMap['rappresentante']->attribute('data_type_string') == 'ezobjectrelationlist'
        ) {
            $relationIds = explode('-', $this->dataMap['rappresentante']->toString());
            foreach ($relationIds as $relationId) {
                $relation = eZContentObject::fetch($relationId);
                if ($relation instanceof eZContentObject) {
                    $rappresentante .= $relation->attribute('name') . ' ';
                }
            }
        }
        $rappresentante = trim($rappresentante);
        if (empty( $rappresentante )) {
            $rappresentante = $this->object->ClassIdentifier;
        }

        return array(
            'id' => $this->id(),
            'type' => $rappresentante,
            'nome' => $this->dataMap['nome']->content(),
            'cognome' => $this->dataMap['cognome']->content(),
            'email' => array_unique($email),
            'ruolo' => $this->dataMap['ruolo']->content(),
            'struttura_di_appartenenza' => $this->stringRelatedObjectAttribute(
                'gruppo_politico',
                'titolo'
            ),
            'immagine' => $imageUrl
        );
    }

    /**
     * Individua la seduta in_progress o closed di oggi a cui il politico dovrebbe partecipare
     */
    protected function lastSedutaInProgressOrClosed()
    {
        $organoIds = array();
        $currentOrgani = $this->currentOrgani();
        foreach ($this->availableOrgani() as $identifier => $id) {
            if ($currentOrgani[$identifier]) {
                $organoIds[] = $id;
            }
        }
        if (!empty( $organoIds )) {
            $organoFilters = count($organoIds) > 1 ? array('or') : array();
            foreach ($organoIds as $nodeId) {
                $organoFilters[] = self::generateSolrSubMetaField('organo', 'id') . ':' . $nodeId;
            }

            $sedute = OCEditorialStuffHandler::instance('seduta')->fetchItems(
                array(
                    'filters' => $organoFilters,
                    'state' => array('in_progress', 'closed'),
                    'sort' => array('meta_published_dt' => 'desc'),
                    'limit' => 1,
                    'offset' => 0
                )
            );
            if (isset( $sedute[0] )) {
                return $sedute[0];
            }
        }

        return null;
    }

    public function lastData()
    {
        $data = array(
            'seduta' => array(
                'id' => null,
                'stato' => null,
                'data_svolgimento' => null,
                'timestamp' => null,
                '_timestamp_readable' => null,
                'presenza' => array(
                    'id' => null,
                    'in_out' => null,
                    'timestamp' => null,
                    '_timestamp_readable' => null,
                ),
                'punto' => array(
                    'id' => null,
                    'stato' => null,
                    'timestamp' => null,
                    '_timestamp_readable' => null,
                ),
                'votazione' => array(
                    'id' => null,
                    'stato' => null,
                    'timestamp' => null,
                    '_timestamp_readable' => null,
                    'short_text' => null,
                    'text' => null,
                    'punto_id' => null,
                    'user_voted' => 'null'
                ),
            )
        );
        $seduta = $this->lastSedutaInProgressOrClosed();
        if ($seduta instanceof Seduta) {
            try {
                if ($seduta instanceof Seduta) {
                    $data['seduta']['id'] = $seduta->id();
                    $data['seduta']['stato'] = $seduta->currentState()->attribute('identifier');
                    $data['seduta']['data_svolgimento'] = $seduta->dataOra(Seduta::DATE_FORMAT);
                    $data['seduta']['timestamp'] = $seduta->getObject()->attribute('modified');
                    $data['seduta']['_timestamp_readable'] = date(Seduta::DATE_FORMAT,
                        $seduta->getObject()->attribute('modified'));

                    $lastPresenza = OpenPAConsiglioPresenza::fetchLastByUserIDAndSedutaID($this->id(), $seduta->id());
                    if ($lastPresenza instanceof OpenPAConsiglioPresenza) {
                        $data['seduta']['presenza']['id'] = intval($lastPresenza->attribute('id'));
                        $data['seduta']['presenza']['in_out'] = intval($lastPresenza->attribute('in_out'));
                        $data['seduta']['presenza']['timestamp'] = $lastPresenza->attribute('created_time');
                        $data['seduta']['presenza']['_timestamp_readable'] = date(Seduta::DATE_FORMAT, $lastPresenza->attribute('created_time'));
                        $data['seduta']['presenza']['type'] = $lastPresenza->attribute('type');
                    } else {
                        $data['seduta']['presenza'] = null;
                    }

                    // ricavo punto attivo
                    $punto = $seduta->getPuntoInProgress();
                    if (!$punto instanceof Punto) {
                        $punto = $seduta->getPuntoLastClosed();
                    }
                    if ($punto instanceof Punto) {
                        $data['seduta']['punto']['id'] = $punto->id();
                        $data['seduta']['punto']['name'] = $punto->getObject()->attribute('name');
                        $data['seduta']['punto']['stato'] = $punto->currentState()->attribute('identifier');
                        $data['seduta']['punto']['timestamp'] = $punto->getObject()->attribute('modified');
                        $data['seduta']['punto']['_timestamp_readable'] = date(Seduta::DATE_FORMAT, $punto->getObject()->attribute('modified'));
                    } else {
                        $data['seduta']['punto'] = null;
                    }

                    // ricavo ultima votazione
                    $votazione = $seduta->getVotazioneLastModified();
                    if ($votazione instanceof Votazione) {
                        $data['seduta']['votazione']['id'] = $votazione->id();
                        $data['seduta']['votazione']['stato'] = $votazione->currentState()->attribute('identifier');
                        $data['seduta']['votazione']['short_text'] = $votazione->stringAttribute(Votazione::$shortTextIdentifier);
                        $data['seduta']['votazione']['text'] = $votazione->stringAttribute(Votazione::$textIdentifier);
                        $data['seduta']['votazione']['punto_id'] = $votazione->stringAttribute(Votazione::$puntoIdentifier, 'intval');
                        $data['seduta']['votazione']['timestamp'] = $votazione->getObject()->attribute('modified');
                        $data['seduta']['votazione']['_timestamp_readable'] = date(Seduta::DATE_FORMAT, $votazione->getObject()->attribute('modified'));
                        $data['seduta']['votazione']['user_voted'] = $votazione->userAlreadyVoted($this->id());
                    } else {
                        $data['seduta']['votazione'] = null;
                    }

                }
            } catch (Exception $e) {
                $data['seduta'] = null;
            }
        } else {
            $data['seduta'] = null;
        }

        return $data;
    }

    public function getTimelinePresenza(Seduta $seduta)
    {
        return new OpenPAConsiglioPresenzaArrayAccess($this);
    }

    public function getPercentualePresenza()
    {
        return new OpenPAConsiglioPresenzaArrayAccess($this, 'percent');
    }

    public function getImportoGettone()
    {
        return new OpenPAConsiglioPresenzaArrayAccess($this, 'importo');
    }

    public function getLivelloGettone()
    {
        return new OpenPAConsiglioPresenzaArrayAccess($this, 'livello');
    }

    public function getRilevazioniPresenze()
    {
        return new OpenPAConsiglioPresenzaArrayAccess($this, 'presenze');
    }

}
