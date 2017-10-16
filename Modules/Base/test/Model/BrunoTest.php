<?php

namespace Framework\Base\Test;

use Framework\Base\Model\Modifiers\LowerCaseFilter;
use Framework\Base\Model\Modifiers\TrimFilter;

/**
 * Class BrunoTest
 * @package Framework\Base\Test
 */
class BrunoTest extends UnitTest
{
    /**
     * Test Bruno model field modifiers
     */
    public function testBrunoAddAttributesWithFieldModifiers()
    {
        $model = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->addFieldFilter('name', new LowerCaseFilter())
            ->addFieldFilter('email', new TrimFilter())
            ->setAttributes([
                'name' => 'TESTING',
                'email' => ' test@test.com ',
                'password' => 'test password',
            ]);

        $attributes = $model->getAttributes();

        $this->assertEquals('testing', $attributes['name']);
        $this->assertEquals('test@test.com', $attributes['email']);
        $this->assertNotEquals('test password', $attributes['password']);
        $this->assertEquals(
            true,
            password_verify('test password', $attributes['password'])
        );
    }
}
