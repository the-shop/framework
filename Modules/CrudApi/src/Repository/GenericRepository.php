<?php

namespace Framework\CrudApi\Repository;

use Framework\Base\Database\DatabaseQueryInterface;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Repository\BrunoRepository;

/**
 * Class GenericRepository
 * @package Framework\CrudApi\Repository
 */
class GenericRepository extends BrunoRepository
{
    /**
     * @return array
     */
    public function getModelAttributesDefinition()
    {
        return $this->getRepositoryManager()
            ->getRegisteredModelFields($this->getResourceName());
    }

    /**
     * @param BrunoInterface $bruno
     *
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function createNewQueryForModel(BrunoInterface $bruno): DatabaseQueryInterface
    {
        $bruno->setCollection($this->resourceName);

        $query = parent::createNewQueryForModel($bruno);

        return $query;
    }
}
