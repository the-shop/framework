<?php

namespace Framework\RestApi\Listener;

use Firebase\JWT\JWT;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Auth\RequestAuthorization;
use Framework\Base\Event\ListenerInterface;

class AuthenticationListener implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     *
     * @return string
     */
    public function handle($payload): string
    {
        /**
         * @todo implement key generation, adjustable time on token expiration, algorithm selection
         */

        /**@var \Framework\Http\Request\RequestInterface $request */
        $request = $this->getApplication()->getRequest();
        $headers = $request->getHeaders();
        $requestAuth = new RequestAuthorization();
        JWT::$timestamp = time();
        $alg = 'HS384';
        $key = 'rV)7Djb{DpEpY5ex';

        if (isset($headers['Authorization']) === true) {
            $decodedJwt = JWT::decode(substr($headers['Authorization'], 7), $key, [$alg]);
            $requestAuth->setId($decodedJwt->modelId)
                        ->setResourceName($decodedJwt->resourceName)
                        ->setRole($decodedJwt->aclRole);

            if ($requestAuth->getId() !== null) {
                $requestAuth->setModel(
                    $this->getApplication()
                         ->getRepositoryManager()
                         ->getRepositoryFromResourceName($requestAuth->getResourceName())
                         ->loadOne($requestAuth->getId())
                );
            }
        }
        $payload = [
            'iss' => 'framework.the-shop.io',
            'exp' => JWT::$timestamp + 3600,
            'modelId' => $requestAuth->getId(),
            'resourceName' => $requestAuth->getResourceName(),
            'aclRole' => $requestAuth->getRole(),
        ];

        $jwt = JWT::encode($payload, $key, $alg);

        $this->getApplication()->setRequestAuthorization($requestAuth);

        $this->getApplication()
             ->getResponse()
             ->addHeader('Authorization', "Bearer $jwt");

        return $jwt;
    }
}
