<?php

namespace App\Controller;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * https://www.rabbitmq.com/tutorials/tutorial-one-php.html
 *
 * @author Vlad Riabchenko <contact@vria.eu>
 */
class TutorialController
{
    /**
     * @Route("/", name="tutorial_index")
     * @Template()
     */
    public function index()
    {
        return [];
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-one-php.html
     * The receiver is @see \App\Command\TutorialOneReceiverCommand
     *
     * @Route("/one", name="tutorial_one")
     *
     * @return Response
     */
    public function one()
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Declare a queue for us to send to
        $channel->queue_declare('hello', false, false, false, false);

        // Create and publish a message to the queue
        $msg = new AMQPMessage('Hello World!');
        $channel->basic_publish($msg, '', 'hello');

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response('ok');
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-two-php.html
     * The receiver is @see \App\Command\TutorialTwoReceiverCommand
     *
     * @Route("/two/{complexity}", name="tutorial_two", requirements={"complexity"="\d+"})
     * @Template()
     *
     * @param string $complexity
     * @return Response
     */
    public function two(string $complexity)
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Declare a queue for us to send to
        $channel->queue_declare('tutorial_two', false, false, false, false);

        // Generate task id
        $taskId = date('H:i:s');

        // Pass a complexity and task id in the message
        $msg = new AMQPMessage(json_encode([
            'complexity' => $complexity,
            'task_id' => $taskId,
        ]));

        // Publish a message to the queue
        $channel->basic_publish($msg, '', 'tutorial_two');

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response("Task $taskId has been sent.");
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-three-php.html
     * The receiver is @see \App\Command\TutorialThreeReceiverCommand
     *
     * @Route("/three", name="tutorial_three")
     *
     * @return Response
     */
    public function three()
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Create an exchange of fanout type
        $channel->exchange_declare('tutorial_three', 'fanout', false, false, false);

        // Generate task id
        $taskId = date('H:i:s');

        // Pass task id in the message
        $msg = new AMQPMessage(json_encode([
            'task_id' => $taskId,
        ]));

        // Publish a message to the queue
        $channel->basic_publish($msg, 'tutorial_three');

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response("Task $taskId has been sent.");
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-four-php.html
     * The receiver is @see \App\Command\TutorialFourReceiverCommand
     *
     * @Route("/four/{severity}", name="tutorial_four", requirements={"severity"="debug|normal|critical"})
     *
     * @param string $severity
     *
     * @return Response
     */
    public function four(string $severity)
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Create an exchange of direct type
        $channel->exchange_declare('tutorial_four', 'direct', false, false, false);

        // Generate task id
        $taskId = date('H:i:s');

        // Pass a task id and a severity in the message
        $msg = new AMQPMessage(json_encode([
            'task_id' => $taskId,
            'severity' => $severity,
        ]));
        $channel->basic_publish($msg, 'tutorial_four', $severity);

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response("Task $taskId of $severity severity has been sent.");
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-five-php.html
     * The receiver is @see \App\Command\TutorialFiveReceiverCommand
     *
     * @Route("/five/{facility}/{severity}", name="tutorial_five",
     *     requirements={
     *         "facility"="auth|cron|kern",
     *         "severity"="debug|normal|critical"
     *     }
     * )
     *
     * @param string $facility
     * @param string $severity
     *
     * @return Response
     */
    public function five(string $facility, string $severity)
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Create an exchange of direct type
        $channel->exchange_declare('tutorial_five', 'topic', false, false, false);

        // Pass a task id and a severity in the message
        $msg = new AMQPMessage();
        $channel->basic_publish($msg, 'tutorial_five', "$facility.$severity");

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response("$severity message from $facility has been sent.");
    }

    /**
     * https://www.rabbitmq.com/tutorials/tutorial-six-php.html
     * The receiver is @see \App\Command\TutorialSixReceiverCommand
     *
     * @Route("/six/{n}", name="tutorial_six", requirements={"n"="\d+"})
     *
     * @param string $n
     *
     * @return Response
     */
    public function six(string $n)
    {
        // Create a connection to the server
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

        // Create a channel
        $channel = $connection->channel();

        // Declare a queue to post the client's tasks to
        $channel->queue_declare('tutorial_six', false, false, false, false);

        // Create callback queue
        list($callbackQueue,) = $channel->queue_declare('');

        // Callback function that retrieves a result
        $fib = null;
        $callback = function(AMQPMessage $res) use (&$fib) {
            $body = json_decode($res->getBody(), true);
            $fib = $body['fib'] ?? 'error';
        };

        // Start consuming responses
        $channel->basic_consume($callbackQueue, '', false, true, false, false, $callback);

        // Create and publish message
        $req = new AMQPMessage($n, [
            'reply_to' => $callbackQueue,
            'correlation_id' => microtime(),
        ]);
        $channel->basic_publish($req, '', 'tutorial_six');

        // Wait for response
        if (!$fib) {
            $channel->wait();
        }

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        return new Response("Fibonacci number of $n is $fib");
    }
}
