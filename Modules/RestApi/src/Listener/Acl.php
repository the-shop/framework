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

            $aclRules = $this->getApplication()->getAclRules();

            // TODO: Hardcoded admin role -> read role from user
            $aclDefinedRoutes = $aclRules['admin']['routes'][$method];

            foreach ($routeParameters as $param => $value) {
                $modifiedParam = "{" . $param . "}";
                $uri = str_replace($value, $modifiedParam, $uri);
            }

            if (in_array($uri, $aclDefinedRoutes) === false) {
                throw new MethodNotAllowedException('Permission denied.', 403);
            }

            return $this;
        }
    }
}
