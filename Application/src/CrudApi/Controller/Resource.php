<?php

namespace Application\CrudApi\Controller;

use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Application\Exception\ValidationException;
use Framework\Base\Database\DatabaseQueryInterface;
use Framework\Base\Model\BrunoInterface;
use Application\CrudApi\Repository\GenericRepository;
use Framework\Base\Repository\BrunoRepositoryInterface;
use Framework\Base\Validation\Validator;
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
                    'resourceName' => $resourceName,
                ]
            );

        /* @var GenericRepository $repository */
        $repository = $this->getRepositoryFromResourceName($resourceName);

        $query = $this->buildFilteredQuery($repository);

        $models = $repository->loadMultiple($query);

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
                    'identifier' => $identifier,
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
                    'resourceName' => $resourceName,
                ]
            );

        $this->validateInput($resourceName, $this->getPost());

        $model = $this->getRepositoryFromResourceName($resourceName)
            ->newModel()
            ->setAttributes($this->getPost())
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
                    'identifier' => $identifier,
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        $this->validateInput($resourceName, $postParams);

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
                    'identifier' => $identifier,
                ]
            );

        $model = $this->loadModel($resourceName, $identifier);

        $postParams = $this->getPost();

        $this->validateInput($resourceName, $postParams);

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
                    'identifier' => $identifier,
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

    /**
     * @param string $resourceName
     * @param array $requestParameters
     * @return $this
     * @throws \HttpException
     */
    public function validateInput(string $resourceName, array $requestParameters = [])
    {
        $app = $this->getApplication();
        $requestMethod = $this->getRequest()->getMethod();

        // Get registered model fields
        $registeredModelFields = $app->getRepositoryManager()
            ->getRegisteredModelFields($resourceName);

        // Make new validator instance and attach app to it
        $validator = (new Validator())->setApplication($app);

        /* Loop through registeredModelFields and see if there are any fields that have validation
           rule defined */
        foreach ($registeredModelFields as $fieldName => $options) {
            /* If request method is "POST", "PUT" or "DELETE" check if requestParameters are
            missing field that's defined as required */
            if (array_key_exists($fieldName, $requestParameters) === false) {
                switch ($requestMethod) {
                    case 'POST':
                        $this->validateRequiredField($fieldName, $options);
                        break;
                    case 'PUT':
                        $this->validateRequiredField($fieldName, $options);
                        break;
                    case 'DELETE':
                        $this->validateRequiredField($fieldName, $options);
                        break;
                    case 'PATCH':
                        continue;
                }
            }
            /* Check if registeredModel field exists in request params and validate input if
               validation rule is defined for that specific model field */
            if ((array_key_exists($fieldName, $requestParameters)) === true
                && isset($options['validation']) === true
            ) {
                $value = $requestParameters[$fieldName];
                foreach ($options['validation'] as $validationRule) {
                    /* If field has got unique validation rule, set value as
                       array with fieldName => value, and resourceName so we can make query to DB */
                    if (($validationRule === 'unique') === true) {
                        $value = [
                            $fieldName => $requestParameters[$fieldName],
                            'resourceName' => $resourceName,
                        ];
                    }
                    $validator->addValidation($value, $validationRule);
                }
            }
        }

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            throw new \RuntimeException('Malformed input.', 400);
        }

        return $this;
    }

    /**
     * @param string $fieldName
     * @param array $options
     */
    private function validateRequiredField(string $fieldName, array $options = [])
    {
        // Throw exception if required field is not defined - assume that is required = TRUE
        if (array_key_exists('required', $options) === false) {
            throw new \InvalidArgumentException($fieldName . ' field is required!', 404);
        }
        // Throw exception if field required = TRUE
        if (array_key_exists('required', $options) === true
            && $options['required'] === true
        ) {
            throw new \InvalidArgumentException($fieldName . ' field is required!', 404);
        }
    }

    /**
     * @param BrunoRepositoryInterface $repository
     * @return \Framework\Base\Database\DatabaseQueryInterface
     * @throws ValidationException
     */
    private function buildFilteredQuery(BrunoRepositoryInterface $repository
    ): DatabaseQueryInterface
    {
        $query = $repository->getPrimaryAdapter()
            ->newQuery();

        // Default query params values
        $orderBy = $repository->getModelPrimaryKey();
        $orderDirection = 'desc';
        $offset = 0;
        $limit = 100;

        $errors = [];

        $allParams = $this->getRequest()->getQuery();

        // Validate query params based on request params
        if (empty($allParams) !== true) {
            $skipParams = [
                'orderBy',
                'orderDirection',
                'offset',
                'limit',
                'looseSearch',
            ];

            // Set operator like if request has looseSearch
            $operator = '=';
            if (array_key_exists('looseSearch', $allParams) === true) {
                $operator = 'like';
            }

            foreach ($allParams as $key => $value) {
                if (in_array($key, $skipParams)) {
                    continue;
                }

                // Check if value has "range" delimiter and set query
                if (is_array($value) === false && strpos($value, '>=<')) {
                    $values = explode('>=<', $value);
                    $trimmedValues = array_map('trim', $values);
                    $query->addAndCondition(
                        $key,
                        '>=',
                        ctype_digit($trimmedValues[0]) ? (int)$trimmedValues[0] : $trimmedValues[0]
                    );
                    $query->addAndCondition(
                        $key,
                        '<=',
                        ctype_digit($trimmedValues[1]) ? (int)$trimmedValues[1] : $trimmedValues[1]
                    );

                    if (count($trimmedValues) > 2) {
                        $errors[] = 'Range search must be between two values.';
                    }
                    continue;
                }

                // Check if value is array
                if (is_array($value)) {
                    $query->whereInArrayCondition($key, $value);
                } else {
                    if ($value === 'false') {
                        $value = false;
                    } elseif ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'null') {
                        $value = null;
                    }

                    $query->addAndCondition($key, $operator, $value);
                }
            }
        }

        // Check if request has orderBy, orderDirection, offset or limit field and set query
        if (array_key_exists('orderBy', $allParams) === true) {
            $orderBy = $allParams['orderBy'];
        }

        if (array_key_exists('orderDirection', $allParams) === true) {
            if (strtolower(substr($allParams['orderDirection'], 0, 3)) === 'asc' ||
                strtolower(substr($allParams['orderDirection'], 0, 4)) === 'desc'
            ) {
                $orderDirection = $allParams['orderDirection'];
            } else {
                $errors[] = 'Invalid orderDirection input.';
            }
        }

        if (array_key_exists('offset', $allParams) === true) {
            if (ctype_digit($allParams['offset']) && $allParams['offset'] >= 0) {
                $offset = (int)$allParams['offset'];
            } else {
                $errors[] = 'Invalid offset input.';
            }
        }

        if (array_key_exists('limit', $allParams)) {
            if (ctype_digit($allParams['limit']) && $allParams['limit'] >= 0) {
                $limit = (int)$allParams['limit'];
            } else {
                $errors[] = 'Invalid limit input.';
            }
        }

        if (count($errors) > 0) {
            $exception = new ValidationException();
            $exception->setFailedValidations($errors);
            throw $exception;
        }

        $query->setDatabase(getenv('DATABASE_NAME', 'framework'));
        $query->setCollection($repository->getResourceName());
        $query->setLimit($limit);
        $query->setOffset($offset);
        $query->setOrderBy($orderBy);
        $query->setOrderDirection($orderDirection);

        return $query;
    }
}
