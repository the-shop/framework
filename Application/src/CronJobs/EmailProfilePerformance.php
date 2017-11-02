<?php

namespace App\Console\Commands;

use Application\Helpers\ProfileOverall;
use Application\Services\EmailService;
use Application\Services\ProfilePerformance;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class EmailProfilePerformance
 * @package App\Console\Commands
 */
class EmailProfilePerformance extends CronJob
{
    use ApplicationAwareTrait;

    /**
     * Execute the console command.
     */
    public function execute()
    {
        $app = $this->getApplication();
        $appConfig = $app->getConfiguration();

        // Set time range
        $arguments = $this->getArgs();
        $daysAgo = (int)$arguments['daysAgo'];
        $unixNow = (int)(new \DateTime())->format('U');
        $unixAgo = $unixNow - $daysAgo * 24 * 60 * 60;

        $forAccountants = $arguments['accountants'];

        // If accountants flag is passed set date range from 1st day of current month until
        // last day of current month
        if ($forAccountants === true) {
            $unixNow = (int)(new \DateTime())
                ->modify('last day of this month')
                ->format('U');
            $unixAgo = (int)(new \DateTime())
                ->modify('first day of this month')
                ->format('U');
        }

        $adminAggregation = [];

        /**
         * @var EmailService $emailService
         */
        $emailService = $app->getService(EmailService::class);
        $emailService->setApplication($app);
        $templateDirPath = $app->getRootPath() . '/Application/src/Templates/Emails/profile';

        /**
         * @var ProfilePerformance $performance
         */
        $performance = $app->getService(ProfilePerformance::class);

        // Get profiles
        $usersRepository = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users');
        $profiles = $usersRepository->loadMultiple(['employee' => true]);

        foreach ($profiles as $profile) {
            if ($forAccountants === true
                && $profile->getAttribute('employee') === false
            ) {
                continue;
            }

            $profileAttributes = $profile->getAttributes();

            $data = $performance->aggregateForTimeRange($profile, $unixAgo, $unixNow);
            $data['name'] = $profileAttributes['name'];
            $data['fromDate'] = \DateTime::createFromFormat('U', $unixAgo)
                ->format('Y-m-d');
            $data['toDate'] = \DateTime::createFromFormat('U', $unixNow)
                ->format('Y-m-d');

            // If option is not passed send mail to each profile
            if ($forAccountants === false) {
                $template = $templateDirPath . 'email-template.php';
                $view = $templateDirPath . 'performance-report-html.template.php';
                $subject = $appConfig
                    ->getPathValue('env.PRIVATE_MAIL_SUBJECT');

                if (isset($profileAttributes['email']) && empty($profileAttributes['email']) ===
                    false && isset($profileAttributes['active']) && $profileAttributes['active']
                    === true
                ) {
                    $emailService->sendEmail(
                        $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                        $subject,
                        $profile->getAttribute('email'),
                        [
                            'template' => $template,
                            'data' => [
                                'dataTemplate' => $view,
                                'dataToFill' => $adminAggregation,
                            ],
                        ]
                    );
                }
            }

            $adminAggregation[] = $data;

            if ($forAccountants === true) {
                // Update profile overall stats
                $profileOverall = (new ProfileOverall())->getProfileOverallRecord($profile);
                $overallAtt = $profileOverall->getAttributes();
                $calculatedCost = $overallAtt['totalCost'] += $data['costTotal'];
                $calculatedProfit = $overallAtt['totalEarned'] - $calculatedCost;
                $profileOverall->setAttributes([
                    'totalCost' => $calculatedCost,
                    'profit' => $calculatedProfit,
                ]);
                $profileOverall->save();
            }
        }

        $overviewRecipients = $usersRepository->loadMultiple(['admin' => true]);

        // If option is passed get all admins and profiles with accountant role
        if ($forAccountants === true) {
            $accountants = $usersRepository->loadMultiple(['role' => 'accountant']);

            $overviewRecipients = array_merge($overviewRecipients, $accountants);
        }

        foreach ($overviewRecipients as $recipient) {
            $template = $templateDirPath . 'email-template.php';
            $view = '';

            if ($forAccountants === true) {
                $view = $templateDirPath . 'salary-performance-report-html.template.php';
            } else {
                $view = $templateDirPath .'admin-performance-report-html.template.php';
            }

            $subject = $appConfig
                ->getPathValue('env.ADMIN_PERFORMANCE_EMAIL_SUBJECT');

            if ($recipient->getAttribute('active') === true) {
                // Create pdf with salary report and attach it to email
                $attachments = [];
                /*$pdf = \PDF::loadView($view, [
                    'reports' => $adminAggregation,
                    'pdf' => true
                ])->output();
                $attachments['SalaryReport.pdf'] = $pdf;*/

                $emailService->sendEmail(
                    $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                    $subject,
                    $recipient->getAttribute('email'),
                    [
                        'template' => $template,
                        'data' => [
                            'dataTemplate' => $view,
                            'dataToFill' => $adminAggregation,
                        ],
                    ]
                );
            }
        }
    }
}
