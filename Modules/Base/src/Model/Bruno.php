<?php

namespace Framework\Base\Model;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Model\Modifiers\HashFilter;
use Framework\Base\Mongo\MongoQuery;
use Framework\Base\Model\Modifiers\FieldModifierInterface;
use MongoDB\Model\BSONArray;

/**
 * Base Model for database
 *
 * @package Framework\Base\Model
 */
abstract class Bruno implements BrunoInterface
{
    use ApplicationAwareTrait;

    /**
     * @const string
     */
    const EVENT_MODEL_HANDLE_ATTRIBUTE_VALUE_MODIFY_PRE = 'EVENT\MODEL\HANDLE_ATTRIBUTE_VALUE_MODIFY_PRE';

    /**
     * @const string
     */
    const EVENT_MODEL_HANDLE_ATTRIBUTE_VALUE_MODIFY_POST = 'EVENT\MODEL\HANDLE_ATTRIBUTE_VALUE_MODIFY_POST';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * @var string
     */
    protected $databaseAddress = null;

    /**
     * @var string
     */
    protected $database = null;

    /**
     * @var string
     */
    protected $collection = 'bruno';

    /**
     * @var array
     */
    private $dbAttributes = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $definedAttributes = [];

    /**
     * @var bool
     */
    private $isNew = true;

    /**
     * @var array
     */
    private $fieldFilters = [];

    /**
     * @var bool
     */
    private $filtersEnabled = true;

    /**
     * Bruno constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * @return null|string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return $this
     */
    public function setPrimaryKey(string $primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        if (isset($this->primaryKey) === true) {
            return $this->getAttribute($this->getPrimaryKey());
        }

        return null;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $query = new MongoQuery();
        $query->setDatabase($this->getDatabase());
        $query->setCollection($this->getCollection());

        $adapters = $this->getDatabaseAdapters();

        if ($this->isNew() === true) {
            $adapterActionParams = [
                'method' => 'insertOne',
                'params' => [
                    $query,
                    $this->getAttributes(),
                ],
            ];
        } else {
            $adapterActionParams = [
                'method' => 'updateOne',
                'params' => [
                    $query,
                    $this->getId(),
                    $this->getAttributes(),
                ],
            ];
        }

        foreach ($adapters as $adapter) {
            $response = call_user_func_array(
                [
                    $adapter,
                    $adapterActionParams['method'],
                ],
                $adapterActionParams['params']
            );

            if ($this->isNew() === true) {
                $this->attributes['_id'] = (string)$response;
            }
            $this->dbAttributes = $this->getAttributes();
        }

        $this->setIsNew(false);

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $query = new MongoQuery();
        $query->setDatabase($this->getDatabase());
        $query->setCollection($this->getCollection());
        $query->addAndCondition(
            '_id',
            '=',
            $this->getId()
        );

        $adapters = $this->getDatabaseAdapters();

        /**
         * @var DatabaseAdapterInterface $adapter
         */
        foreach ($adapters as $adapter) {
            $adapter->deleteOne($query);
        }

        return $this;
    }

    /**
     * @return DatabaseAdapterInterface[]
     */
    public function getDatabaseAdapters()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getModelAdapters($this->collection);
    }

    /**
     * @return null|string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return null|string
     */
    public function getDatabaseAddress()
    {
        return $this->databaseAddress;
    }

    /**
     * @param string $collection
     *
     * @return $this
     */
    public function setCollection(string $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setIsNew(bool $flag = true)
    {
        $this->isNew = $flag;

        return $this;
    }

    /**
     * @param string $databaseName
     * @return BrunoInterface
     */
    public function setDatabase(string $databaseName): BrunoInterface
    {
        $this->database = $databaseName;

        return $this;
    }

    /**
     * @param string $databaseAddress
     * @return BrunoInterface
     */
    public function setDatabaseAddress(string $databaseAddress): BrunoInterface
    {
        $this->databaseAddress = $databaseAddress;

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAttribute(string $attribute, $value)
    {
        if (array_key_exists($attribute, $this->getDefinedAttributes()) === false) {
            throw new \InvalidArgumentException('Property "' . $attribute . '" not defined.');
        }

        $this->getApplication()
            ->triggerEvent(
                self::EVENT_MODEL_HANDLE_ATTRIBUTE_VALUE_MODIFY_PRE,
                [
                    $attribute => $value,
                ]
            );

        if ($attribute === 'password') {
            $this->addFieldFilter('password', new HashFilter());
        }

        if ($this->filtersEnabled === true
            && array_key_exists($attribute, $this->fieldFilters) === true
        ) {
            foreach ($this->fieldFilters[$attribute] as $filter) {
                /* @var FieldModifierInterface $filter */
                $value = $filter->modify($value);
            }
        }

        $this->attributes[$attribute] = $value;

        $this->getApplication()
            ->triggerEvent(
                self::EVENT_MODEL_HANDLE_ATTRIBUTE_VALUE_MODIFY_POST,
                $this
            );

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributeName
     *
     * @return mixed|null
     */
    public function getAttribute(string $attributeName)
    {
        return isset($this->getAttributes()[$attributeName]) === true ? $this->getAttributes()[$attributeName] : null;
    }

    /**
     * @return array
     */
    public function getDirtyAttributes()
    {
        $attributes = $this->getAttributes();
        $databaseAttributes = $this->getDatabaseAttributes();

        $dirty = [];

        foreach ($attributes as $key => $value) {
            if (isset($databaseAttributes[$key]) === false) {
                $dirty[$key] = $value;
            } elseif ($value !== $databaseAttributes[$key]
                      && $this->originalIsNumericallyEquivalent($key) === false) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        return empty($this->getDirtyAttributes()) === false;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setDatabaseAttributes(array $attributes = [])
    {
        $this->dbAttributes = $attributes;

        return $this;
    }

    /**
     * @param string $attributeName
     *
     * @return mixed|null
     */
    public function getDatabaseAttribute(string $attributeName)
    {
        return isset($this->getDatabaseAttributes()[$attributeName]) === true ?
            $this->getDatabaseAttributes()[$attributeName] : null;
    }

    /**
     * @return array
     */
    public function getDatabaseAttributes()
    {
        return $this->dbAttributes;
    }

    /**
     * @return array
     */
    public function getDefinedAttributes()
    {
        return $this->definedAttributes;
    }

    /**
     * @param array $definition
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function defineModelAttributes(array $definition = []): BrunoInterface
    {
        $types = [
            'string',
            'password',
            'int',
            'integer',
            'float',
            'bool',
            'boolean',
            'array',
            'num',
            'numeric',
            'alpha_num',
            'alpha_numeric',
            'alpha_dash',
            'mixed',
        ];

        foreach ($definition as $key => $value) {
            if (is_array($value) === false ||
                is_string($key) === false
            ) {
                throw new \InvalidArgumentException('Attribute "' . $key . '" must be formatted as associative array');
            }
            if (isset($value['type']) === false) {
                throw new \InvalidArgumentException('Attribute "' . $key . '" must have "type" defined');
            }
            if (is_array($value['type']) === true ||
                in_array($value['type'], $types, true) === false
            ) {
                throw new \InvalidArgumentException('Unsupported model attribute type: ' . $value['type']);
            }

            $this->definedAttributes[$key] = $value['type'];
        }

        return $this;
    }

    /**
     * @param string $field
     * @param FieldModifierInterface $filter
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function addFieldFilter(string $field, FieldModifierInterface $filter): BrunoInterface
    {
        if (array_key_exists($field, $this->fieldFilters) === false) {
            $this->fieldFilters[$field] = [];
        }

        $this->fieldFilters[$field][] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getFieldFilters(): array
    {
        return $this->fieldFilters;
    }

    /**
     * @return $this
     */
    public function enableFieldFilters()
    {
        if ($this->filtersEnabled === false) {
            $this->filtersEnabled = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function disableFieldFilters()
    {
        if ($this->filtersEnabled === true) {
            $this->filtersEnabled = false;
        }

        return $this;
    }

    /**
     * Validates collection is set properly, throws exception otherwise
     *
     * @param $resourceType
     * @return $this
     * @throws \Exception
     */
    public function confirmResourceOf(string $resourceType)
    {
        if ($this->getCollection() !== $resourceType) {
            throw new \Exception('Model resource not configured correctly');
        }

        return $this;
    }

    /**
     * Determine if the new and old values for a given key are numerically equivalent.
     *
     * @param  string $key
     * @return bool
     */
    private function originalIsNumericallyEquivalent($key)
    {
        $current = $this->getAttribute($key);
        $databaseAttributes = $this->getDatabaseAttributes();

        $original = $databaseAttributes[$key];

        return is_numeric($current)
            && is_numeric($original)
            && strcmp((string)$current, (string)$original) === 0;
    }
}
