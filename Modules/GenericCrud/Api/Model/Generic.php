<?php

namespace Framework\GenericCrud\Api\Model;

use Framework\Base\Model\Bruno;

/**
 * Class Generic
 * @package Framework\GenericCrud\Api\Model
 */
class Generic extends Bruno
{
    /**
     * @param string $databaseName
     * @return $this
     */
    public function setDatabase(string $databaseName = 'framework')
    {
        $this->database = $databaseName;

        return $this;
    }

    /**
     * @param string $collectionName
     * @return $this
     */
    public function setCollection(string $collectionName = 'generic')
    {
        $this->collection = $collectionName;

        return $this;
    }
}
