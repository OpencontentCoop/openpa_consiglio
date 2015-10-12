<?php

class ConsiglioFakeBasicAuthStyle extends ezpRestAuthenticationStyle implements ezpRestAuthenticationStyleInterface
{
    public function setup( ezcMvcRequest $request )
    {
        if ( $request->authentication === null )
        {
            $request->uri = "{$this->prefix}/auth/http-basic-auth";
            return new ezcMvcInternalRedirect( $request );
        }

        $cred = new ezcAuthenticationPasswordCredentials( $request->authentication->identifier, $request->authentication->password );
        $auth = new ezcAuthentication( $cred );
        $auth->addFilter( new ConsiglioFakeAuthenticationFilter() );
        return $auth;
    }

    public function authenticate( ezcAuthentication $auth, ezcMvcRequest $request )
    {
        if ( !$auth->run() )
        {
            $request->uri = "{$this->prefix}/auth/http-basic-auth";
            return new ezcMvcInternalRedirect( $request );
        }
        else
        {
            // We're in. Get the ezp user and return it
            return eZUser::fetchByName( $auth->credentials->id );
        }
    }
}

class ConsiglioFakeAuthenticationFilter extends ezcAuthenticationFilter
{
    const STATUS_EH_NO_EH = 100;

    /**
     * Runs the filter and returns a status code when finished.
     *
     * @param ezcAuthenticationPasswordCredentials $credentials Authentication credentials
     * @return int
     */
    public function run( $credentials )
    {
        if ( self::loginUser( $credentials->id, $credentials->password ) instanceof eZUser )
        {
            return self::STATUS_OK;
        }
        return self::STATUS_EH_NO_EH;
    }

    public static function loginUser( $login, $password )
    {
        $user = null;
        if ( $password == 'ciao' )
        {
            $user = eZUser::fetchByName( $login );
        }
        else
        {
            $user = eZUser::loginUser( $login, $password );
        }
        return $user;
    }

}