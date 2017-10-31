<?php

namespace Framework\Http\Controller;

use Application\Services\FileUploadService;
use Framework\Base\Application\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        /**
         * @var UploadedFile[] $files
         */
        foreach ($files as $file) {
            $upload = $repository->newModel();

            $fileName = $userId . '-' . bin2hex(random_bytes(20)) . '.' .
                $file->guessExtension();

            $filePath = $file->getRealPath();

            $fileUrl = $uploadService->uploadFile($filePath, $fileName);

            $upload->setAttributes([
                'projectId' => $projectId,
                'name' => $file->getClientOriginalName(),
                'fileUrl' => $fileUrl,
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
}
