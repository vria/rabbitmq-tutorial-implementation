RabbitMQ tutorial implementation
================================

This is a RabbitMQ tutorial implementation with *Symfony* and *Docker*.

1. **Hello World!**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::one()](src/Controller/TutorialController.php)
   
   Receiver: [TutorialOneReceiverCommand](src/Command/TutorialOneReceiverCommand.php)
   
2. **Work queues**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::two()](src/Controller/TutorialController.php)
      
   Receiver: [TutorialTwoReceiverCommand](src/Command/TutorialTwoReceiverCommand.php)

3. **Publish/Subscribe**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::three()](src/Controller/TutorialController.php)
         
   Receiver: [TutorialThreeReceiverCommand](src/Command/TutorialThreeReceiverCommand.php)

4. **Routing**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::four()](src/Controller/TutorialController.php)
            
   Receiver: [TutorialFourReceiverCommand](src/Command/TutorialFourReceiverCommand.php)

5. **Topics**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::five()](src/Controller/TutorialController.php)
               
   Receiver: [TutorialFiveReceiverCommand](src/Command/TutorialFiveReceiverCommand.php)

6. **RPC**

   Tutorial: [https://www.rabbitmq.com/tutorials/tutorial-one-php.html](https://www.rabbitmq.com/tutorials/tutorial-one-php.html)
   
   Sender: [TutorialController::six()](src/Controller/TutorialController.php)
                  
   Receiver: [TutorialSixReceiverCommand](src/Command/TutorialSixReceiverCommand.php)

## Run in docker

Launch containers:
```bash
cd docker
docker-compose up -d
```

Connect to php container in order to launch receivers and other symfony commands:
```php
docker exec -it docker_php_1 bash
```

Don't forget to install dependencies with `composer install`.

Application URL is [http://amqp.vm](http://amqp.vm) and can be changed in
[nginx configuration](docker/nginx/symfony.conf).

RabbitMQ management tool is available on
<a href="http://amqp.vm:15672">http://amqp.vm:15672</a>. 
