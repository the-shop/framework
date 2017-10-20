<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Event\ListenerInterface;

/**
 * Class Acl
 * @package Framework\RestApi\Listeners
 */
class Acl implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return $this
     * @throws MethodNotAllowedException
     */
    public function handle($payload)
    {
        $request = $this->getApplication()->getRequest();

        $routeParameters = $this->getApplication()->getDispatcher()->getRouteParameters();
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Remove route prefix
        $routePrefix = $this->getApplication()->getConfiguration()->getPathValue('routePrefix');
        $uri = str_replace($routePrefix, '', $uri);

        // Transform uri to actually registered route so we can compare that route with acl
        foreach ($routeParameters as $param => $value) {
            $modifiedParam = "{" . $param . "}";
            $uri = str_replace($value, $modifiedParam, $uri);
        }

        $aclRoutesRules = $this->getApplication()->getAclRules()['routes'];

        // If route is public and allowed for user role, ALLOW
        if ($this->checkRoutes($uri, $aclRoutesRules['public'][$method]) === true) {
            return $this;
        }

        // If route is private and allowed for user role, ALLOW
        if ($this->checkRoutes($uri, $aclRoutesRules['private'][$method])) {
            return $this;
        }

        // If no rules defined for this route and user role, DENY
        throw new MethodNotAllowedException('Permission denied.', 403);
    }

    /**
     * @param string $requestedRoute
     * @param array $routesDefinition
     * @return bool
     */
    private function checkRoutes(string $requestedRoute, array $routesDefinition = [])
    {

        // TODO: Hardcoded admin role -> read role from user, don't leave it hardcoded
        foreach ($routesDefinition as $routeDefinition) {
            if ($routeDefinition['route'] === $requestedRoute
                && in_array('admin', $routeDefinition['allows']) === true) {
                return true;
            }
        }

        return false;
    }
}
