<?php

namespace Framework\Base\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class QueueWorker
 * @package Framework\Base\Terminal\Commands
 */
class QueueWorker
{
    use ApplicationAwareTrait;

    /**
     * @param string $queueName
     */
    public function handle(string $queueName)
    {
        $connection = new AMQPStreamConnection(
            'localhost',    #host
            5672,           #port
            'guest',        #user
            'guest'         #password
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            $queueName,    #queue name, the same as the sender
            false,          #passive
            false,          #durable
            false,          #exclusive
            false           #autodelete
        );

        $channel->basic_consume(
            $queueName,                    #queue
            '', #consumer tag - Identifier for the consumer, valid within the current channel. just string
            false, #no local - TRUE: the server will not send messages to the connection that published them
            true, #no ack - send a proper acknowledgment from the worker, once we're done with a task
            false,        #exclusive - queues may only be accessed by the current connection
            false, #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
            [$this, 'processQueue']    #callback - method that will receive the message
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * @param $msg
     */
    public function processQueue($msg)
    {
        /* ... CODE TO PROCESS ORDER HERE ... */
    }
}
