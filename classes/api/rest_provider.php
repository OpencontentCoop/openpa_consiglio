<?php


class ConsiglioApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(
            'consiglioApiListSeduta' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/list', 'ConsiglioApiController', 'loadSedutaList', array() ), 1 ),
            'consiglioApiLoadSeduta' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id', 'ConsiglioApiController', 'loadSeduta', array(), 'http-get' ), 1 ),
            'consiglioApiLoadSedutaAllegati' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/allegati', 'ConsiglioApiController', 'loadSedutaAllegati', array(), 'http-get' ), 1 ),
            'consiglioApiLoadSedutaOdg' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/odg', 'ConsiglioApiController', 'loadSedutaOdg', array(), 'http-get' ), 1 ),

            'consiglioApiLoadPunto' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:Id', 'ConsiglioApiController', 'loadPunto', array(), 'http-get' ), 1 ),

            'consiglioApiAddPresenza' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:PuntoId/presenza', 'ConsiglioApiController', 'addPresenza', array(), 'http-post' ), 1 ),
            'consiglioApiAddVoto' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:PuntoId/voto', 'ConsiglioApiController', 'addVoto', array(), 'http-post' ), 1 ),
        );
        return $routes;
    }

    public function getViewController()
    {
        return new ConsiglioApiViewController();
    }

}
