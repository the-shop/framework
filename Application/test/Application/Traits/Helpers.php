<?php

namespace Application\Test\Application\Traits;

/**
 * Class Helpers
 * @package Application\Test\Application\Traits
 */
trait Helpers
{
    /**
     * Helper method for generating random E-mail
     * @param int $length
     * @return string
     */
    public function generateRandomEmail(int $length = 10)
    {
        // Generate random email
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $email = '';

        for ($i = 0; $i < $length; $i++) {
            $email .= $characters[rand(0, $charactersLength - 1)];
        }

        $email .= '@test.com';

        return $email;
    }
}
