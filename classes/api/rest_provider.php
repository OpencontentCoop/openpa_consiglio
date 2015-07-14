<?php


class ConsiglioApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            'consiglioApiUtenteLoad' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/utente/:Id', 'ConsiglioApiController', 'loadUtente', array(), 'http-get' ), 1 ),
            'consiglioApiUtenteLoadStatistiche' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/utente/:Id/statistiche', 'ConsiglioApiController', 'loadUtenteStatistiche', array(), 'http-get' ), 1 ),

            'consiglioApiSedutaList' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/list', 'ConsiglioApiController', 'loadSedutaList', array() ), 1 ),
            'consiglioApiSedutaLoad' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id', 'ConsiglioApiController', 'loadSeduta', array(), 'http-get' ), 1 ),
            'consiglioApiSedutaLoadDocumenti' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/documenti', 'ConsiglioApiController', 'loadSedutaDocumenti', array(), 'http-get' ), 1 ),
            'consiglioApiSedutaLoadOdg' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/odg', 'ConsiglioApiController', 'loadSedutaOdg', array(), 'http-get' ), 1 ),
            'consiglioApiSedutaLoadPresenze' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/presenze', 'ConsiglioApiController', 'loadSedutaPresenze', array(), 'http-get' ), 1 ),
            'consiglioApiSedutaAddPresenza' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/seduta/:Id/presenza', 'ConsiglioApiController', 'addPresenzaSeduta', array(), 'http-post' ), 1 ),

            'consiglioApiPresenzaLoad' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/presenza/:Id', 'ConsiglioApiController', 'loadPresenza', array(), 'http-get' ), 1 ),

            'consiglioApiPuntoLoad' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:Id', 'ConsiglioApiController', 'loadPunto', array(), 'http-get' ), 1 ),
            'consiglioApiPuntoLoadDocumenti' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:Id/documenti', 'ConsiglioApiController', 'loadPuntoDocumenti', array(), 'http-get' ), 1 ),
            'consiglioApiPuntoLoadOsservazioni' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:Id/osservazioni', 'ConsiglioApiController', 'loadPuntoOsservazioni', array(), 'http-get' ), 1 ),
            'consiglioApiPuntoLoadVotazioni' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:Id/votazioni', 'ConsiglioApiController', 'loadPuntoVotazioni', array(), 'http-get' ), 1 ),
            'consiglioApiPuntoSetStatoVotazione' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:PuntoId/voto', 'ConsiglioApiController', 'setPuntoStatoVotazione', array(), 'http-post' ), 1 ),
            'consiglioApiPuntoAddVotazione' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/punto/:PuntoId/votazione', 'ConsiglioApiController', 'addVotazionePunto', array(), 'http-post' ), 1 ),

            'consiglioApiVotazioneLoad' => new ezpRestVersionedRoute( new ConsiglioApiRailsRoute( '/votazione/:Id', 'ConsiglioApiController', 'loadVotazione', array(), 'http-get' ), 1 ),
        );
        return $routes;
    }

    public function getViewController()
    {
        return new ConsiglioApiViewController();
    }

}
