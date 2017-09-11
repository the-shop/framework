<?php

namespace Framework\Base\Auth\Controller;

use Firebase\JWT\JWT;
use Framework\Http\Controller\Http;

class AuthController extends Http
{
    public function authenticate()
    {
        $authModel = $this->getRepositoryManager()->getAuthenticatableModels();
        $post = $this->getPost();

//
//        foreach ($authStrategy as $strategy) {
//            $strategy->validate($passwordHash);
//        }
//
//
//        $key = $password;
//        $payload = array(
//            "iss" => "http://framework.the-shop.io",
//            "exp" => time() + 3600,
//        );
//        $alg = 'HS256';
//        $jwt = JWT::encode($payload, $key, $alg);
//
//        return $jwt;
    }
}
