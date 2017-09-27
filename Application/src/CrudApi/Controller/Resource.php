<?php

namespace Application\CrudApi\Controller;

use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Model\BrunoInterface;
use Application\CrudApi\Repository\GenericRepository;
use Framework\Http\Controller\Http as HttpController;

/**
 * Class Resource
 * @package Application\CrudApi\Controller
 */
class Resource extends HttpController
{
    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE = 'EVENT\CRUD_API\RESOURCE_LOAD_ALL_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_LOAD_ALL_POST = 'EVENT\CRUD_API\RESOURCE_LOAD_ALL_POST';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_LOAD_PRE = 'EVENT\CRUD_API\RESOURCE_LOAD_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_LOAD_POST = 'EVENT\CRUD_API\RESOURCE_LOAD_POST';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_CREATE_PRE = 'EVENT\CRUD_API\RESOURCE_CREATE_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_CREATE_POST = 'EVENT\CRUD_API\RESOURCE_CREATE_POST';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_UPDATE_PRE = 'EVENT\CRUD_API\RESOURCE_UPDATE_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_UPDATE_POST = 'EVENT\CRUD_API\RESOURCE_UPDATE_POST';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_PARTIAL_UPDATE_PRE = 'EVENT\CRUD_API\RESOURCE_PARTIAL_UPDATE_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_PARTIAL_UPDATE_POST = 'EVENT\CRUD_API\RESOURCE_PARTIAL_UPDATE_POST';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_DELETE_PRE = 'EVENT\CRUD_API\RESOURCE_DELETE_PRE';

    /**
     * @const string
     */
    const EVENT_CRUD_API_RESOURCE_DELETE_POST = 'EVENT\CRUD_API\RESOURCE_DELETE_POST';

    /**
     * @param string $resourceName
     * @return array
     */
    public function loadAll(string $resourceName)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE,
                [
                    'resourceName' => $resourceName
                ]
            );

        /* @var GenericRepository $repository */
        $repository = $this->getRepositoryFromResourceName($resourceName);
        $models = $repository->loadMultiple();

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_LOAD_ALL_POST, $models);

        return $models;
    }

    /**
     * @param string $resourceName
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function load(string $resourceName, string $identifier)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_LOAD_PRE,
                [
                    'resourceName' => $resourceName,
                    'identifier' => $identifier
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_LOAD_POST, $model);

        return $model;
    }

    /**
     * @param string $resourceName
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function create(string $resourceName)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_CREATE_PRE,
                [
                    'resourceName' => $resourceName
                ]
            );

        $model = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName($resourceName)
            ->newModel();

        $model->setAttributes($this->getPost())
              ->save();

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_CREATE_POST, $model);

        return $model;
    }

    /**
     * @param string $resourceName
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function update(string $resourceName, string $identifier)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_UPDATE_PRE,
                [
                    'resourceName' => $resourceName,
                    'identifier' => $identifier
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        $model->setAttributes($postParams);
        $model->save();

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_UPDATE_POST, $model);

        return $model;
    }

    /**
     * @param string $resourceName
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function partialUpdate(string $resourceName, string $identifier)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_PARTIAL_UPDATE_PRE,
                [
                    'resourceName' => $resourceName,
                    'identifier' => $identifier
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        foreach ($postParams as $attribute => $value) {
            $model->setAttribute($attribute, $value);
        }

        $model->save();

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_PARTIAL_UPDATE_POST, $model);

        return $model;
    }

    /**
     * @param string $resourceName
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function delete(string $resourceName, string $identifier)
    {
        $this->getApplication()
            ->triggerEvent(
                self::EVENT_CRUD_API_RESOURCE_DELETE_PRE,
                [
                    'resourceName' => $resourceName,
                    'identifier' => $identifier
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $model->delete();

        $this->getApplication()
            ->triggerEvent(self::EVENT_CRUD_API_RESOURCE_DELETE_POST, $model);

        return $model;
    }

    /**
     * Helper method for the controller
     *
     * @param string $resourceName
     * @param string $identifier
     * @return BrunoInterface
     * @throws NotFoundException
     */
    protected function loadModel(string $resourceName, string $identifier)
    {
        /* @var GenericRepository $repository */
        $repository = $this->getRepositoryFromResourceName($resourceName);
        $model = $repository->loadOne($identifier);

        if (!$model) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }
}
