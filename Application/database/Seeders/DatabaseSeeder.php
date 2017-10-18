<?php

namespace Application\Database\Seeders;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class DatabaseSeeder
 * @package Application\Database\Seeders
 */
class DatabaseSeeder
{
    use ApplicationAwareTrait;

    /**
     * Register Seeders in here
     * @var array
     */
    private $seeders = [
        'settings' => DatabaseSeedSettings::class
    ];

    /**
     * @param string $seederName
     * @return mixed
     */
    public function handle(string $seederName)
    {
        $seeders = $this->getSeeders();
        if (array_key_exists($seederName, $seeders) === true) {
            $seeder = new $seeders[$seederName];
            $seeder->setApplication($this->getApplication());
            return $seeder->handle();
        }
    }

    /**
     * @return array
     */
    public function getSeeders()
    {
        return $this->seeders;
    }

    /**
     * @param string $seederName
     * @param string $fullyQualifiedClassPath
     * @return $this
     */
    public function setSeeder(string $seederName, string $fullyQualifiedClassPath)
    {
        $this->seeders[$seederName] = $fullyQualifiedClassPath;

        return $this;
    }
}
