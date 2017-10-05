<?php

namespace Framework\Base\Queue\Adapters;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 * @package Framework\Base\Queue\Adapters
 */
class RabbitMQ
{
    /**
     * @param string $queueName
     * @param array $payload
     */
    public function handle(string $queueName, array $payload = [])
    {
        /**
         * Create a connection to RabbitAMQP
         */
        $connection = new AMQPStreamConnection(
            'localhost',    #host - host name where the RabbitMQ server is running
            5672,           #port - port number of the service, 5672 is the default
            'guest',        #user - username to connect to server
            'guest'         #password
        );

        /** @var $channel AMQPChannel */
        $channel = $connection->channel();

        $channel->queue_declare(
            $queueName,    #queue name - Queue names may be up to 255 bytes of UTF-8 characters
            false,          #passive - can use this to check whether an exchange exists without modifying the server state
            false,          #durable - make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
            false,          #exclusive - used by only one connection and the queue will be deleted when that connection closes
            false           #autodelete - queue is deleted when last consumer unSubscribes
        );

        $msg = new AMQPMessage($payload);

        $channel->basic_publish(
            $msg,           #message
            '',             #exchange
            $queueName     #routing key
        );

        $channel->close();
        $connection->close();
    }
}
