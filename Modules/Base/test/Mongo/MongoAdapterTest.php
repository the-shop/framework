<?php

namespace Framework\Base\Test\Mongo;

use Framework\Base\Mongo\MongoAdapter;
use Framework\Base\Test\UnitTest;
use MongoDB\Client;

class MongoAdapterTest extends UnitTest
{
    public function testGetClient()
    {
        $adapter = new MongoAdapter();
        $mongoClient = new Client();
        // Set the client
        $adapter->setClient($mongoClient);

        $this->assertEquals($mongoClient, $adapter->getClient());
    }
}
