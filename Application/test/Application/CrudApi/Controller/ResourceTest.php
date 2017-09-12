<?php

namespace ApplicationTest\Application\CrudApi\Controller;

use Framework\Base\Test\UnitTest;

/**
 * Class ResourceTest
 * @package ApplicationTest\Application\CrudApi\Controller
 */
class ResourceTest extends UnitTest
{
    public function testGetAllUser()
    {
        $response = $this->makeHttpRequest('GET', '/users');

        $responseBody = $response->getBody();

        $this->assertNotEmpty($responseBody);
    }
}
