<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$currentUser = eZUser::currentUser();

$errors = array();
$sedutaId = $Params['SedutaID'];
$action = $Params['Action'];
$actionParameters = $Params['ActionParameters'];
$seduta = false;
if ( is_numeric( $sedutaId ) )
{
    try
    {
        /** @var Seduta $seduta */
        $seduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchByObjectId( $sedutaId );
    }
    catch( Exception $e )
    {
        $errors[] = $e->getMessage();
    }
}

if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( '/' );
    return;
}
elseif ( $action )
{
    try
    {
        switch ( $action )
        {
            case 'markVotoValid':
            {
                $userId = $http->getVariable( 'uid' );
                $votoId = $http->getVariable( 'vid' );
            } break;
            
            case 'markVotoInvalid':
            {
                $userId = $http->getVariable( 'uid' );
                $votoId = $http->getVariable( 'vid' );
                $voto = OpenPAConsiglioVoto::fetch( $votoId );
                if ( $voto->attribute( 'user_id' ) == $userId )
                {
                    $voto->remove();
                }
            } break;
            
            case 'markPresente':
            {
                $userId = $http->getVariable( 'uid' );
                $seduta->addPresenza( 1, 'manual', $userId );
            } break;

            case 'markAssente':
            {
                $userId = $http->getVariable( 'uid' );
                $seduta->addPresenza( 0, 'manual', $userId );
            } break;
            
            case 'startSeduta':
            {
                $seduta->start();
            } break;

            case 'stopSeduta':
            {
                $seduta->stop();

            } break;

            case 'startPunto':
            {
                $puntoId = $actionParameters;
                foreach ( $seduta->odg() as $punto )
                {
                    if ( $punto->id() == $puntoId )
                    {
                        $punto->start();
                    }
                }
            } break;

            case 'stopPunto':
            {
                $puntoId = $actionParameters;
                foreach ( $seduta->odg() as $punto )
                {
                    if ( $punto->id() == $puntoId )
                    {
                        $punto->stop();
                    }
                }
                break;
            }

            case 'creaVotazione':
            {
                $puntoInProgress = $seduta->getPuntoInProgress();
                if ( $http->hasPostVariable( 'puntoId' ) )
                {
                    foreach ( $seduta->odg() as $punto )
                    {
                        if ( $punto->id() == $http->postVariable( 'puntoId' ) )
                        {
                            $puntoInProgress = $punto;
                        }
                    }
                }
                Votazione::create(
                    $seduta,
                    $puntoInProgress,
                    $http->postVariable( 'shortText' ),
                    $http->postVariable( 'text' ),
                    $http->hasPostVariable( 'type' ) ? $http->postVariable( 'type' ) : 'default'
                );

            } break;

            case 'startVotazione':
            {
                $votazioneInProgress = $seduta->getVotazioneInProgress();
                if ( $votazioneInProgress !== null )
                {
                    throw new ConsiglioApiException( "Una votazione è già aperta", ConsiglioApiException::VOTAZIONE_ALREADY_OPEN );
                }
                $idVotazione = $http->postVariable( 'idVotazione' );
                /** @var Votazione $votazione */
                $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $idVotazione );
                $votazione->start();
            } break;

            case 'stopVotazione':
            {
                $votazioneInProgress = $seduta->getVotazioneInProgress();
                if ( $votazioneInProgress === null )
                {
                    throw new ConsiglioApiException( "Non esistono votazioni aperte", ConsiglioApiException::VOTAZIONE_OPEN_NOT_FOUND );
                }
                $idVotazione = $http->postVariable( 'idVotazione' );
                if ( $idVotazione != $votazioneInProgress->id() )
                {
                    throw new ConsiglioApiException( "Si sta cercando di chiudere una votazione diversa da quella attualmente aperta", ConsiglioApiException::VOTAZIONE_TRY_CLOSE_NOT_OPEN );
                }
                $votazioneInProgress->stop();
            } break;

            case 'removeVotazione':
            {
                $idVotazione = $http->postVariable( 'idVotazione' );
                Votazione::removeByID( $idVotazione );
            } break;

            case 'launchMonitorVotazione':
            {
                $votazione = Votazione::getByID( $actionParameters );
                if ( $votazione instanceof Votazione )
                {
                    OpenPAConsiglioPushNotifier::instance()->emit(
                        'show_votazione',
                        $votazione->jsonSerialize()
                    );
                }
            } break;

            case 'launchMonitorPresenze':
            {                
                OpenPAConsiglioPushNotifier::instance()->emit(
                    'show_presenze',
                    $seduta->jsonSerialize()
                );
            } break;

            case 'launchMonitorVerbale':
            {                
                $identifier = $actionParameters ? $actionParameters : 'all';
                $payload = $seduta->jsonSerialize();
                $payload['show_verbale_part'] = $identifier;
                OpenPAConsiglioPushNotifier::instance()->emit(
                    'show_verbale',
                    $payload
                );
            } break;

            case 'launchMonitorPunto':
            {
                $puntoId = $actionParameters;
                foreach ( $seduta->odg() as $punto )
                {
                    if ( $punto->id() == $puntoId )
                    {
                        OpenPAConsiglioPushNotifier::instance()->emit(
                            'show_punto',
                            $punto->jsonSerialize()
                        );
                    }
                }
            } break;

            case 'saveVerbale':
            {
                $seduta->saveVerbale( $http->postVariable( 'Verbale' ) );
            } break;

            default:
                throw new ConsiglioApiException( "Richiesta non valida", ConsiglioApiException::NOT_VALID );
        }
    }
    catch( Exception $e )
    {
        $errors[] = $e->getMessage();
    }

    if ( count( $errors ) > 0 )
    {
        header('Content-Type: application/json');
        header( 'HTTP/1.1 500 Internal Server Error' );
        echo json_encode(
            array(
                'error_messages' => $errors
            )
        );
    }
    else
    {
        header('Content-Type: application/json');
        header( 'HTTP/1.1 200 OK' );
        echo json_encode(
            array(
                'response' => 'success',
                'action' => $action
            )
        );
    }
    eZExecution::cleanExit();
}
else
{
    $Result = array();

    if ( !$seduta )
    {
        $Result['content'] = $tpl->fetch( 'design:consiglio/cruscotto_seduta/select_seduta.tpl' );
    }
    else
    {
        $tpl->setVariable( 'enable_votazione', OpenPAConsiglioConfiguration::instance()->enableVotazioniinCruscotto() );
        $tpl->setVariable( 'use_app', OpenPAConsiglioConfiguration::instance()->useApp() );
        $tpl->setVariable( 'errors', $errors );
        $tpl->setVariable( 'seduta', $seduta );
        $tpl->setVariable( 'title', 'Cruscotto' );
    
        $Result['content'] = $tpl->fetch( 'design:consiglio/cruscotto_seduta.tpl' );
    }
    $Result['pagelayout'] = 'consiglio/cruscotto_seduta_pagelayout.tpl';
}
