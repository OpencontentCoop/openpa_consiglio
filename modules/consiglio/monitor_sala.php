<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$currentUser = eZUser::currentUser();

$errors = array();
$sedutaId = $Params['SedutaID'];
$seduta = false;
if ( is_numeric( $sedutaId ) )
{
    try
    {
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
else
{
    $Result = array();

    $tpl->setVariable( 'errors', $errors );
    $tpl->setVariable( 'seduta', $seduta );
    $tpl->setVariable( 'title', 'Monitor' );

    $Result['content'] = $tpl->fetch( 'design:consiglio/monitor_sala.tpl' );
    $Result['pagelayout'] = 'consiglio/cruscotto_seduta_pagelayout.tpl';
}