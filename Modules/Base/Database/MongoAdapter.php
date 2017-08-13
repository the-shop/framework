<?php

namespace Framework\Base\Database;

use MongoDB\BSON\ObjectID;
use MongoDB\Client;

/**
 * Class MongoAdapter
 * @package Framework\Base\Database
 */
class MongoAdapter implements DatabaseAdapterInterface
{
    /**
     * @var Client|null
     */
    private $mongoClient = null;

    /**
     * @var MongoAdapter|null
     */
    private $databaseAdapter = null;

    /**
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->databaseAdapter = $adapter;

        return $this;
    }

    public function getDatabaseAdapter()
    {
        return $this->databaseAdapter;
    }

    /**
     * @param $mongoClient
     * @return $this
     */
    public function setClient($mongoClient)
    {
        $this->mongoClient = $mongoClient;

        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient()
    {
        if ($this->mongoClient === null) {
            $this->setClient(new Client());// TODO: config
        }

        return $this->mongoClient;
    }

    /**
     * @param DatabaseQueryInterface $query
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function insertOne(DatabaseQueryInterface $query, array $data = [])
    {
        $result = $this->getClient()
            ->selectCollection($query->getDatabase(), $query->getCollection())
            ->insertOne($data);

        if ($result->getInsertedId()) {
            return $result->getInsertedId();
        }

        throw new \Exception('Mongo insert one failed');
    }

    /**
     * @param DatabaseQueryInterface $query
     * @param string $identifier
     * @param array $updateData
     * @return $this
     */
    public function updateOne(DatabaseQueryInterface $query, string $identifier, array $updateData = [])
    {
        if (empty($updateData)) {
            return $this;
        }

        $this->getClient()
            ->selectCollection(
                $query->getDatabase(),
                $query->getCollection()
            )
            ->updateOne(
                ['_id' => new ObjectID($identifier)],
                ['$set' => $updateData]
            );

        // TODO: check if $result reported successful update, throw exception otherwise

        return $this;
    }

    /**
     * @param DatabaseQueryInterface $query
     * @return mixed|null
     */
    public function loadOne(DatabaseQueryInterface $query)
    {
        $query->setLimit(1);
        $results = $this->loadMultiple($query);

        if (array_key_exists(0, $results) === true) {
            return $results[0];
        }

        return null;
    }

    public function deleteOne(DatabaseQueryInterface $query)
    {
        $result = $this->getClient()
            ->selectCollection($query->getDatabase(), $query->getCollection())
            ->deleteOne($query->build());

        return true; // TODO: return true / false depending on `$result` data
    }

    public function loadMultiple(DatabaseQueryInterface $query)
    {
        $queryResults = $this->getClient()
            ->selectCollection(
                $query->getDatabase(),
                $query->getCollection()
            )
            ->find(
                $query->build(),
                [
                    'projection' => $query->getSelectFields(),
                    'skip' => (int) $query->getOffset(),
                    'limit' => (int) $query->getLimit(),
                ]
            );

        $out = [];
        foreach ($queryResults as $result) {
            $out[] = $result;
        }

        return $out;
    }
}
