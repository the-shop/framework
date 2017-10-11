<?php

namespace Framework\Base\Auth\Controller;

use Application\CrudApi\Model\Generic;
use Firebase\JWT\JWT;
use Framework\Base\Application\Exception\AuthenticationException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Model\Modifiers\HashFilter;
use Framework\Base\Queue\Adapters\Sync;
use Framework\Base\Queue\TaskQueue;
use Framework\Http\Controller\Http;
use Framework\Http\Request\Request;

/**
 * Class AuthController
 * @package Framework\Base\Auth\Controller
 */
class AuthController extends Http
{
    /**
     * @return string
     * @throws \Framework\Base\Application\Exception\AuthenticationException
     * @throws \RuntimeException
     * @throws NotFoundException
     */
    public function authenticate()
    {
        $authModels = $this->getRepositoryManager()->getAuthenticatableModels();
        $post = $this->getPost();
        $model = $exception = null;
        $attemptStrategies = [];

        foreach ($authModels as $resourceName => $params) {
            if (count(array_diff($params['credentials'], array_keys($post))) === 0 &&
                count($post) === count($params['credentials'])
            ) {
                $attemptStrategies[] = [
                    'repository' => $this->getRepositoryFromResourceName($resourceName),
                    'class' => '\\Framework\\Base\\Auth\\' . ucfirst(strtolower($params['strategy'])) . 'AuthStrategy',
                    'credentials' => $params['credentials'],
                ];
            }
        }

        if (empty($attemptStrategies) === true) {
            throw new \RuntimeException('Auth strategy not registered');
        }

        foreach ($attemptStrategies as $strategy) {
            if (class_exists($strategy['class']) === false) {
                throw new \RuntimeException('Strategy not implemented');
            }
            try {
                /**
                 * @var \Framework\Base\Auth\AuthStrategyInterface $auth
                 * @var \Framework\Base\Model\BrunoInterface $model
                 */
                $auth = new $strategy['class']($post, $strategy['repository']);
                $model = $auth->validate($strategy['credentials']);
            } catch (AuthenticationException $e) {
                $exception = $e;
            } catch (NotFoundException $e) {
                $exception = $e;
            }
        }

        if ($model === null) {
            throw $exception;
        }

        /**
         * @todo implement key generation, adjustable time on token expiration, algorithm selection
         */
        $key = 'rV)7Djb{DpEpY5ex';
        JWT::$timestamp = time();
        $payload = [
            'iss' => 'framework.the-shop.io',
            'exp' => JWT::$timestamp + 3600,
            'modelId' => $model->getId(),
            'resourceName' => $model->getCollection(),
            'aclRole' => '',
        ];
        $alg = 'HS384';
        $jwt = JWT::encode($payload, $key, $alg);

        return $jwt;
    }

    /**
     * @param Request $request
     * @return string
     * @throws NotFoundException
     * @throws \Exception
     */
    public function forgotPassword(Request $request)
    {
        // Check if there is email field
        $postParams = $request->getPost();
        if (array_key_exists('email', $postParams) === false) {
            throw new NotFoundException('Email field missing.', 404);
        }

        // Load user model
        $model = $this->getRepositoryFromResourceName('users')
            ->loadOneBy([
                'email' => $postParams['email'],
            ]);

        // If no model found, throw exception
        if (!$model) {
            throw new NotFoundException('User not found.', 404);
        }

        // Generate random token and timestamp and set to profile
        $passwordResetToken = md5(uniqid(rand(), true));
        $passwordResetTime = (new \DateTime())->format('U');
        $model->setAttributes(
            [
                'passwordResetToken' => $passwordResetToken,
                'passwordResetTime' => $passwordResetTime,
            ]
        );

        // Try to save model and send password reset email
        if ($model->save()) {
            $modelAttributes = $model->getAttributes();
            $webDomain = $this->getApplication()
                ->getConfiguration()
                ->getPathValue('env.WEB_DOMAIN');
            $webDomain .= 'reset-password';
            $subject = 'Password reset confirmation link!';
            $html = "<html>
                    <body>
                    <p> Please, visit this link below to change your password.</p>
                    <p> 
                    <a href=\"{$webDomain}?token={$modelAttributes['passwordResetToken']}\">
                    Click here to set a new password.
                    </a></p>
                    </body>
                </html>";

            if ($this->sendEmail($model, $subject, $html)) {
                return 'You will shortly receive an email with the link to reset your password.';
            }

            throw new \Exception('Issue with sending password reset email.');
        }
    }

    /**
     * @param Request $request
     * @return string
     * @throws NotFoundException
     * @throws \Exception
     * @throws \HttpRuntimeException
     */
    public function resetPassword(Request $request)
    {
        $postParams = $request->getPost();

        // Check if token is provided
        if (array_key_exists('token', $postParams) === false) {
            throw new NotFoundException('Token not provided.', 404);
        }

        // Load user model
        $model = $this->getRepositoryFromResourceName('users')
            ->loadOneBy([
                'passwordResetToken' => $postParams['token'],
            ]);

        // If no model found, throw exception
        if (!$model) {
            throw new NotFoundException('Invalid token provided.', 404);
        }

        $modelAttributes = $model->getAttributes();

        // Check timestamps
        $unixNow = (int) (new \DateTime())->format('U');
        if ($unixNow - $modelAttributes['passwordResetTime'] > (24 * 60 * 60)) {
            throw new \HttpRuntimeException('Token has expired.', 400);
        }

        $newPassword = $postParams['newPassword'];
        $repeatNewPassword = $postParams['repeatNewPassword'];

        // Check passwords
        if ($newPassword !== $repeatNewPassword) {
            throw new \InvalidArgumentException('Passwords mismatch');
        }

        $model->addFieldFilter('password', new HashFilter());
        $model->setAttribute('password', $newPassword);

        // Try to save model and send confirmation email
        if ($model->save()) {
            $subject = 'Password successfully changed!';
            $html = "<html>
                    <body>
                    <p> Hey, you have successfully changed your password.</p>
                    </body>
                </html>";

            $this->sendEmail($model, $subject, $html);

            return 'Password successfully changed.';
        }

        throw new \Exception('Issue with saving new password!');
    }

    /**
     * @param BrunoInterface $model
     * @param $subject
     * @param $html
     * @return mixed
     */
    private function sendEmail(BrunoInterface $model, $subject, $html)
    {
        $profileAttributes = $model->getAttributes();
        $appConfiguration = $this->getApplication()
            ->getConfiguration();

        $emailSender = new EmailSender(new SendGrid());
        $emailSender->setClient(
            new \SendGrid($appConfiguration->getPathValue('env.SENDGRID_API_KEY'))
        );

        $emailSender->setFrom(
            $appConfiguration
                ->getPathValue('env.PRIVATE_MAIL_FROM')
        );
        $emailSender->setSubject($subject);
        $emailSender->setTo($profileAttributes['email']);
        $emailSender->setHtmlBody($html);

        return TaskQueue::addTaskToQueue(
            'email',
            Sync::class,
            [
                'taskClassPath' => $emailSender,
                'method' => 'send',
                'parameters' => [],
            ]
        );
    }
}
