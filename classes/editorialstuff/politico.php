<?php

class Politico extends OCEditorialStuffPost implements OCEditorialStuffPostInputActionInterface
{

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {

    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'locations';
        $attributes[] = 'is_in';
        return $attributes;
    }

    public function attribute( $property )
    {
        if ( $property == 'locations' )
        {
            return $this->availableLocations();
        }

        if ( $property == 'is_in' )
        {
            return $this->currentLocations( );
        }

        return parent::attribute( $property );
    }

    protected function availableLocations()
    {
        $data = array();
        $configuration = $this->getFactory()->getConfiguration();
        foreach( $configuration['Locations'] as $identifier => $nodeId )
        {
            $data[$identifier] = eZContentObjectTreeNode::fetch( $nodeId );
        }
        return $data;
    }

    protected function currentLocations()
    {
        $configuration = $this->getFactory()->getConfiguration();
        $locations = $configuration['Locations'];

        $data = array();
        foreach( $locations as $identifier => $nodeId )
        {
            $data[$identifier] = false;
            /** @var eZContentObjectTreeNode[] $assignedNodes */
            $assignedNodes = $this->getObject()->assignedNodes();
            foreach( $assignedNodes as $node )
            {
                if ( $node->attribute( 'parent' )->attribute( 'node_id' ) == $nodeId )
                {
                    $data[$identifier] = true;
                    break;
                }
            }
        }
        return $data;
    }

    protected function addLocation( $slug )
    {
        $configuration = $this->getFactory()->getConfiguration();
        $nodeId = $configuration['Locations'][$slug];
        if ( $nodeId )
        {
            $object = $this->getObject();
            if ( $object instanceof eZContentObject )
            {
                eZContentOperationCollection::addAssignment(
                    $object->attribute( 'main_node_id' ),
                    $object->attribute( 'id' ),
                    array( $nodeId )
                );
            }
        }
    }

    protected function removeLocation( $slug )
    {
        $configuration = $this->getFactory()->getConfiguration();
        $nodeId = $configuration['Locations'][$slug];
        $object = $this->getObject();
        if ( $object instanceof eZContentObject )
        {
            /** @var eZContentObjectTreeNode[] $nodes */
            $nodes = $object->attribute( 'assigned_nodes' );
            $removeNodeIdList = array();
            if ( count( $nodes ) > 1 )
            {
                foreach ( $nodes as $node )
                {
                    if ( $node->attribute( 'parent_node_id' ) == $nodeId )
                    {
                        $removeNodeIdList[] = $node->attribute( 'node_id' );
                    }
                }
            }
            if ( !empty( $removeNodeIdList ) )
            {
                eZContentOperationCollection::removeNodes( $removeNodeIdList );
            }
        }
    }

    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null )
    {
        if ( $actionIdentifier == 'AddLocation' && isset( $actionParameters['location'] ) )
        {
            $this->addLocation( $actionParameters['location'] );
        }

        if ( $actionIdentifier == 'RemoveLocation' && isset( $actionParameters['location'] ) )
        {
            $this->removeLocation( $actionParameters['location'] );
        }
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

        $rappresentante = '';
        if ( $this->dataMap['rappresentante']->hasContent()
             && $this->dataMap['rappresentante']->attribute( 'data_type_string' ) == 'ezobjectrelationlist'
        )
        {
            $relationIds = explode( '-', $this->dataMap['rappresentante']->toString() );
            foreach( $relationIds as $relationId )
            {
                $relation = eZContentObject::fetch( $relationId );
                if ( $relation instanceof eZContentObject )
                {
                    $rappresentante .= $relation->attribute( 'name' ) . ' ';
                }
            }
        }
        $rappresentante = trim( $rappresentante );
        if ( empty( $rappresentante ) )
        {
            $rappresentante = $this->object->ClassIdentifier;
        }

        return array(
            'id' => $this->id(),
            'type' => $rappresentante,
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
        // ricavo la seduta dalla presenza
        $lastPresenza = OpenPAConsiglioPresenza::fetchLastByUserID( $this->id() );
        if ( $lastPresenza instanceof OpenPAConsiglioPresenza )
        {
            try
            {
                /** @var Seduta $seduta */
                $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $lastPresenza->attribute( 'seduta_id' ) );
                if ( $seduta instanceof Seduta )
                {
                    $data['seduta']['id'] = $seduta->id();
                    $data['seduta']['stato'] = $seduta->currentState()->attribute( 'identifier' );
                    $data['seduta']['data_svolgimento'] = $seduta->dataOra( Seduta::DATE_FORMAT );

                    // ricavo timestamp di ultimo stato
                    $lastSedutaHistory = OCEditorialStuffHistory::getLastHistoryByObjectIdAndType( $seduta->id(), 'updateobjectstate' );
                    if ( $lastSedutaHistory instanceof OCEditorialStuffHistory )
                    {
                        $data['seduta']['timestamp'] = $lastSedutaHistory->attribute( 'created_time' );
                        $data['seduta']['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastSedutaHistory->attribute( 'created_time' ) );
                    }

                    $data['seduta']['presenza']['id'] = intval( $lastPresenza->attribute( 'id' ) );
                    $data['seduta']['presenza']['in_out'] = intval( $lastPresenza->attribute( 'in_out' ) );
                    $data['seduta']['presenza']['timestamp'] = $lastPresenza->attribute( 'created_time' );
                    $data['seduta']['presenza']['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastPresenza->attribute( 'created_time' ) );
                    $data['seduta']['presenza']['type'] = $lastPresenza->attribute( 'type' );

                    // ricavo punto attivo
                    $punto = null;
                    $puntoInProgress = $seduta->getPuntoInProgress();
                    if ( $puntoInProgress instanceof Punto )
                    {
                        $punto = $puntoInProgress;
                    }
                    else
                    {
                        foreach ( $seduta->odg() as $puntoOdg )
                        {
                            if ( $puntoOdg->currentState()->attribute( 'identifier' ) == 'closed' )
                            {
                                $punto = $puntoOdg;
                            }
                        }
                    }

                    if ( $punto instanceof Punto )
                    {
                        $data['seduta']['punto']['id'] = $punto->id();
                        $data['seduta']['punto']['stato'] = $punto->currentState()->attribute( 'identifier' );

                        // ricavo timestamp di ultimo stato
                        $lastPuntoHistory = OCEditorialStuffHistory::getLastHistoryByObjectIdAndType( $punto->id(), 'updateobjectstate' );
                        if ( $lastPuntoHistory instanceof OCEditorialStuffHistory )
                        {
                            $data['seduta']['punto']['timestamp'] = $lastPuntoHistory->attribute( 'created_time' );
                            $data['seduta']['punto']['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastPuntoHistory->attribute( 'created_time' ) );
                        }
                    }
                    else
                    {
                        $data['seduta']['punto'] = null;
                    }

                    // ricavo ultima votazione
                    $votazione = $seduta->getVotazioneInProgress();
                    if ( !$votazione instanceof Votazione )
                    {
                        $votazioni = Votazione::getBySedutaID( $seduta->id() );
                        if ( is_array( $votazioni ) && $votazioni[0] instanceof Votazione )
                        {
                            $votazione = $votazioni[0];
                        }
                    }
                    if ( $votazione instanceof Votazione )
                    {
                        $jsonArrayVotazione = $votazione->jsonSerialize();
                        $data['seduta']['votazione']['id'] = $jsonArrayVotazione['id'];
                        $data['seduta']['votazione']['stato'] = $jsonArrayVotazione['stato'];
                        $data['seduta']['votazione']['short_text'] = $jsonArrayVotazione['short_text'];
                        $data['seduta']['votazione']['text'] = $jsonArrayVotazione['text'];
                        $data['seduta']['votazione']['punto_id'] = $jsonArrayVotazione['punto_id'];
                        // ricavo timestamp di ultimo stato
                        $lastVotazioneHistory = OCEditorialStuffHistory::getLastHistoryByObjectIdAndType( $votazione->id(), 'updateobjectstate' );
                        if ( $lastVotazioneHistory instanceof OCEditorialStuffHistory )
                        {
                            $data['seduta']['votazione']['timestamp'] = $lastVotazioneHistory->attribute( 'created_time' );
                            $data['seduta']['votazione']['_timestamp_readable'] = date( Seduta::DATE_FORMAT, $lastVotazioneHistory->attribute( 'created_time' ) );
                        }
                        $data['seduta']['votazione']['user_voted'] = $votazione->userAlreadyVoted( $this->id() );
                    }
                    else
                    {
                        $data['seduta']['votazione'] = null;
                    }

                }
            }
            catch( Exception $e )
            {
                $data['seduta'] = null;
            }
        }
        else
        {
            $data['seduta'] = null;
        }

        return $data;
    }
}
