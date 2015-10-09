<?php

class ConsiglioApiController extends ezpRestMvcController
{

    protected function getErrorResult( Exception $exception )
    {
        if ( $exception instanceof ConsiglioApiException )
        {
            $result = new ezcMvcResult;
            $result->variables['message'] = $exception->getMessage();
            $result->status = new ConsiglioApiHttpErrorResponse( ezpHttpResponseCodes::SERVER_ERROR, $exception->getMessage(), $exception->getCode(), $exception->getErrorDetails() );
            return $result;
        }

        $result = new ezcMvcResult;
        $result->variables['message'] = $exception->getMessage();
        $result->status = new ConsiglioApiHttpErrorResponse( ezpHttpResponseCodes::SERVER_ERROR, $exception->getMessage(), $exception->getCode() );
        return $result;
    }

    public function doAuth()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $login = isset( $this->request->get['login'] ) ? $this->request->get['login'] : null;
            $password = isset( $this->request->get['password'] ) ? $this->request->get['password'] : null;
            $user = eZUser::loginUser( $login, $password );
            if ( !$user instanceof eZUser )
            {
                throw new ConsiglioApiException( "Authentication failed", ConsiglioApiException::AUTHENTICATION );
            }
            $result->variables = array(
                'result' => 'success',
                'user_id' => $user->id()
            );
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }
    
    public function doLoadSedutaList()
    {
        try
        {
            $parameters = array(
                'limit' => 20,
                'offset' => ( isset( $this->request->get['offset'] ) and is_numeric( $this->request->get['offset'] ) ) ? $this->request->get['offset'] : 0,
                'query'  => ( isset( $this->request->get['query'] ) and is_string( $this->request->get['query'] ) ) ? $this->request->get['query'] : false,
                'state' => ( isset( $this->request->get['state'] ) and is_numeric( $this->request->get['state'] ) ) ? $this->request->get['state'] : false,
                'interval'  => ( isset( $this->request->get['interval'] ) and is_string( $this->request->get['interval'] ) ) ? $this->request->get['interval'] : false,
                'tag'  => ( isset( $this->request->get['tag'] ) and is_string( $this->request->get['tag'] ) ) ? $this->request->get['tag'] : false
            );
            $sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems( $parameters );
            $result = new ezpRestMvcResult();
            /** @var Seduta $seduta */
            foreach( $sedute as $seduta )
            {
                if ($seduta->isVisibleByApp())
                {
                    $seduta = $seduta->jsonSerialize();
                    if ( $seduta )
                    {
                        $result->variables[] = $seduta;
                    }
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSeduta()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $result->variables = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id )->jsonSerialize();

            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSedutaDocumenti()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );

            foreach( $seduta->attribute( 'documenti' ) as $documento )
            {
                /** @var Allegato $documento */
                $result->variables[] = $documento->jsonSerialize();
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSedutaOdg()
    {
        try
        {
            $result = new ezpRestMvcResult();
            /** @var Punto[] $odg */
            $odg = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id )->attribute( 'odg' );
            /** @var Punto $punto */
            foreach( $odg as $punto )
            {
                if ( $punto->isVisibleByApp() )
                {
                    $validPunto = $punto->jsonSerialize();
                    if ( $validPunto )
                    {
                        $result->variables[] = $validPunto;
                    }
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSedutaPresenze()
    {
        try
        {
            $result = new ezpRestMvcResult();

            $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
            if ( $seduta instanceof Seduta )
            {
                /** @var OpenPAConsiglioPresenza[] $presenze */
                $startTime = ( isset( $this->request->get['start_time'] ) and is_numeric( $this->request->get['start_time'] ) ) ? $this->request->get['start_time'] : null;
                $inOut = isset( $this->request->get['in_out'] ) ? $this->request->get['in_out'] : null;
                $type = ( isset( $this->request->get['type'] ) and is_string( $this->request->get['type'] ) ) ? $this->request->get['type'] : null;
                $userId = ( isset( $this->request->get['user_id'] ) and is_numeric( $this->request->get['user_id'] ) ) ? $this->request->get['user_id'] : null;
                $presenze = $seduta->presenze( $startTime, $inOut, $type, $userId );
                foreach ( $presenze as $presenza )
                {
                    $validPresenza = $presenza->jsonSerialize();
                    if ( $validPresenza )
                    {
                        $result->variables[] = $validPresenza;
                    }
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSedutaPresenzeUtente()
    {
        try
        {
            $result = new ezpRestMvcResult();
            if ( !is_numeric( $this->UserId ) )
            {
                throw new ConsiglioApiException( "UserId not found", ConsiglioApiException::NOT_FOUND );
            }
            $data = array();
            $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
            if ( $seduta instanceof Seduta )
            {
                /** @var OpenPAConsiglioPresenza[] $presenze */
                foreach( array( 'checkin', 'beacons', 'manual' ) as $type )
                {
                    $data[$type] = null;
                    $presenze = OpenPAConsiglioPresenza::fetchObjectList(
                        OpenPAConsiglioPresenza::definition(),
                        null,
                        array(
                            'seduta_id' => $seduta->id(),
                            'user_id' => $this->UserId,
                            'type' => $type
                        ),
                        array( 'created_time' => 'desc' ),
                        array( 'limit' => 1, 'offset' => 0 )
                    );
                    foreach ( $presenze as $presenza )
                    {
                        $validPresenza = $presenza->jsonSerialize();
                        if ( $validPresenza )
                        {
                            $data[$type] = $validPresenza;
                        }
                    }
                }
            }
            $result->variables = $data;
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadSedutaVotazioni()
    {
        try
        {
            $result = new ezpRestMvcResult();

            $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
            if ( $seduta instanceof Seduta )
            {
                $votazioni = $seduta->votazioni();
                foreach ( $votazioni as $votazione )
                {
                    $validVotazione = $votazione->jsonSerialize();
                    if ( $validVotazione )
                    {
                        $result->variables[] = $validVotazione;
                    }
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doAddPresenzaSeduta()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
            if ( !$seduta instanceof Seduta )
            {
                throw new ConsiglioApiException( "Post {$this->Id} is not a valid Seduta", ConsiglioApiException::NOT_VALID );
            }
            $inOut = isset( $this->request->post['in_out'] ) ? $this->request->post['in_out'] : null;
            $type = isset( $this->request->post['type'] ) ? $this->request->post['type'] : null;
            $userId = isset( $this->request->post['user_id'] ) ? $this->request->post['user_id'] : eZUser::currentUserID();
            $presenza = $seduta->addPresenza( $inOut, $type, $userId );
            //$result->variables['result'] = 'success';
            $result->variables['presenza'] = $presenza->jsonSerialize();
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadVotazione()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $this->Id );
            if ( $votazione instanceof Votazione )
                $result->variables = $votazione->jsonSerialize();
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }
    
    public function doLoadVotazioneUserStatus()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $this->Id );
            if ( $votazione instanceof Votazione )
            {
                $values = array(
                    'user_id' => intval( $this->UserId ),
                    'votazione_id' => $votazione->id()
                );

                if ( $votazione->is( 'in_progress' ) )
                {
                    //@todo
                    $data = eZPersistentObject::fetchObjectList( OpenPAConsiglioVoto::definition(),
                        null,
                        array(
                            'votazione_id' => $votazione->id(),
                            'user_id' => intval( $this->UserId ),
                        ),
                        false,
                        null
                    );
                    $values['result'] = count( $data ) > 0 ? 'done' : 'waiting';
                }
                else
                {
                    $values['result'] = $votazione->currentState()->attribute( 'identifier' );
                }
                $result->variables = $values;
            }
            else
            {
                throw new ConsiglioApiException( "{$this->Id} is not a valid Votazione", ConsiglioApiException::NOT_VALID );
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doAddVotoVotazione()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $this->Id );
            if ( !$votazione instanceof Votazione )
            {
                throw new ConsiglioApiException( "Post {$this->Id} is not a valid Votazione", ConsiglioApiException::NOT_VALID );
            }
            $value = isset( $this->request->post['value'] ) ? $this->request->post['value'] : null;
            $userId = isset( $this->request->post['user_id'] ) ? $this->request->post['user_id'] : eZUser::currentUserID();
            $voto = $votazione->addVoto( $value, $userId );
            $result->variables['result'] = 'success';
            $result->variables['voto_id'] = $voto->attribute( 'id' );
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadPresenza()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $presenza = OpenPAConsiglioPresenza::fetch( $this->Id );
            if ( $presenza instanceof OpenPAConsiglioPresenza )
                $result->variables = $presenza->jsonSerialize();
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadPunto()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $result->variables = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id )->jsonSerialize();
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadPuntoDocumenti()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $punto = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id );

            foreach( $punto->attribute( 'documenti' ) as $documento )
            {
                /** @var Allegato $documento */
                $result->variables[] = $documento->jsonSerialize();
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    // @todo
    public function doLoadPuntoOsservazioni()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $punto = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id );
            foreach( $punto->attribute( 'osservazioni' ) as $osservazione )
            {
                /** @var Osservazione $osservazione */
                $result->variables[] = $osservazione->jsonSerialize();
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadPuntoVotazioni()
    {
        try
        {
            $result = new ezpRestMvcResult();

            $punto = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id );
            if ( $punto instanceof Punto )
            {
                $votazioni = $punto->votazioni();
                foreach ( $votazioni as $votazione )
                {
                    $validVotazione = $votazione->jsonSerialize();
                    if ( $validVotazione )
                    {
                        $result->variables[] = $validVotazione;
                    }
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadAllegato()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $allegato = OCEditorialStuffHandler::instance( 'allegati_seduta' )->fetchByObjectId( $this->Id );
            if ( $allegato instanceof Allegato )
                $result->variables = $allegato->jsonSerialize();
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doDownloadAllegato()
    {
        try
        {
            $allegato = OCEditorialStuffHandler::instance( 'allegati_seduta' )->fetchByObjectId( $this->Id );
            $result = false;
            if ( $allegato instanceof Allegato )
            {
                $fileHandler = eZBinaryFileHandler::instance();
                $attributeFile = $allegato->attributeFile() ;
                ob_start();
                $result = $fileHandler->handleDownload( $allegato->getObject(), $attributeFile, eZBinaryFileHandler::TYPE_FILE );
            }
            if ( $result == eZBinaryFileHandler::RESULT_UNAVAILABLE )
            {
                throw new ConsiglioApiException( "The specified file could not be found.", ConsiglioApiException::NOT_FOUND );
            }
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadUtente()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $object = eZContentObject::fetch( $this->Id );

            if ( $object instanceof eZContentObject )
            {

                $instances = OCEditorialStuffHandler::instances();
                if (array_key_exists($object->attribute( 'class_identifier' ), $instances))
                {
                    $user = OCEditorialStuffHandler::instance( $object->attribute( 'class_identifier' ) )->fetchByObjectId( $this->Id );
                    $result->variables = $user->jsonSerialize();
                }
            }
            return $result;
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }

    public function doLoadUtenteStatoPerSeduta()
    {
        try
        {
            $result = new ezpRestMvcResult();
            $object = eZContentObject::fetch( $this->Id );

            if ( $object instanceof eZContentObject )
            {

                $instances = OCEditorialStuffHandler::instances();
                if (array_key_exists($object->attribute( 'class_identifier' ), $instances))
                {
                    $user = OCEditorialStuffHandler::instance( $object->attribute( 'class_identifier' ) )->fetchByObjectId( $this->Id );
                    if ( $user instanceof Politico )
                    {
                        $result->variables = $user->lastData();
                        return $result;
                    }
                }
            }
            throw new ConsiglioApiException( "Politico {$this->Id} non trovato", ConsiglioApiException::NOT_FOUND );
        }
        catch( Exception $e )
        {
            return $this->getErrorResult( $e );
        }
    }
}