<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$isAjax = $http->hasVariable( 'AjaxMode' );
$done = false;
if ( $http->hasVariable( 'AddMateria' ) )
{
    $type = 'materia/like';
    $id = $http->variable( 'AddMateria' );
    $userId = eZUser::currentUserID();
    if ( is_numeric( $id ) )
    {
        $exists = OCEditorialStuffNotificationRule::fetchPostsForUserID( $type, $userId );
        if ( !in_array( $id, $exists ) )
        {
            $rule = OCEditorialStuffNotificationRule::create( $type, $id, $userId );
            $rule->store();
            $done = true;
        }
    }
}
elseif ( $http->hasVariable( 'RemoveMateria' ) )
{
    $type = 'materia/like';
    $id = $http->variable( 'RemoveMateria' );
    $userId = eZUser::currentUserID();
    if ( is_numeric( $id ) )
    {
        $exists = OCEditorialStuffNotificationRule::fetchObject( OCEditorialStuffNotificationRule::definition(), null, array( 'type' => $type, 'user_id' => $userId, 'post_id' => $id ) );
        if ( $exists instanceof OCEditorialStuffNotificationRule )
        {
            $exists->remove();
            $done = true;
        }
    }
}

if ( $isAjax )
{
    echo json_encode( intval($done) );
    eZExecution::cleanExit();
}
$module->redirectTo( 'consiglio/dashboard' );
