<?php

namespace Framework\Base\Terminal\Output;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Response\ResponseInterface;

/**
 * Class TerminalOutput
 * @package Framework\Base\TerminalApp\Output
 */
class TerminalOutput implements TerminalOutputInterface
{
    use ApplicationAwareTrait;
    /**
     * @var array
     */
    private $outputMessages = [];

    /**
     * @var
     */
    private $stream;

    /**
     * @var ColorFormatter
     */
    private $colorFormatter;

    /**
     * TerminalOutput constructor.
     * @param $stream
     */
    public function __construct($stream)
    {
        if (is_resource($stream) === false || ('stream' === get_resource_type($stream)) === false) {
            throw new \InvalidArgumentException('The TerminalOutput class needs a stream as its first argument.');
        }

        $this->setColorFormatter(new ColorFormatter());
        $this->setOutputStream($stream);
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function setOutputMessages(array $messages = [])
    {
        $this->outputMessages = $messages;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutputMessages()
    {
        return $this->outputMessages;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function render(ResponseInterface $response)
    {
        $responseCode = $response->getCode();
        $responseBody = $response->getBody();

        $foregroundColor = 'green';
        $backgroundColor = 'black';
        $colorFormatter = $this->getColorFormatter();

        $statusMessage = 'Status code: ' . $responseCode;

        if ($responseCode === 200) {
            $statusMessage .= ' command DONE.';
            $responseMessage =
                $colorFormatter->getColoredString(
                    'Response: ',
                    $foregroundColor,
                    $backgroundColor
                )
                . json_encode($responseBody);
        } else {
            $statusMessage .= ' command FAILED!';
            $foregroundColor = 'red';
            $backgroundColor = 'light_gray';
            $responseMessage =
                $colorFormatter->getColoredString(
                    'Response: '
                    . json_encode($responseBody),
                    $foregroundColor,
                    $backgroundColor
                );
        }

        $codeMsg = $colorFormatter->getColoredString(
            $statusMessage,
            $foregroundColor,
            $backgroundColor
        );

        $this->setOutputMessages([
            $codeMsg,
            $responseMessage,
        ]);

        $this->outputMessages();

        return $this;
    }

    /**
     * @return $this
     */
    public function outputMessages()
    {
        $messages = $this->getOutputMessages();

        foreach ($messages as $message) {
            $this->writeOutput($message, true);
        }

        $this->closeOutputStream();

        return $this;
    }

    /**
     * @param string $message
     * @param bool $newline
     * @return $this
     */
    public function writeOutput(string $message, bool $newline = false)
    {
        if (fwrite($this->stream, $message) === false ||
            ($newline && (fwrite($this->stream, PHP_EOL))) === false
        ) {
            throw new \RuntimeException('Unable to write output.');
        }

        return $this;
    }

    /**
     * @param $colorFormatter
     * @return $this
     */
    public function setColorFormatter($colorFormatter)
    {
        $this->colorFormatter = $colorFormatter;

        return $this;
    }

    /**
     * @return ColorFormatter
     */
    public function getColorFormatter()
    {
        return $this->colorFormatter;
    }

    /**
     * @return mixed
     */
    public function getOutputStream()
    {
        return $this->stream;
    }

    /**
     * @param $stream
     * @return $this
     */
    private function setOutputStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return $this
     */
    private function closeOutputStream()
    {
        $stream = $this->getOutputStream();

        fclose($stream);

        return $this;
    }
}
