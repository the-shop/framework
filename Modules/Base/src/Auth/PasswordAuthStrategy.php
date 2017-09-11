<?php

namespace Framework\Base\Auth;

use Framework\Base\Application\Exception\AuthenticationException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Class PasswordAuthStrategy
 * @package Framework\Base\Auth
 */
class PasswordAuthStrategy extends AuthStrategy
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $password;

    /**
     * @var BrunoRepositoryInterface
     */
    private $repository;

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setIdentifier(string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $authString
     *
     * @return $this
     */
    public function setAuthorization(string $authString)
    {
        $this->password = $authString;

        return $this;
    }

    /**
     * @param \Framework\Base\Repository\BrunoRepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(BrunoRepositoryInterface $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return BrunoRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $hash
     *
     * @return \Framework\Base\Model\BrunoInterface|null
     * @throws \Framework\Base\Application\Exception\AuthenticationException
     * @throws \Framework\Base\Application\Exception\NotFoundException
     */
    public function validate(string $hash)
    {
        $model = $this->getRepository()->loadOne($this->getId());

        if ($model === null) {
            throw new NotFoundException('Model not found.');
        }

        if (password_verify($this->getPassword(), $hash) === false) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $model;
    }
}
