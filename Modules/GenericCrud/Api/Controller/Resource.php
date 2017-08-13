<?php

namespace Framework\GenericCrud\Api\Controller;

use Framework\Application\RestApi\NotFoundException;
use Framework\GenericCrud\Api\Model\Generic as GenericModel;
use Framework\Http\Controller\Http as HttpController;

/**
 * Class Resource
 * @package Framework\GenericCrud\Api\Controller
 */
class Resource extends HttpController
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
        $model = $this->getRepository(GenericModel::class)
            ->loadOne($identifier);

        if (!$model) {
            throw new NotFoundException('Model not found.');
        }

        $postParams = $this->getPost();

        $model->setAttributes($postParams);
        $model->save();

        return $model;
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
