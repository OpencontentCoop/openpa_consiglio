<?php /* #?ini charset="utf-8"?

[ApiProvider]
ProviderClass[consiglio]=ConsiglioApiProvider

[ConsiglioApiController_CacheSettings]
ApplicationCache=disabled

[Authentication]
RequireAuthentication=enabled
#AuthenticationStyle=ConsiglioFakeBasicAuthStyle
AuthenticationStyle=ezpRestBasicAuthStyle
DefaultUserID=

[RouteSettings]
SkipFilter[]=ConsiglioApiController_auth

*/ ?>
