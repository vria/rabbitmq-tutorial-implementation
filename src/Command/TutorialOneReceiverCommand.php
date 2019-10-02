<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sender is @see \App\Controller\TutorialController::one()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class TutorialOneReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:one:receiver');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a connection to the RabbitMQ server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Declare a queue for us to receive from. We declare the queue here,
        // as well. Because we might start the consumer before the publisher.
        $channel->queue_declare('hello', false, false, false, false);

        // A callback to receive the messages sent by the server
        $callback = function ($msg) use ($output) {
            echo 'Received ', $msg->body, "\n";
        };

        // Start consuming
        $channel->basic_consume('hello', '', false, true, false, false, $callback);
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // Close the channel and the connection
        $channel->close();
        $connection->close();
    }
}
