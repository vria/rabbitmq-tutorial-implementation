<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sender is @see \App\Controller\TutorialController::four()
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialFiveReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:five:receiver')
            ->addArgument('routing_key', InputArgument::REQUIRED)
        ;
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

        // Create an exchange of direct type
        $channel->exchange_declare('tutorial_five', 'topic', false, false, false);

        // Create a non-durable queue for this exact instance of receiver
        list($queue_name, ,) = $channel->queue_declare();
        $output->writeln($queue_name);

        // Create a binding of the created queue to exchange "tutorial_five"
        $channel->queue_bind($queue_name, 'tutorial_five', $input->getArgument('routing_key'));

        // A callback to receive the messages sent by clients
        $callback = function (AMQPMessage $msg) use ($output) {
            $output->writeln('Received message with routing key'.$msg->delivery_info['routing_key']);
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
