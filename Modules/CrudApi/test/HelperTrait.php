<?php

namespace Framework\CrudApi\Test;

use Framework\Base\Model\BrunoInterface;

/**
 * Trait HelperTrait
 * @package Framework\CrudApi\Test
 */
trait HelperTrait
{
    /**
     * Helper method for generating random E-mail
     * @param int $length
     * @return string
     */
    public function generateRandomEmail(int $length = 10)
    {
        $email = $this->generateRandomString($length);

        $email .= '@test.com';

        return $email;
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function generateRandomString(int $length = 10)
    {
        // Generate random email
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, $charactersLength - 1)];
        }

        return $string;
    }

    /**
     * Deletes test records from db collection
     * @param string $resourceName
     */
    private function purgeCollection(string $resourceName)
    {
        $adapter = $this->getApplication()
                        ->getRepositoryManager()
                        ->getRepositoryFromResourceName($resourceName)
                        ->getPrimaryAdapter();

        $databaseName = $this->getApplication()
                             ->getConfiguration()
                             ->getPathValue('env.DATABASE_NAME');

        $adapter->getClient()
                ->selectCollection($databaseName, $resourceName)
                ->drop();
    }

    /**
     * @param \Framework\Base\Model\BrunoInterface $model
     */
    private function deleteCollection(BrunoInterface $model)
    {
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName($model->getCollection());

        $repository->getPrimaryAdapter()
                   ->getClient()
                   ->selectCollection(
                       $model->getDatabase(),
                       $model->getCollection()
                   )
                   ->drop();
    }
}
