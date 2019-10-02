<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sender is @see \App\Controller\TutorialController::four()
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialFourReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:four:receiver')
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
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
        $channel->exchange_declare('tutorial_four', 'direct', false, false, false);

        // Create a non-durable queue for this exact instance of receiver
        list($queue_name, ,) = $channel->queue_declare();
        $output->writeln($queue_name);

        $severities = $input->getOption('severity');
        if (empty($severities)) {
            $output->writeln('<error>Pass at least one severity option</error>');

            return;
        }

        // Create a binding of the created queue to exchange "tutorial_four"
        // for each severity level.
        foreach ($severities as $severity) {
            $channel->queue_bind($queue_name, 'tutorial_four', $severity);
        }

        // A callback to receive the messages sent by clients
        $callback = function (AMQPMessage $msg) use ($output) {
            $body = json_decode($msg->body, true);
            $taskId = $body['task_id'];
            $severity = $body['severity'];
            $output->writeln("Sent at $taskId with severity $severity");
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
