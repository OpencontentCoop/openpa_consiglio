<?php

abstract class OpenPAConsiglioNotifiableFactory extends OCEditorialStuffPostNotifiableFactory
{
	public function handleEditorialStuffNotificationEvent( $event, OCEditorialStuffPostInterface $refer = null )
	{		
		$currentUser = eZUser::currentUser();
		/** @var eZUser $user */
		$user = eZUser::fetchByName('admin');
		eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
		
		$result = parent::handleEditorialStuffNotificationEvent( $event, $refer );

		eZUser::setCurrentlyLoggedInUser($currentUser, $currentUser->attribute('contentobject_id'));
		return $result;
	}
}