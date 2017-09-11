<?php

namespace Framework\Base\Auth;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Interface AuthStrategyInterface
 * @package Framework\Base\Auth
 */
interface AuthStrategyInterface extends ApplicationAwareInterface
{
    /**
     * @param string $id
     *
     * @return $this
     */
    public function setIdentifier(string $id);

    /**
     * @param string $authString
     *
     * @return $this
     */
    public function setAuthorization(string $authString);

    /**
     * @param \Framework\Base\Repository\BrunoRepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(BrunoRepositoryInterface $repository);

    /**
     * @param string $hash
     *
     * @return \Framework\Base\Model\BrunoInterface
     * @throws \Framework\Base\Application\Exception\AuthenticationException
     * @throws \Framework\Base\Application\Exception\NotFoundException
     */
    public function validate(string $hash);
}
