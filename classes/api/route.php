<?php

class ConsiglioApiRailsRoute extends ezcMvcRailsRoute
{
    /**
     * Holds protocol string.
     *
     * @var string|null
     */
    protected $protocol;

    /**
     * Constructs a new SensorRailsRoute with $pattern for $protocol.
     *
     * Accepted protocol format: http-get, http-post, http-put, http-delete
     * @see ezcMvcHttpRequestParser::processProtocol();
     *
     * @param string $pattern
     * @param string $controllerClassName
     * @param string $action
     * @param array $defaultValues
     * @param null|string $protocol Match specific protocol if string value, eg: 'http-get'
     */
    public function __construct( $pattern, $controllerClassName, $action = null, array $defaultValues = array(), $protocol = null )
    {
        $this->protocol = $protocol;
        parent::__construct( $pattern, $controllerClassName, $action, $defaultValues );
    }

    /**
     * Evaluates the URI against this route and protocol.
     *
     * @param ezcMvcRequest $request
     * @return ezcMvcRoutingInformation|null
     */
    public function matches( ezcMvcRequest $request )
    {
        if ( $this->protocol === null || $request->protocol === $this->protocol )
            return parent::matches( $request );

        return null;
    }
}