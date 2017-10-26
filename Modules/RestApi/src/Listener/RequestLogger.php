<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;

/**
 * Class RequestLogger
 * @package App\Http\Middleware
 */
class RequestLogger implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * Handle an incoming request.
     * @param $payload
     * @return mixed
     */
    public function handle($payload)
    {
        $app = $this->getApplication();
        $request = $app->getRequest();
        $requestAuth = $app->getRequestAuthorization();

        $name = '';
        $id = '';

        if ($requestAuth !== null) {
            $id = $requestAuth->getId();
            $model = $requestAuth->getModel();
            if ($model !== null) {
                $name = $model->getAttribute('name');
            }
        }

        $logData = [
            'name' => $name,
            'id' => $id,
            'date' => (new \DateTime())->format('d-m-Y H:i:s'),
            'ip' => $request->getClientIp(),
            'uri' => $request->getUri(),
            'method' => $request->getMethod()
        ];

        $app->getRepositoryManager()
            ->getRepositoryFromResourceName('logs')
            ->newModel()
            ->setAttributes($logData)
            ->save();

        return $this;
    }
}
