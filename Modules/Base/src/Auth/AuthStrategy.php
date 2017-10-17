<?php

namespace Framework\Base\Auth;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Class AuthStrategy
 * @package Framework\Base\Auth
 */
abstract class AuthStrategy implements AuthStrategyInterface
{
    use ApplicationAwareTrait;

    /**
     * AuthStrategy constructor.
     *
     * @param string                                              $id
     * @param string                                              $authString
     * @param \Framework\Base\Repository\BrunoRepositoryInterface $repository
     */
    public function __construct(string $id, string $authString, BrunoRepositoryInterface $repository)
    {
        $this->setIdentifier($id)
             ->setAuthorization($authString)
             ->setRepository($repository);
    }
}
