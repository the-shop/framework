<?php

namespace Framework\Base\Auth;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Repository\BrunoRepositoryInterface;

abstract class AuthStrategy implements AuthStrategyInterface
{
    use ApplicationAwareTrait;

    public function __construct(string $id, string $authString, BrunoRepositoryInterface $repository)
    {
        $this->setIdentifier($id);
        $this->setAuthorization($authString);
        $this->setRepository($repository);
    }
}
