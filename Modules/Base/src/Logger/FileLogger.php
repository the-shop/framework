<?php

namespace Framework\Base\Logger;

/**
 * Class FileLogger
 * @package Framework\Base\Logger
 */
class FileLogger implements LoggerInterface
{
    /**
     * @var array|false|string
     */
    private $fileName = '';

    /**
     * @var array|false|string
     */
    private $fileDirPath = '';

    /**
     * FileLogger constructor.
     */
    /**
     * @var mixed|string
     */
    private $rootPath = '';

    /**
     * FileLogger constructor.
     */
    public function __construct()
    {
        $this->rootPath = str_replace('public', '', getcwd());
        $this->setFileName(getenv('FILE_LOGGER_FILE_NAME'));
        $this->setFileDirPath(getenv('FILE_LOGGER_FILE_DIR_PATH'));

        if (empty($this->getFileName()) || empty($this->getFileDirPath())) {
            throw new \RuntimeException('Filename with extension and directory path must be provided', 403);
        }
    }

    /**
     * @param LogInterface $log
     * @return mixed
     */
    public function log(LogInterface $log)
    {
        if ($log->isException()) {
            $event = $this->logException($log);
        } else {
            $event = $this->logMessage($log);
        }

        return $event;
    }

    /**
     * @param LogInterface $log
     * @return mixed
     * @throws \Exception
     */
    public function logException(LogInterface $log)
    {
        $payload = $log->getPayload();
        $exception = $payload->__toString();

        $fullFileDirectoryPath = $this->rootPath . $this->getFileDirPath();

        if (file_exists($fullFileDirectoryPath) === false) {
            $this->createNewDirectory($fullFileDirectoryPath);
        }

        $file = $fullFileDirectoryPath . $this->getFileName();

        $this->writeLogToFile($file, $exception);

        return $exception;
    }

    /**
     * @param LogInterface $log
     * @return mixed
     * @throws \Exception
     */
    public function logMessage(LogInterface $log)
    {
        $message = $log->getPayload();

        $fullFileDirectoryPath = $this->rootPath . $this->getFileDirPath();

        if (file_exists($fullFileDirectoryPath) === false) {
            $this->createNewDirectory($fullFileDirectoryPath);
        }

        $file = $fullFileDirectoryPath . $this->getFileName();

        $this->writeLogToFile($file, $message);

        return $message;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param string $fileDirPath
     */
    public function setFileDirPath(string $fileDirPath)
    {
        $this->fileDirPath = $fileDirPath;
    }

    /**
     * @return array|false|string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return array|false|string
     */
    public function getFileDirPath()
    {
        return $this->fileDirPath;
    }

    private function createNewDirectory(string $directoryPath)
    {
        if (mkdir($directoryPath, 0777) === false) {
            throw new \RuntimeException('Failed to create folder '. $directoryPath);
        }

        return true;
    }

    private function writeLogToFile($file, $message)
    {
        $logFile = fopen($file, 'a+');
        if ($logFile === false) {
            throw new \RuntimeException('Unable to open specified filename: ' . $file, 403);
        }
        $dateTime = '[' . (new \DateTime())->format('Y-m-d H:i:s') . ']';
        fwrite($logFile, $dateTime . ' ' . $message . PHP_EOL);
        fclose($logFile);

        return true;
    }
}
