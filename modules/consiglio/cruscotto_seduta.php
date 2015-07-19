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
            case 'startSeduta':
            {
                $seduta->start();
                break;
            }
            case 'stopSeduta':
            {
                $seduta->stop();
                break;
            }
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
                break;
            }
        }
    }
    catch( Exception $e )
    {
        $errors[] = $e->getMessage();
    }
    header('Content-Type: application/json');
    if ( count( $errors ) > 0 )
    {
        header( 'HTTP/1.1 500 Internal Server Error' );
        echo json_encode(
            array(
                'error_messages' => $errors
            )
        );
    }
    eZExecution::cleanExit();
}
else
{
    $Result = array();

    $tpl->setVariable( 'errors', $errors );
    $tpl->setVariable( 'seduta', $seduta );
    $tpl->setVariable( 'title', 'Cruscotto' );

    $Result['content'] = $tpl->fetch( 'design:consiglio/cruscotto_seduta.tpl' );
    $Result['pagelayout'] = 'consiglio/cruscotto_seduta_pagelayout.tpl';
}