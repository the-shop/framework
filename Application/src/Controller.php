<?php

namespace Application;

use Framework\CrudApi\Controller\Resource;
use Framework\Base\Helpers\Parse;
use Framework\Base\Application\Exception\NotFoundException;

class Controller extends Resource
{
    /**
     * @param string $resourceName
     * @param string $identifier
     * @return mixed
     * @throws NotFoundException
     */
    public function getPerformance(string $resourceName, string $identifier)
    {
        // Default last month
        $startDate = strtotime('first day of last month');
        $endDate = strtotime('last day of last month');

        $query = $this->getQuery();

        if (array_key_exists('unixStart', $query) === true) {
            $startDate = Parse::unixTimestamp($query['unixStart']);
        }

        if (array_key_exists('unixEnd', $query) === true) {
            $endDate = Parse::unixTimestamp($query['unixEnd']);
        }

        $profile = $this->getRepositoryFromResourceName($resourceName)->loadOne($identifier);
        if (!$profile) {
            throw new NotFoundException('Profile not found', 404);
        }

        $profilePerformance = $this->getApplication()->getService('profilePerformance');

        return $profilePerformance->aggregateForTimeRange($profile, $startDate, $endDate);
    }
}
