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
    /**
     * @param string $resourceName
     * @return array
     */
    public function loadAll(string $resourceName)
    {
        $out = $this->getRepositoryFromResourceName($resourceName)
            ->loadMultiple();

        return $out;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function load(string $resourceName, $identifier)
    {
        $model = $this->loadModel($resourceName, $identifier);

        return $model;
    }

    /**
     * @param string $resourceName
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function create(string $resourceName)
    {
        $model = new GenericModel();
        $model->setResourceName($resourceName);
        $model->save();
        return $model;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function update(string $resourceName, $identifier)
    {
        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        $model->setAttributes($postParams);
        $model->save();

        return $model;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function partialUpdate(string $resourceName, $identifier)
    {
        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        foreach ($postParams as $attribute => $value) {
            $model->setAttribute($attribute, $value);
        }

        $model->save();

        return $model;
    }

    /**
     * @param string $resourceName
     * @param $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function delete(string $resourceName, $identifier)
    {
        $model = $this->loadModel($resourceName, $identifier);

        $model->delete();

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
