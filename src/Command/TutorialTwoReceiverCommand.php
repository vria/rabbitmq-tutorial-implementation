<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see \App\Controller\TutorialController::two()
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialTwoReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:two:receiver');
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

        // Don't dispatch a new message to a worker until it has processed and
        // acknowledged the previous one. Instead, it will dispatch it to the
        // next worker that is not still busy.
        $channel->basic_qos(null, 1, null);

        // Declare a queue for us to receive from. We declare the queue here,
        // as well. Because we might start the consumer before the publisher.
        $channel->queue_declare('tutorial_two', false, false, false, false);

        $callback = function (AMQPMessage $msg) use ($output) {
            // Decode the data sent by a sender
            $body = json_decode($msg->body, true);
            $complexity = (int)$body['complexity'];
            $taskId = $body['task_id'];

            // Process a tack
            sleep($complexity);
            $output->writeln("Treated the task $taskId with complexity $complexity");

            // Acknowledge received message
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        // Start consuming
        $channel->basic_consume('tutorial_two', '', false, false, false, false, $callback);
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // Close the channel and the connection
        $channel->close();
        $connection->close();
    }
}
