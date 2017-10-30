<?php

namespace Framework\Base\Mongo;

use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\DatabaseQueryInterface;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;

/**
 * Class MongoAdapter
 * @package Framework\Base\Mongo
 */
class MongoAdapter implements DatabaseAdapterInterface
{
    /**
     * @var Client|null
     */
    private $mongoClient = null;

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
            $this->setClient(
                new Client(
                    $uri = 'mongodb://127.0.0.1/',
                    $uriOptions = [],
                    $driverOptions = [
                        'typeMap' => [
                            'array' => 'array',
                            'document' => 'MongoDB\Model\BSONDocument',
                            'root' => 'MongoDB\Model\BSONDocument',
                        ],
                    ]
                )
            );
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
                       ->selectCollection(
                           $query->getDatabase(),
                           $query->getCollection()
                       )
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
    public function updateOne(
        DatabaseQueryInterface $query,
        string $identifier,
        array $updateData = []
    ) {
        if (empty($updateData)) {
            return $this;
        }

        if (array_key_exists('_id', $updateData)) {
            unset($updateData['_id']);
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
                    'sort' => [
                        $query->getOrderBy() => $query->getOrderDirection() === 'asc' ? -1 : 1
                    ],
                ]
            );

        $out = [];
        foreach ($queryResults as $result) {
            if (isset($result['_id']) === true &&
                $result['_id'] instanceof ObjectID === true
            ) {
                $result['_id'] = (string) $result['_id'];
            }
            $out[] = $result->getArrayCopy();
        }

        return $out;
    }

    /**
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function newQuery(): DatabaseQueryInterface
    {
        return new MongoQuery();
    }
}
