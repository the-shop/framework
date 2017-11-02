<?php

namespace Application\CronJobs;

use Application\Helpers\GenerateHtmlData;
use Application\Helpers\ProfileOverall;
use Application\Services\EmailService;
use Application\Services\ProfilePerformance;
use Dompdf\Dompdf;
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

        $forAccountants = false;
        if (isset($arguments['accountants']) === true) {
            $forAccountants = $arguments['accountants'];
        }

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
        $templateDirPath = $app->getRootPath() . '/Application/src/Templates/Emails/profile/';

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
                $dataTemplate = $templateDirPath . 'performance-report-html.template.php';
                $htmlBody = GenerateHtmlData::generateHtml([
                    'template' => $template,
                    'data' => [
                        'dataTemplate' => $dataTemplate,
                        'dataToFill' => [$data],
                    ],
                ]);
                $subject = $appConfig
                    ->getPathValue('env.ADMIN_PERFORMANCE_EMAIL_SUBJECT');

                if (isset($profileAttributes['email']) === true
                    && empty($profileAttributes['email']) === false
                    && isset($profileAttributes['active'])
                    && $profileAttributes['active'] === true
                ) {
                    $emailService->sendEmail(
                        $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                        $subject,
                        $profile->getAttribute('email'),
                        $htmlBody
                    );
                }
            }

            $adminAggregation[] = $data;

            if ($forAccountants === true) {
                // Update profile overall stats
                $profileOverall = (new ProfileOverall())->setApplication($app)
                    ->getProfileOverallRecord($profile);
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
            if ($recipient->getAttribute('active') === true) {
                $template = $templateDirPath . 'email-template.php';
                $dataTemplate = '';

                if ($forAccountants === true) {
                    $dataTemplate = $templateDirPath . 'salary-performance-report-html.template.php';
                } else {
                    $dataTemplate = $templateDirPath . 'performance-report-html.template.php';
                }

                $subject = $appConfig
                    ->getPathValue('env.ADMIN_PERFORMANCE_EMAIL_SUBJECT');

                // Create pdf with salary report and attach it to email
                $attachments = [];
                $htmlBody = GenerateHtmlData::generateHtml([
                    'template' => $template,
                    'data' => [
                        'dataTemplate' => $dataTemplate,
                        'dataToFill' => $adminAggregation,
                    ],
                ]);

                $pdf = new Dompdf(['enable_remote' => true]);
                $pdf->setPaper('A4', 'landscape');
                $pdf->loadHtml($htmlBody);
                $pdf->render();
                $pdf = $pdf->output();
                $attachments['SalaryReport.pdf'] = $pdf;

                $emailService->sendEmail(
                    $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                    $subject,
                    $recipient->getAttribute('email'),
                    $htmlBody,
                    $attachments
                );
            }
        }
    }
}
