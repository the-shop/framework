<?php

namespace Framework\Base\Auth\Controller;

use Firebase\JWT\JWT;
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
        $model = null;
        $attemptStrategies = [];

        foreach ($authModels as $modelName => $params) {
            if (in_array($params['credentials'], array_keys($post), true) === true &&
                count($post) === count($params['credentials'])
            ) {
                $attemptStrategies[] = [
                    'repository' => $this->getRepositoryFromResourceName($modelName),
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
            } catch (\Exception $exception) {
                /**
                 * @todo implement handling distinction between invalid credentials and similar but wrong strategy
                 */
            }
        }

        if ($model === null) {
            throw new \RuntimeException('No auth mechanism matched');
        }

        /**
         * @todo implement key generation, adjustable time on token expiration, algorithm selection
         */
        $key = 'rV)7Djb{DpEpY5ex';
        $payload = array(
            'iss' => 'framework.the-shop.io',
            'exp' => time() + 3600,
            'sub' => $model->getId(),
        );
        $alg = 'HS256';
        $jwt = JWT::encode($payload, $key, $alg);

        return $jwt;
    }
}
