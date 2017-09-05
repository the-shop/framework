<?php

namespace Framework\Http\Render;

use Framework\Base\Render\Render;
use Framework\Base\Response\ResponseInterface;
use Mustache_Engine;

/**
 * Class Mustache
 * @package Framework\Http\Render
 */
class Mustache extends Render
{
    /**
     * @var null
     */
    private $templateName = '';

    public function render(ResponseInterface $response)
    {
        $rootPath = str_replace('public', '', getcwd());
        $mustacheEngine = new Mustache_Engine(
            [
                'loader' => new \Mustache_Loader_FilesystemLoader(
                    $rootPath . getenv('MUSTACHE_TEMPLATES_DIR_PATH'),
                    [
                        'extension' => getenv('MUSTACHE_FILE_EXTENSION')
                    ]
                )
            ]
        );

        $responseBody = $response->getBody();

        http_response_code($response->getCode());

        header('Content-type: text/html');

        $rendered = $mustacheEngine->render($this->getTemplateName(), $responseBody);

        echo $rendered;

        return $rendered;
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName(string $templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @return null
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }
}
