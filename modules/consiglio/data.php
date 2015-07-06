<?php
/** @var eZModule $module */
$module = $Params['Module'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ID'];
$templatePath = str_replace( ':', '/', $Params['TemplatePath'] );

$odg = null;
try
{
    /** @var OCEditorialStuffPostInterface $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( $post->getObject()->attribute( 'can_read' ) )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'post', $post );
        $data = $tpl->fetch( 'design:' . $post->getFactory()->getTemplateDirectory() . '/' . $templatePath . '.tpl' );
    }
}
catch ( Exception $e )
{
    $data = $e->getMessage();
}

echo $data;

eZExecution::cleanExit();