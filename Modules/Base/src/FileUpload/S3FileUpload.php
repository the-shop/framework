<?php

namespace Framework\Base\FileUpload;

use Aws\S3\Exception\S3Exception;
use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class S3FileUpload
 * @package Framework\Base\FileUpload
 */
class S3FileUpload extends FileUpload
{
    use ApplicationAwareTrait;

    /**
     * @param $filePath
     * @param $fileName
     * @return bool
     * @throws \Exception
     */
    public function uploadFile($filePath, $fileName)
    {

        $client = $this->getClient();

        $bucket = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('env.S3_BUCKET');

        try {
            $response = $client->putObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
                'SourceFile' => $filePath,
                'ACL' => 'public-read'
            ]);
        } catch (S3Exception $e) {
            throw new \Exception('There was an error uploading the file.', 400);
        }

        return $response->get('ObjectURL');
    }
}
