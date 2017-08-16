<?php

namespace Framework\GenericCrud\Api\Repository;

use Framework\Base\Database\MongoQuery;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Repository\BrunoRepository;

/**
 * Class GenericRepository
 * @package Framework\GenericCrud\Api\Repository
 */
class GenericRepository extends BrunoRepository
{
    private $resourceName = 'generic';

    /**
     * Sets `$resourceName` as the document collection
     *
     * @param string $resourceName
     * @return $this
     */
    public function setResourceName(string $resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * @param BrunoInterface $bruno
     * @return MongoQuery
     */
    protected function createNewQueryForModel(BrunoInterface $bruno)
    {
        $bruno->setCollection($this->resourceName);

        $query = parent::createNewQueryForModel($bruno);

        return $query;
    }
}
