<?php

namespace Application\Database\Seeders;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class DatabaseSeedSettings
 * @package Application\Database\Seeders
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

        foreach ($settingsArray as $scope => $settings) {
            foreach ($settings as $key => $value) {
                $repository->newModel()
                    ->setAttributes([
                        'key' => $key,
                        'value' => $value,
                        'scope' => $scope
                    ])
                    ->save();
            }
        }

        return 'Database seeded! Seeded collection: settings.';
    }
}
