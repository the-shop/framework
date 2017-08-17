<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\BaseApplication;
use Framework\Http\Render\Json;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Router\Dispatcher;

/**
 * Class RestApi
 * @package Framework\Application\RestApi
 */
class RestApi extends BaseApplication
{
    /**
     * RestApi constructor.
     * @param array $registerModules
     */
    public function __construct(array $registerModules = [])
    {
        $this->setRenderer(new Json());
        $this->setDispatcher(new Dispatcher());
        $this->setResponse(new Response());

        parent::__construct($registerModules);
    }

    /**
     * @inheritdoc
     */
    public function buildRequest()
    {
        $request = $this->getResolver()
            ->resolve(Request::class);

        $request->setPost(isset($_POST) ? $_POST : []);
        $request->setQuery(isset($_GET) ? $_GET : []);
        $request->setFiles(isset($_FILES) ? $_FILES : []);
        $request->setServer($_SERVER);

        unset($_POST);
        unset($_GET);
        unset($_FILES);

        $this->setRequest($request);

        return $request;
    }
}
