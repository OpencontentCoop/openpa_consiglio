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
                Votazione::create(
                    $seduta,
                    $seduta->getPuntoInProgress(),
                    $http->postVariable( 'shortText' ),
                    $http->postVariable( 'text' ),
                    'default'
                );

            } break;

            case 'startVotazione':
            {
                $idVotazione = $http->postVariable( 'idVotazione' );
                /** @var Votazione $votazione */
                $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $idVotazione );
                $votazione->start();
            } break;

            case 'stopVotazione':
            {
                $idVotazione = $http->postVariable( 'idVotazione' );
                /** @var Votazione $votazione */
                $votazione = OCEditorialStuffHandler::instance( 'votazione' )->fetchByObjectId( $idVotazione );
                $votazione->stop();
            } break;

            case 'saveVerbale':
            {
                $seduta->saveVerbale( $http->postVariable( 'Verbale' ) );
            } break;

            default:
                throw new Exception( "Richiesta non valida" );
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
        $tpl->setVariable( 'errors', $errors );
        $tpl->setVariable( 'seduta', $seduta );
        $tpl->setVariable( 'title', 'Cruscotto' );
    
        $Result['content'] = $tpl->fetch( 'design:consiglio/cruscotto_seduta.tpl' );
    }
    $Result['pagelayout'] = 'consiglio/cruscotto_seduta_pagelayout.tpl';
}