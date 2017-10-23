<?php

namespace Framework\Base\Test;

use Framework\Base\Repository\BrunoRepository;

class TestRepository extends BrunoRepository
{
    protected $resourceName = 'tests';

    /**
     * @return array
     */
    public function getModelAttributesDefinition()
    {
        return $this->getRepositoryManager()
                    ->getRegisteredModelFields($this->resourceName);
    }

    public function loadOneBy(array $keyValues = [])
    {
        return $this->getPrimaryAdapter()->loadOne(new TestDatabaseQuery());
    }
}
