<?php

namespace Application\Test\Application\Listeners;

use Application\Listeners\ProjectMember;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

class ProjectMemberTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $project;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $user1;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $user2;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $slackMessage;

    public function setUp()
    {
        parent::setUp();
        $this->user1 = $this->getApplication()
                            ->getRepositoryManager()
                            ->getRepositoryFromResourceName('users')
                            ->newModel()
                            ->setAttributes(
                                [
                                    'name' => 'test user',
                                    'email' => $this->generateRandomEmail(20),
                                    'password' => 'test',
                                    'slack' => 'test1'
                                ]
                            )
                            ->save();

        $this->user2 = $this->getApplication()
                            ->getRepositoryManager()
                            ->getRepositoryFromResourceName('users')
                            ->newModel()
                            ->setAttributes(
                                [
                                    'name' => 'test user2',
                                    'email' => $this->generateRandomEmail(20),
                                    'password' => 'test',
                                    'slack' => 'test2'
                                ]
                            )
                            ->save();

        $this->project = $this->getNewProject()
                              ->setAttribute('members', [$this->user1->getId()])
                              ->save();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deleteCollection($this->project);
        $this->deleteCollection($this->user1);
        $this->deleteCollection($this->slackMessage);
    }

    public function testHandle()
    {
        $this->project->setAttribute('members', [$this->user2->getId()]);

        $listener = new ProjectMember();
        $listener->setApplication($this->getApplication());

        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient())
                  ->setApplication($this->getApplication());

        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $listener->handle($this->project);

        $this->slackMessage = $this->getApplication()
                                   ->getRepositoryManager()
                                   ->getRepositoryFromResourceName('slackMessages')
                                   ->loadOneBy(['recipient' => $this->user1->getAttribute('slack')]);

        $slackMessage = $this->getApplication()
                             ->getRepositoryManager()
                             ->getRepositoryFromResourceName('slackMessages')
                             ->loadOneBy(['recipient' => $this->user2->getAttribute('slack')]);

        $this::assertStringStartsWith(
            'Hey, you\'ve just been removed from project ',
            $this->slackMessage->getAttribute('message')
        );
        $this::assertEquals(
            SlackService::HIGH_PRIORITY,
            $this->slackMessage->getAttribute('priority')
        );
        $this::assertEquals(false, $this->slackMessage->getAttribute('sent'));

        $this::assertStringStartsWith(
            'Hey, you\'ve just been added to project ',
            $slackMessage->getAttribute('message')
        );
        $this::assertEquals(SlackService::HIGH_PRIORITY, $slackMessage->getAttribute('priority'));
        $this::assertEquals(false, $slackMessage->getAttribute('sent'));
    }
}
