<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
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
     */
    public function handle($payload)
    {
        if ($payload instanceof RequestInterface) {
            $aclRules = $this->getApplication()->getAclRules();

            $method = $payload->getMethod();
            $uri = $payload->getUri();
        }
    }
}
