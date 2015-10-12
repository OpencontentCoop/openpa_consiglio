<?php /* #?ini charset="utf-8"?

[ApiProvider]
ProviderClass[consiglio]=ConsiglioApiProvider

[ConsiglioApiController_CacheSettings]
ApplicationCache=disabled

[Authentication]
RequireAuthentication=enabled
#AuthenticationStyle=ezpRestBasicAuthStyle
AuthenticationStyle=ConsiglioFakeBasicAuthStyle
DefaultUserID=

[RouteSettings]
SkipFilter[]=ConsiglioApiController_auth

*/ ?>
