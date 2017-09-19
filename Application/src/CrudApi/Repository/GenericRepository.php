<?php

namespace Application\CrudApi\Repository;

use Framework\Base\Model\BrunoInterface;
use Framework\Base\Repository\BrunoRepository;

/**
 * Class GenericRepository
 * @package Application\CrudApi\Repository
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
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    protected function createNewQueryForModel(BrunoInterface $bruno)
    {
        $bruno->setCollection($this->resourceName);

        $query = parent::createNewQueryForModel($bruno);

        return $query;
    }
}
