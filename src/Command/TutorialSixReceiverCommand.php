<?php

namespace App\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sender is @see \App\Controller\TutorialController::six()
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialSixReceiverCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('tutorial:six:receiver');
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

        // Declare a queue to receive the client's tasks
        $channel->queue_declare('tutorial_six', false, false, false, false);

        // A callback to receive the messages sent by clients
        $callback = function (AMQPMessage $req) use ($output, $channel) {
            $n = intval($req->body);
            $reply_to = $req->get('reply_to');
            $correlation_id = $req->get('correlation_id');

            // Calculate Fibonacci number
            $fib = TutorialSixReceiverCommand::fib($n);
            $output->writeln("Fibonacci number of $n is $fib");

            // Create a response and publish it in callback queue
            $response = new AMQPMessage(
                json_encode(['fib' => $fib]),
                ['correlation_id' => $correlation_id]
            );
            $channel->basic_publish($response, '', $reply_to);
        };

        // Start consuming
        $channel->basic_consume('tutorial_six', '', false, true, false, false, $callback);
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // Close the channel and the connection
        $channel->close();
        $connection->close();
    }

    /**
     * Calculate the Fibonacci number in a very slow manner.
     *
     * @param $n
     *
     * @return int
     */
    private static function fib($n)
    {
        if ($n == 0) {
            return 0;
        }
        if ($n == 1) {
            return 1;
        }

        return static::fib($n - 1) + static::fib($n - 2);
    }
}
