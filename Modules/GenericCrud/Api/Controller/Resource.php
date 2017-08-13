<?php

namespace Framework\GenericCrud\Api\Controller;

use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Application\BaseController;
use Framework\GenericCrud\Api\Model\Generic as GenericModel;

/**
 * Class Resource
 * @package Framework\GenericCrud\Api\Controller
 */
class Resource extends BaseController
{
    public function loadAll($resourceName)
    {
        return ['called', 'resource', $resourceName];
    }

    public function load($resourceName, $identifier)
    {
        $model = $this->getRepository(GenericModel::class)
            ->loadOne($identifier);

        if (!$model) {
            throw new NotFoundException('Model not found.');
        }

        return $model->getId();
    }

    public function create()
    {
        $user = new GenericModel();
        $user->save();
        return $user->getId();
    }

    public function update($resourceName, $identifier)
    {
        // TODO: implement
    }

    public function partialUpdate($resourceName, $identifier)
    {
        // TODO: implement
    }

    public function delete($resourceName, $identifier)
    {
        // TODO: implement
    }
}
