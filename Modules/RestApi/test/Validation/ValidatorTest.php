<?php

namespace Framework\RestApiTest\Validation;

use Framework\Base\Application\Exception\ValidationException;
use Framework\Base\Test\UnitTest;
use Framework\Base\Validation\Validator;

/**
 * Class ValidatorTest
 * @package Framework\RestApiTest\Validation
 */
class ValidatorTest extends UnitTest
{
    /**
     * Test alphabetic validation - failed
     */
    public function testAlphabeticValidationFailed()
    {
        $validator = new Validator();
        $validator->addValidation(1212, 'alphabetic');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();

        $this->assertEquals(['alphabetic' => 1212], $validator->getFailed());
    }

    /**
     * Test alphabetic validation - success
     */
    public function testAlphabeticValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation('test', 'alphabetic');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test array validation - failed
     */
    public function testArrayValidationFailed()
    {
        $validator = new Validator();
        $validator->addValidation('test', 'array');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();

        $this->assertEquals(['array' => 'test'], $validator->getFailed());
    }

    /**
     * Test array validation - success
     */
    public function testArrayValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation(['test'], 'array');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test boolean validation - failed
     */
    public function testBooleanValidationFailed()
    {
        $validator = new Validator();
        $validator->addValidation('test', 'boolean');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();

        $this->assertEquals(['boolean' => 'test'], $validator->getFailed());
    }

    /**
     * Test boolean validation - success
     */
    public function testBooleanValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation(true, 'boolean');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test email validation - failed
     */
    public function testEmailValidationFailed()
    {
        $validator = new Validator();
        $value = 'test@121212.1212';
        $validator->addValidation($value, 'email');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();

        $this->assertEquals(['email' => $value], $validator->getFailed());
    }

    /**
     * Test email validation - success
     */
    public function testEmailValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation('test@test.com', 'email');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test float validation - failed
     */
    public function testFloatValidationFailed()
    {
        $validator = new Validator();
        $value = 1;
        $validator->addValidation($value, 'float');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();
        $this->assertEquals(['float' => $value], $validator->getFailed());
    }

    /**
     * Test float validation - success
     */
    public function testFloatValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation(12.12, 'float');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test integer validation - failed
     */
    public function testIntegerValidationFailed()
    {
        $validator = new Validator();
        $value = 12.12;
        $validator->addValidation($value, 'integer');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();
        $this->assertEquals(['integer' => $value], $validator->getFailed());
    }

    /**
     * Test integer validation - success
     */
    public function testIntegerValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation(12, 'integer');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test nonEmpty validation - failed
     */
    public function testNonEmptyValidationFailed()
    {
        $validator = new Validator();
        $value = "";
        $validator->addValidation($value, 'nonempty');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();
        $this->assertEquals(['nonempty' => $value], $validator->getFailed());
    }

    /**
     * Test nonEmpty validation - success
     */
    public function testNonEmptyValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation(12.12, 'nonempty');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test string validation - failed
     */
    public function testStringValidationFailed()
    {
        $validator = new Validator();
        $value = true;
        $validator->addValidation($value, 'string');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();
        $this->assertEquals(['string' => $value], $validator->getFailed());
    }

    /**
     * Test string validation - success
     */
    public function testStringValidationSuccess()
    {
        $validator = new Validator();
        $validator->addValidation('test', 'string');
        $validator->validate();
        $this->assertEquals([], $validator->getFailed());
    }

    /**
     * Test multiple validations with validator with some failed and some success validations
     */
    public function testValidatorMultipleValidations()
    {
        $validator = new Validator();
        $value = 'test';
        $number = 12;
        $validator->addValidation($value, 'string');
        $validator->addValidation($number, 'integer');
        $validator->addValidation($value, 'array');
        $validator->addValidation($value, 'alphabetic');
        $validator->addValidation($value, 'boolean');
        $validator->addValidation($value, 'email');
        $validator->addValidation($number, 'float');
        $validator->addValidation($number, 'nonempty');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $validator->validate();
        $this->assertEquals([
            'array' => $value,
            'boolean' => $value,
            'email' => $value,
            'float' => $number
        ], $validator->getFailed());
    }
}
