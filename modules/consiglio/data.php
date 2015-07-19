<?php
/** @var eZModule $module */
$module = $Params['Module'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ID'];
$templatePath = str_replace( ':', '/', $Params['TemplatePath'] );
$data = null;
$odg = null;
try
{
    /** @var OCEditorialStuffPostInterface $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( $post->getObject()->attribute( 'can_read' ) )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'post', $post );
        if ( strpos( $templatePath, '/' ) > 0 )
        {
            $templatePath = $post->getFactory()->getTemplateDirectory() . '/' . $templatePath;
        }
        else
        {
            $templatePath = ltrim( $templatePath, '/' );
        }
        $data = $tpl->fetch( 'design:' . $templatePath . '.tpl' );
    }
}
catch ( Exception $e )
{
    $data = $e->getMessage();
}

echo $data;
//eZDisplayDebug();
eZExecution::cleanExit();