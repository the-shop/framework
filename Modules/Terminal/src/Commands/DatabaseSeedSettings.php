<?php

namespace Framework\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class DatabaseSeedSettings
 * @package Framework\Terminal\Commands
 */
class DatabaseSeedSettings
{
    use ApplicationAwareTrait;

    public function handle()
    {
        $app = $this->getApplication();

        $settingsPath = $app->getRootPath() . '/Application/config/sharedSettings.php';

        $settingsArray = include $settingsPath;

        $repository = $app->getRepositoryManager()->getRepositoryFromResourceName('settings');

        foreach ($settingsArray as $key => $value) {
            $repository->newModel()
            ->setAttribute($key, $value)
            ->save();
        }

        return 'Database seeded! Seeded collection: settings.';
    }
}
