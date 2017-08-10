<?php

/**
 * Main entry point for the API - it does basic routing and handles file inclusion for dependencies
 *
 * Class Api
 */
class Api
{
    /**
     * Location of the API dependency files
     * @var string
     */
    private $apiDirectory = '';

    /**
     * Location of the Wordpress installation
     * @var string
     */
    private $rootDirectory = '';

    /**
     * @var array
     */
    private $requestHeaders = array();

    /**
     * Array of file paths to include relative to API files directory
     *
     * @var array
     */
    private static $apiStaticDependencies = array(
        'Base/ValidationTrait.php',
        'Base/AuthorizationRequiredInterface.php',
        'Base/AuthorizationInterface.php',
        'Base/AuthorizationTrait.php',
        'Base/BaseController.php',
        'Base/BaseJsonController.php',
        'Base/BaseLogin.php',
    );

    /**
     * Array of file paths to include relative to installation directory
     *
     * @var array
     */
    private static $projectStaticDependencies = [];

    /**
     * Map of request type values to endpoint class names
     */
    private $endpointRegistry = array(
        'unauthorized' => 'Unauthorized',
        'not-found' => 'NotFound',
        'login' => 'Login',
        'signup' => 'SignUp',
    );

    /**
     * @var string
     */
    private $requestedEndpoint = '';

    /**
     * @var array
     */
    private $config = [];

    /**
     * Api constructor.
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct(array $config = array())
    {
        /**
         * Set the configuration
         */
        $this->config = $config;

        /**
         * Define apiDirectory
         */
        $this->apiDirectory = __DIR__ . DIRECTORY_SEPARATOR;
        $this->rootDirectory = realpath('../') . DIRECTORY_SEPARATOR;

        /**
         * Save request type
         */
        $this->requestedEndpoint = isset($_REQUEST['req_type']) ? $_REQUEST['req_type'] : 'not-found';

        /**
         * Check if requested endpoint is defined in the endpoint registry
         */
        if (array_key_exists($this->requestedEndpoint, $this->endpointRegistry) === false) {
            $this->requestedEndpoint = 'not-found';
        }

        /**
         * Define constants
         */
//        define('AUTHORIZATION_HEADER_NAME', $this->config['authorizationHeaderName']);
//        define('API_AUTH_SIGNATURE', $this->config['authorizationAuthSignature']);

        /**
         * Get request headers
         */
        $headers = getallheaders();
        if ($headers === false) {
            throw new \Exception('Request headers could not be parsed. Server misconfigured.');
        }
        $this->requestHeaders = $headers;

        /**
         * Include the dependencies
         */
        $this->includeDependencies();
    }

    /**
     * Main entry point for the API logic
     */
    public function run()
    {
        /**
         * Instantiate the API endpoint.
         */
        $endpointClassName = $this->endpointRegistry[$this->requestedEndpoint];
        /* @var BaseController $endpoint */
        $endpoint = new $endpointClassName($this->config);

        $endpoint->setRequestHeaders($this
            ->requestHeaders);

        if ($endpoint instanceof AuthorizationInterface) {
            $endpoint->setAuthorizationValidationHeaders($endpoint->getRequestHeaders())
                ->validateAuthorization();

            if ($endpoint instanceof AuthorizationRequiredInterface && $endpoint->isAuthorizedRequest() === false) {
                $endpoint = new Unauthorized();
            }
        }

        /**
         * Set request data to the endpoint instance
         */
        $getData = isset($_GET) ? $_GET : array();
        $postData = isset($_POST) ? $_POST : array();
        $files = isset($_FILES) ? $_FILES : array();
        $endpoint->setQuery($getData);
        $endpoint->setPost($postData);
        $endpoint->setFiles($files);

        /**
         * Clear the raw data
         */
        unset($_GET);
        unset($_POST);
        unset($_FILES);

        /**
         * Handle the API endpoint
         */
        $response = $endpoint->handle();

        echo $response;
    }

    /**
     * Helper method that handles the file inclusions
     */
    public function includeDependencies()
    {
        /**
         * Include API dependencies
         */
        foreach (self::$apiStaticDependencies as $dependency) {
            require_once $this->apiDirectory . $dependency;
        }

        foreach (self::$projectStaticDependencies as $dependency) {
            require_once $this->rootDirectory . $dependency;
        }

        /**
         * Include all endpoint files
         */
        $endpointFileExtension = '.php';
        foreach ($this->endpointRegistry as $classFileName) {
            require_once $this->apiDirectory . 'Endpoint' . DIRECTORY_SEPARATOR . $classFileName . $endpointFileExtension;
        }
    }

    public static function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}
