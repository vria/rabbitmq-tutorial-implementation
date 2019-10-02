<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sender is @see \App\Controller\TutorialController::three()
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialThreeReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:three:receiver');
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

        // Create an exchange of fanout type
        $channel->exchange_declare('tutorial_three', 'fanout', false, false, false);

        // Create a non-durable queue for this exact instance of receiver
        list($queue_name, ,) = $channel->queue_declare();
        $output->writeln('Queue name is '.$queue_name);

        // Bind the created queue to exchange 'tutorial_three'
        $channel->queue_bind($queue_name, 'tutorial_three');

        // A callback to receive the messages sent by the server
        $callback = function (AMQPMessage $msg) use ($output) {
            $body = json_decode($msg->body, true);
            $taskId = $body['task_id'];
            $output->writeln("Treated the task $taskId");
        };

        // Start consuming
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // Close the channel and the connection
        $channel->close();
        $connection->close();
    }
}
