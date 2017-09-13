<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Request\RequestInterface;

/**
 * Class Acl
 * @package Framework\RestApi\Listener
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
        if ($payload instanceof RequestInterface) {
            $routeParameters = $this->getApplication()->getDispatcher()->getRouteParameters();
            $method = $payload->getMethod();
            $uri = $payload->getUri();

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
    }

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
