<?php

class ConsiglioApiController extends ezpRestMvcController
{

    public function doLoadSedutaList()
    {
        $limit = isset( $this->request->get['limit'] ) ? $this->request->get['limit'] : 10;
        $offset = isset( $this->request->get['offset'] ) ? $this->request->get['offset'] : 0;
        $sedute = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems( array( 'limit' => $limit, 'offset' => $offset ) );
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

    public function doLoadSedutaInfo()
    {
        $result = new ezpRestMvcResult();
        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $this->Id );

        $result->variables['referenti'] = $seduta->attribute( 'referenti' );
        foreach( $seduta->attribute( 'referenti' ) as $referente )
        {
            $result->variables['referenti'][] = $referente;
        }
        return $result;
    }

    public function doLoadSedutaAllegati()
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

    public function doLoadPunto()
    {
        $result = new ezpRestMvcResult();
        $result->variables = OCEditorialStuffHandler::instance( 'punto' )->fetchByObjectId( $this->Id )->jsonSerialize();
        return $result;
    }

    public function doAddPresenza()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

    public function doAddVoto()
    {
        $result = new ezpRestMvcResult();
        return $result;
    }

}