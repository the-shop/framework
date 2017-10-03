<?php

namespace Framework\Base\Auth\Controller;

use Firebase\JWT\JWT;
use Framework\Base\Application\Exception\AuthenticationException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Http\Controller\Http;

/**
 * Class AuthController
 * @package Framework\Base\Auth\Controller
 */
class AuthController extends Http
{
    /**
     * @return string
     * @throws \Framework\Base\Application\Exception\AuthenticationException
     * @throws \RuntimeException
     */
    public function authenticate()
    {
        $authModels = $this->getRepositoryManager()->getAuthenticatableModels();
        $post = $this->getPost();
        $model = $exception = null;
        $attemptStrategies = [];

        foreach ($authModels as $resourceName => $params) {
            if (count(array_diff($params['credentials'], array_keys($post))) === 0 &&
                count($post) === count($params['credentials'])
            ) {
                $attemptStrategies[] = [
                    'repository' => $this->getRepositoryFromResourceName($resourceName),
                    'class' => '\\Framework\\Base\\Auth\\' . ucfirst(strtolower($params['strategy'])) . 'AuthStrategy',
                    'credentials' => $params['credentials'],
                ];
            }
        }

        if (empty($attemptStrategies) === true) {
            throw new \RuntimeException('Auth strategy not registered');
        }

        foreach ($attemptStrategies as $strategy) {
            if (class_exists($strategy['class']) === false) {
                throw new \RuntimeException('Strategy not implemented');
            }
            try {
                /**
                 * @var \Framework\Base\Auth\AuthStrategyInterface $auth
                 * @var \Framework\Base\Model\BrunoInterface $model
                 */
                $auth = new $strategy['class']($post, $strategy['repository']);
                $model = $auth->validate($strategy['credentials']);
            } catch (AuthenticationException $e) {
                $exception = $e;
            } catch (NotFoundException $e) {
                $exception = $e;
            }
        }

        if ($model === null) {
            throw $exception;
        }

        /**
         * @todo implement key generation, adjustable time on token expiration, algorithm selection
         */
        $key = 'rV)7Djb{DpEpY5ex';
        JWT::$timestamp = time();
        $payload = array(
            'iss' => 'framework.the-shop.io',
            'exp' => JWT::$timestamp + 3600,
            'modelId' => $model->getId(),
            'resourceName' => $model->getCollection(),
            'aclRole' => '',
        );
        $alg = 'HS384';
        $jwt = JWT::encode($payload, $key, $alg);

        return $jwt;
    }
}
