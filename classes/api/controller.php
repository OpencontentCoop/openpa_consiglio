<?php

class ConsiglioApiController extends ezpRestMvcController
{

    public function doAuth()
    {
        $result = new ezpRestMvcResult();
        $login = isset( $this->request->get['login'] ) ? $this->request->get['login'] : null;        
        $password = isset( $this->request->get['password'] ) ? $this->request->get['password'] : null;        
        $user = eZUser::loginUser( $login, $password );
        if ( !$user instanceof eZUser )
        {
            throw new Exception( "Authentication failed" );
        }        
        $result->variables = array(
            'result' => 'success',
            'user_id' => $user->id()
        );
        return $result;
    }
    
    public function doLoadSedutaList()
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
        foreach( $sedute as $seduta )
        {
            $seduta = $seduta->jsonSerialize();
            if ( $seduta )
            {
                $result->variables[] = $seduta;
            }
        }
        return $result;
    }

    public function doLoadSeduta()
    {
        $result = new ezpRestMvcResult();
        $result->variables = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id )->jsonSerialize();
        return $result;
    }

//    public function doLoadSedutaInfo()
//    {
//        $result = new ezpRestMvcResult();
//        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
//
//        $result->variables['referenti'] = $seduta->attribute( 'referenti' );
//        foreach( $seduta->attribute( 'referenti' ) as $referente )
//        {
//            $result->variables['referenti'][] = $referente;
//        }
//        return $result;
//    }

    public function doLoadSedutaDocumenti()
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

    public function doLoadSedutaOdg()
    {
        $result = new ezpRestMvcResult();
        /** @var Punto[] $odg */
        $odg = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id )->attribute( 'odg' );
        foreach( $odg as $punto )
        {
            $validPunto = $punto->jsonSerialize();
            if ( $validPunto )
            {
                $result->variables[] = $validPunto;
            }
        }
        return $result;
    }

    public function doLoadSedutaPresenze()
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

    public function doLoadSedutaPresenzeUtente()
    {
        $result = new ezpRestMvcResult();
        if ( !is_numeric( $this->UserId ) )
        {
            throw new Exception( "UserId not found" );
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

    public function doAddPresenzaSeduta()
    {
        $result = new ezpRestMvcResult();
        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );
        if ( !$seduta instanceof Seduta )
        {
            throw new Exception( "Post {$this->Id} is not a valid Seduta" );
        }
        $inOut = isset( $this->request->post['in_out'] ) ? $this->request->post['in_out'] : null;
        $type = isset( $this->request->post['type'] ) ? $this->request->post['type'] : null;
        $userId = isset( $this->request->post['user_id'] ) ? $this->request->post['user_id'] : eZUser::currentUserID();
        $presenza = $seduta->addPresenza( $inOut, $type, $userId );
        $result->variables['result'] = 'success';
        $result->variables['presenza_id'] = $presenza->attribute( 'id' );
        return $result;
    }

    public function doLoadPresenza()
    {
        $result = new ezpRestMvcResult();
        $presenza = OpenPAConsiglioPresenza::fetch( $this->Id );
        if ( $presenza instanceof OpenPAConsiglioPresenza )
            $result->variables = $presenza->jsonSerialize();
        return $result;
    }

    public function doLoadPunto()
    {
        $result = new ezpRestMvcResult();
        $result->variables = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id )->jsonSerialize();
        return $result;
    }

    public function doLoadPuntoDocumenti()
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

    // @todo
    public function doLoadPuntoOsservazioni()
    {
        $result = new ezpRestMvcResult();
        $punto = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id );

//        foreach( $punto->attribute( 'osservazioni' ) as $osservazione )
//        {
//            /** @var Osservazione $osservazione */
//            $result->variables[] = $osservazione->jsonSerialize();
//        }
        return $result;
    }

    // @todo
    public function doLoadPuntoVotazioni()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

    // @todo
    public function doSetPuntoStatoVotazione()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

    // @todo
    public function doAddVotazionePunto()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

    // @todo
    public function doLoadVotazione()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

    public function doLoadAllegato()
    {
        $result = new ezpRestMvcResult();
        $allegato = OCEditorialStuffHandler::instance( 'allegati_seduta' )->fetchByObjectId( $this->Id );
        if ( $allegato instanceof Allegato )
            $result->variables = $allegato->jsonSerialize();
        return $result;
    }

    public function doDownloadAllegato()
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
            throw new Exception( "The specified file could not be found." );
        }
    }

}