<?php

namespace Framework\Base\Auth;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Interface AuthStrategyInterface
 * @package Framework\Base\Auth
 */
interface AuthStrategyInterface extends ApplicationAwareInterface
{
    /**
     * Unique identifier for Model (email, name, id, ....)
     *
     * @param string $id
     *
     * @return \Framework\Base\Auth\AuthStrategyInterface
     */
    public function setIdentifier(string $id): AuthStrategyInterface;

    /**
     * Authorization string (password, token, key, secret...)
     *
     * @param string $authString
     *
     * @return \Framework\Base\Auth\AuthStrategyInterface
     */
    public function setAuthorization(string $authString): AuthStrategyInterface;

    /**
     * Model repository
     *
     * @param \Framework\Base\Repository\BrunoRepositoryInterface $repository
     *
     * @return \Framework\Base\Auth\AuthStrategyInterface
     */
    public function setRepository(BrunoRepositoryInterface $repository): AuthStrategyInterface;

    /**
     * Validates the auth params
     *
     * @param array $credentials
     *
     * @return \Framework\Base\Model\BrunoInterface
     * @throws \Framework\Base\Application\Exception\AuthenticationException
     * @throws \Framework\Base\Application\Exception\NotFoundException
     */
    public function validate(array $credentials): BrunoInterface;
}
