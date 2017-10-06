<?php

namespace Framework\Base\Test\Model\Modifiers;

use Framework\Base\Model\Modifiers\HashFilter;
use Framework\Base\Test\UnitTest;

/**
 * Class HashFilterTest
 * @package Framework\Base\Test\Model\Modifiers
 */
class HashFilterTest extends UnitTest
{
    /**
     * Test HashFilter field modifier
     */
    public function testHashFilter()
    {
        $password = 'test password';
        $hashedPassword = password_hash('test', PASSWORD_DEFAULT);

        $hashFilter = new HashFilter();

        $this->assertNotEquals($password, $hashFilter->modify($password));
        $this->assertNotEquals($hashedPassword, $hashFilter->modify($password));
        $this->assertEquals(
            true,
            password_verify($password, $hashFilter->modify($password))
        );
    }
}
