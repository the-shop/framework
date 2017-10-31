<?php

namespace Framework\Http\Test\Controller;

use Application\Services\FileUploadService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Auth\RequestAuthorization;
use Framework\Base\FileUpload\S3FileUpload;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\FileUpload\DummyS3Client;
use Framework\Base\Test\UnitTest;
use Framework\Http\Controller\FileUploadController;
use Framework\Http\Request\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadControllerTest extends UnitTest
{
    use ProfileRelated, ProjectRelated, Helpers;

    public function setUp()
    {
        parent::setUp();
        $newUser = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->setAttributes([
                'name' => 'test user',
                'email' => $this->generateRandomEmail(20),
                'password' => 'test',
                'skills' => ['PHP'],
                'xp' => 200,
                'employeeRole' => 'Apprentice',
                'minimumsMissed' => 0,
                'employee' => true,
                'slack' => $this->generateRandomString(),
                'active' => true,
            ])
            ->save();

        $this->setTaskOwner($newUser);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @return BrunoInterface
     */
    public function testFileUpload()
    {
        $project = $this->getNewProject();
        $project->save();

        $servicesConfig = [
            FileUploadService::class => [
                'fileUploadInterface' => S3FileUpload::class,
                'fileUploadClient' => [
                    'classPath' => DummyS3Client::class,
                    'constructorArguments' => [],
                ],
            ]
        ];

        $app = $this->getApplication();
        $app->getConfiguration()
            ->setPathValue('servicesConfig', $servicesConfig);

        $requestAuth = new RequestAuthorization();
        $requestAuth->setId($this->profile->getId());
        $app->setRequestAuthorization($requestAuth);

        $filePath = $app->getRootPath() . getenv('FILE_TO_UPLOAD_TEST_PATH');

        $uploadedFile = new UploadedFile($filePath, 'test');
        $request = new Request();
        $request->setFiles([$uploadedFile]);
        $request->setPost([
            'projectId' => $project->getId()
        ]);
        $app->setRequest($request);

        $uploadFileController = new FileUploadController();
        $uploadFileController->setApplication($app);

        /**
         * @var BrunoInterface[] $out
         */
        $out = $uploadFileController->uploadFile();

        $this->assertInstanceOf(BrunoInterface::class, $out[0]);

        $uploadAtt = $out[0]->getAttributes();

        $this->assertEquals('uploads', $out[0]->getCollection());
        $this->assertEquals($project->getId(), $uploadAtt['projectId']);
        $this->assertEquals('ObjectURL.testing.url', $uploadAtt['fileUrl']);
        $this->assertEquals('test', $uploadAtt['name']);

        return $out[0];
    }

    /**
     * @depends testFileUpload
     * @param BrunoInterface $uploadModel
     * @return mixed
     */
    public function testLoadProjectUploads(BrunoInterface $uploadModel)
    {
        $uploadFileController = new FileUploadController();
        $uploadFileController->setApplication($this->getApplication());

        $out = $uploadFileController->getProjectUploads($uploadModel->getAttribute('projectId'));

        $loadedModeL = array_values($out)[0];

        $this->assertEquals($loadedModeL->getAttributes(), $uploadModel->getAttributes());

        return $loadedModeL;
    }

    /**
     * @depends testLoadProjectUploads
     * @param BrunoInterface $uploadModel
     */
    public function testDeleteProjectUploads(BrunoInterface $uploadModel)
    {
        $uploadFileController = new FileUploadController();
        $uploadFileController->setApplication($this->getApplication());

        $uploadFileController->deleteProjectUploads(
            $uploadModel->getAttribute('projectId')
        );

        $check = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('uploads')
            ->loadMultiple(['projectId' => $uploadModel->getAttribute('projectId')]);

        $this->assertEmpty($check);
        $this->assertEquals([], $check);

        $this->profile->delete();
        $this->purgeCollection('projects');
        $this->purgeCollection('tasks');
        $this->purgeCollection('sprints');
        $this->purgeCollection('uploads');
        $this->purgeCollection('users');
    }
}
