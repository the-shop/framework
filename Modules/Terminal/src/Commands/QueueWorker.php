<?php

namespace Framework\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class QueueWorker
 * @package Framework\Base\Terminal\Commands
 */
class QueueWorker
{
    use ApplicationAwareTrait;

    /**
     * @return $this|string
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection(
            getenv('RABBIT_MQ_HOST', 'localhost'), #host
            getenv('RABBIT_MQ_PORT', 5672), #port
            getenv('RABBIT_MQ_USER', 'guest'), #user
            getenv('RABBIT_MQ_PASSWORD', 'guest') #password
        );

        /** @var $channel AMQPChannel */
        $channel = $connection->channel();

        $queueNames = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('queueNames');

        if (empty($queueNames) === true) {
            return 'No queueNames defined in configuration.';
        }

        // Leave connection open and listen to new queues until worker is manually closed
        while (true) {
            foreach ($queueNames as $queueName) {
                $channel->queue_declare(
                    $queueName,    #queue name, the same as the sender
                    false,          #passive
                    true,          #durable
                    false,          #exclusive
                    false           #autodelete
                );

                echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

                $channel->basic_qos(null, 1, null);

                $channel->basic_consume(
                    $queueName, #queue
                    '', #consumer tag - Identifier for the consumer, valid within the current channel. just string
                    false, #no local - TRUE: the server will not send messages to the connection that published them
                    false, #no ack - send a proper acknowledgment from the worker, once we're done with a
                    # task
                    false, #exclusive - queues may only be accessed by the current connection
                    false, #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
                    [$this, 'processQueue']    #callback - method that will receive the message
                );
            }
            $channel->wait();
        }

        return $this;
    }

    /**
     * @param $msg
     * @return $this
     */
    public function processQueue($msg)
    {
        echo " [x] Received " .  $msg->body . "\n";
        $taskInfo = json_decode($msg->body);

        // Execute task
        $response = call_user_func_array(
            [
                $taskInfo->taskClassPath,
                $taskInfo->method
            ],
            $taskInfo->parameters
        );

        sleep(substr_count($msg->body, '.'));
        echo " [x] Done! Response: " . $response . "\n";
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

        return $this;
    }
}
