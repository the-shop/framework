<?php

namespace Framework\Base\Terminal\Output;

/**
 * Class TerminalOutput
 * @package Framework\Base\TerminalApp\Output
 */
class TerminalOutput implements TerminalOutputInterface
{
    /**
     * @var array
     */
    private $outputMessages = [];

    /**
     * @var
     */
    private $stream;

    /**
     * TerminalOutput constructor.
     * @param $stream
     * @param array $messages
     */
    public function __construct($stream, array $messages = [])
    {
        if (is_resource($stream) === false || ('stream' === get_resource_type($stream)) === false) {
            throw new \InvalidArgumentException('The TerminalOutput class needs a stream as its first argument.');
        }

        $this->setOutputStream($stream);
        $this->setOutputMessages($messages);
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
     * @return mixed
     */
    public function getOutputStream()
    {
        return $this->stream;
    }

    /**
     * @param string $message
     * @param bool $newline
     * @return $this
     */
    public function writeOutput(string $message, bool $newline = false)
    {
        if (@fwrite($this->stream, $message) === false ||
            ($newline && (@fwrite($this->stream, PHP_EOL))) === false
        ) {
            throw new \RuntimeException('Unable to write output.');
        }

        fflush($this->stream);

        $this->closeOutputStream();

        return $this;
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
