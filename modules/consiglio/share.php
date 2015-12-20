<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$factoryIdentifier = $http->postVariable( 'Factory' );
$id = $http->postVariable( 'Id' );
try
{
    /** @var OCEditorialStuffPostInputActionInterface $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( method_exists( $post, 'share' ) )
    {
        $post->share();
    }
}
catch ( Exception $e )
{
    eZDebug::writeNotice( $e->getMessage(), __FILE__ );
}
if ( $http->hasPostVariable( 'AjaxMode' ) )
    eZExecution::cleanExit();
elseif ( $module->exitStatus() != eZModule::STATUS_REDIRECT  )
    $module->redirectTo( $http->postVariable( 'RedirectUrl', "editorialstuff/edit/{$factoryIdentifier}/{$id}" ) );
