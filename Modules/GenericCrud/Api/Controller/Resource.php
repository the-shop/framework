<?php

namespace Framework\GenericCrud\Api\Controller;

use Framework\Application\RestApi\NotFoundException;
use Framework\GenericCrud\Api\Model\Generic as GenericModel;
use Framework\GenericCrud\Api\Model\Generic;
use Framework\Http\Controller\Http as HttpController;

/**
 * Class Resource
 * @package Framework\GenericCrud\Api\Controller
 */
class Resource extends HttpController
{
    /**
     * @param string $resourceName
     * @return array
     */
    public function loadAll(string $resourceName)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:loadAll:pre');

        $out = $this->getRepositoryFromResourceName($resourceName)
            ->loadMultiple();

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:loadAll:post');

        return $out;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function load(string $resourceName, $identifier)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:load:pre');

        $model = $this->loadModel($resourceName, $identifier);

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:load:post');

        return $model;
    }

    /**
     * @param string $resourceName
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function create(string $resourceName)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:create:pre');

        $model = new GenericModel();
        $model->setResourceName($resourceName);
        $model->save();
        return $model;

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:create:post');
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function update(string $resourceName, $identifier)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:update:pre');

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        $model->setAttributes($postParams);
        $model->save();

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:update:post');

        return $model;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function partialUpdate(string $resourceName, $identifier)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:partialUpdate:pre');

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        foreach ($postParams as $attribute => $value) {
            $model->setAttribute($attribute, $value);
        }

        $model->save();

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:partialUpdate:post');

        return $model;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function delete(string $resourceName, $identifier)
    {
        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:delete:pre');
        $model = $this->loadModel($resourceName, $identifier);

        $model->delete();

        $this->getApplication()
            ->triggerEvent('GenericCrud\Api\Controller\Resource:delete:post');

        return $model;
    }

    /**
     * Helper method for the controller
     *
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    protected function loadModel(string $resourceName, $identifier)
    {
        $model = $this->getRepositoryFromResourceName($resourceName)
            ->loadOne($identifier);

        if (!$model) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }
}
