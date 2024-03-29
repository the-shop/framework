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
    private $templatePath = '';

    public function __construct(array $config = [])
    {
        if (isset($config["templatePath"])) {
            $this->setTemplatePath($config["templatePath"]);
        }
    }

    public function render(ResponseInterface $response)
    {
        $rootPath = str_replace('public', '', getcwd());
        $mustacheEngine = new Mustache_Engine(
                [
            'loader' => new \Mustache_Loader_FilesystemLoader(
                    $rootPath . getenv('MUSTACHE_TEMPLATES_DIR_PATH'), [
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

    /**
     * @return null
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function generateHtml(array $htmlOptions = [])
    {

        if (array_key_exists('template', $htmlOptions) === false
                || array_key_exists('data', $htmlOptions) === false
                || array_key_exists('dataTemplate', $htmlOptions['data']) === false
                || array_key_exists('dataToFill', $htmlOptions['data']) === false
        ) {
            throw new \InvalidArgumentException('html options array not formatted correctly.', 400);
        }

        $htmlTemplate = '';
        $mustacheEngine = new Mustache_Engine(
                [
            'loader' => new \Mustache_Loader_FilesystemLoader(
                    $this->getTemplatePath(), [
                'extension' => 'html'
                    ]
            )
                ]
        );
        $htmlTemplate = $mustacheEngine->render( $htmlOptions['template']);

        if (empty($htmlOptions['data']['dataTemplate']) === false) {
            foreach ($htmlOptions['data']['dataToFill'] as $data) {
                $htmlTemplate .= $mustacheEngine->render($htmlOptions['data']['dataTemplate'], $data);
            }
        }

        echo($htmlTemplate."\n\n\n");

        return $htmlTemplate;
    }
}
