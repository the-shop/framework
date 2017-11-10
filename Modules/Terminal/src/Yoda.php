<?php

namespace Framework\Terminal;

use Framework\Base\Application\ApplicationConfiguration;
use Framework\Base\Application\BaseApplication;
use Framework\Terminal\Output\TerminalOutput;
use Framework\Terminal\Router\Dispatcher;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;

/**
 * Class TerminalApp
 * @package Framework\Base\TerminalApp
 */
class Yoda extends BaseApplication
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
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = $this->getDispatcher();
        $handlerPath = $dispatcher->getHandler();

        /**
         * @var \Framework\Terminal\Commands\CommandHandlerInterface $handler
         */
        $handler = new $handlerPath();
        $handler->setApplication($this);
        $parameterValues = array_values($dispatcher->getCommandParameters());
        $handlerOutput = $handler->run($parameterValues);

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
