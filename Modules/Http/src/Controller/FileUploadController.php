<?php

namespace Framework\Http\Controller;

use Application\Services\FileUploadService;
use Framework\Base\Application\Exception\NotFoundException;

/**
 * Class FileUploadController
 * @package Framework\Http\Controller
 */
class FileUploadController extends Http
{
    /**
     * @return array
     */
    public function uploadFile()
    {
        $userId = $this->getApplication()
        ->getRequestAuthorization()
        ->getId();

        $post = $this->getPost();

        if (array_key_exists('projectId', $post) === true) {
            $projectId = $post['projectId'];
        } else {
            $projectId = null;
        }

        $files = $this->getFiles();

        $repository = $this->getRepositoryFromResourceName('uploads');

        $response = [];

        $uploadService = $this->getApplication()
            ->getService(FileUploadService::class);

        foreach ($files as $file) {
            $upload = $repository->newModel();

            $fileName = $userId . '-' . bin2hex(random_bytes(20)) . '.' .
                $this->getClientOriginalExtension($file);

            $filePath = $fileName;
            $uploadService->uploadFile($filePath, $fileName);

            //$fileUrl = Storage::cloud()->url($fileName);

            $upload->setAttributes([
               'projectId' => $projectId,
                'name' => $this->getName($file),
                'fileUrl' => 'test',
            ])
                ->save();

            $response[] = $upload;
        }

        return $response;
    }

    /**
     * Lists all uploaded files with set projectId
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface[]
     * @throws NotFoundException
     */
    public function getProjectUploads(string $identifier)
    {
        $project = $this->getRepositoryFromResourceName('projects')
            ->loadOne($identifier);
        if ($project === null) {
            throw new NotFoundException('Project with given ID not found', 404);
        }

        $uploads = $this->getRepositoryFromResourceName('uploads')
        ->loadMultiple(['projectId' => $identifier]);

        return $uploads;
    }


    /**
     * Deletes uploaded files
     * @param string $identifier
     * @return \Framework\Base\Model\BrunoInterface[]
     * @throws NotFoundException
     */
    public function deleteProjectUploads(string $identifier)
    {
        $project = $this->getRepositoryFromResourceName('projects')
            ->loadOne($identifier);
        if ($project === null) {
            throw new NotFoundException('Project with given ID not found', 404);
        }

        $uploads = $this->getRepositoryFromResourceName('uploads')
            ->loadMultiple(['projectId' => $identifier]);

        foreach ($uploads as $upload) {
            $upload->delete();
        }

        return $uploads;
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $filePath The new file name
     *
     * @return string containing
     */
    protected function getName(string $filePath)
    {
        $originalName = str_replace('\\', '/', $filePath);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

    /**
     * Returns the original file extension.
     *
     * It is extracted from the original file name that was uploaded.
     * Then it should not be considered as a safe value.
     *
     * @param string $filePath
     * @return string The extension
     */
    protected function getClientOriginalExtension(string $filePath)
    {
        return pathinfo($this->getName($filePath), PATHINFO_EXTENSION);
    }
}
