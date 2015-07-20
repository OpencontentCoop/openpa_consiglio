<?php


class ConsiglioApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            'consiglioApiAuth' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/auth',
                    'ConsiglioApiController',
                    'auth',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiUtenteLoad' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/utente/:Id',
                    'ConsiglioApiController',
                    'loadUtente',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiUtenteLoadStatistiche' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/utente/:Id/statistiche',
                    'ConsiglioApiController',
                    'loadUtenteStatistiche',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaList' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/list',
                    'ConsiglioApiController',
                    'loadSedutaList',
                    array()
                ), 1
            ),
            'consiglioApiSedutaLoad' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id',
                    'ConsiglioApiController',
                    'loadSeduta',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaLoadDocumenti' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/documenti',
                    'ConsiglioApiController',
                    'loadSedutaDocumenti',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaLoadOdg' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/odg',
                    'ConsiglioApiController',
                    'loadSedutaOdg',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaLoadPresenze' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/presenze',
                    'ConsiglioApiController',
                    'loadSedutaPresenze',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaLoadPresenzeUtente' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/presenze/:UserId',
                    'ConsiglioApiController',
                    'loadSedutaPresenzeUtente',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaAddPresenza' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/presenza',
                    'ConsiglioApiController',
                    'addPresenzaSeduta',
                    array(),
                    'http-post'
                ), 1
            ),
            'consiglioApiSedutaLoadVotazioni' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/seduta/:Id/votazioni',
                    'ConsiglioApiController',
                    'loadSedutaVotazioni',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiVotazioneLoad' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/votazione/:Id',
                    'ConsiglioApiController',
                    'loadVotazione',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiVotazioneAddVoto' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/votazione/:Id',
                    'ConsiglioApiController',
                    'addVotoVotazione',
                    array(),
                    'http-post'
                ), 1
            ),
            'consiglioApiPresenzaLoad' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/presenza/:Id',
                    'ConsiglioApiController',
                    'loadPresenza',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiPuntoLoad' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/punto/:Id',
                    'ConsiglioApiController',
                    'loadPunto',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiPuntoLoadDocumenti' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/punto/:Id/documenti',
                    'ConsiglioApiController',
                    'loadPuntoDocumenti',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiPuntoLoadOsservazioni' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/punto/:Id/osservazioni',
                    'ConsiglioApiController',
                    'loadPuntoOsservazioni',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaLoadAllegato' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/allegato/:Id',
                    'ConsiglioApiController',
                    'loadAllegato',
                    array(),
                    'http-get'
                ), 1
            ),
            'consiglioApiSedutaDownloadAllegato' => new ezpRestVersionedRoute(
                new ConsiglioApiRailsRoute(
                    '/allegato/:Id/download',
                    'ConsiglioApiController',
                    'downloadAllegato',
                    array(),
                    'http-get'
                ), 1
            ),
        );

        return $routes;
    }

    public function getViewController()
    {
        return new ConsiglioApiViewController();
    }

}
