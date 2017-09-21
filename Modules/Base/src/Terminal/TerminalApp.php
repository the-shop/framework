<?php

namespace Framework\Base\Terminal;

use Framework\Base\Application\ApplicationConfiguration;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Terminal\Output\TerminalOutput;
use Framework\Base\Terminal\Router\Dispatcher;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;

/**
 * Class TerminalApp
 * @package Framework\Base\TerminalApp
 */
class TerminalApp extends BaseApplication
{
    /**
     * TerminalApp constructor.
     * @param ApplicationConfiguration|null $applicationConfiguration
     */
    public function __construct(ApplicationConfiguration $applicationConfiguration = null)
    {
        $stream = fopen('php://stdout', 'w');
        $this->setRenderer(new TerminalOutput($stream));
        $this->setDispatcher(new Dispatcher());
        $this->setResponse(new Response());

        parent::__construct($applicationConfiguration);
    }

    public function handle()
    {
        $handlerPath = $this->getDispatcher()->getHandler();

        $handler = new $handlerPath();
        $handler->setApplication($this);
        $handlerOutput = $handler->handle();

        $response = $this->getResponse();

        $response->setBody($handlerOutput);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function buildRequest()
    {
        $request = new Request();
        $request->setServer($_SERVER);

        $this->setRequest($request);

        return $request;
    }
}
