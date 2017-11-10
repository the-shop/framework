<?php

namespace Framework\Base\Queue\Adapters;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 * @package Framework\Base\Queue\Adapters
 */
class RabbitMQ implements QueueAdapterInterface
{
    /**
     * @param string $queueName
     * @param array $payload
     * @return bool
     */
    public function handle(string $queueName, array $payload = [])
    {
        /**
         * Create a connection to RabbitAMQP
         */
        $connection = new AMQPStreamConnection(
            getenv('RABBIT_MQ_HOST', 'localhost'), #host
            getenv('RABBIT_MQ_PORT', 5672), #port
            getenv('RABBIT_MQ_USER', 'guest'), #user
            getenv('RABBIT_MQ_PASSWORD', 'guest') #password
        );

        $channel = $connection->channel();

        $channel->queue_declare(
            $queueName, #queue name - Queue names may be up to 255 bytes of UTF-8 characters
            false, #passive - can use this to check whether an exchange exists without
            # modifying the server state
            true,   #durable - make sure that RabbitMQ will never lose our queue if a crash
            # occurs - the queue will survive a broker restart
            false, #exclusive - used by only one connection and the queue will be deleted
            # when that connection closes
            false #autodelete - queue is deleted when last consumer unSubscribes
        );

        $msg = new AMQPMessage(
            json_encode($payload),
            [
                'delivery mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $channel->basic_publish(
            $msg,          #message
            '',   #exchange
            $queueName     #routing key
        );

        $channel->close();
        $connection->close();

        return true;
    }
}
