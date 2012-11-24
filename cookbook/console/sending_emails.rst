.. index::
   single: Console; Sending emails
   single: Console; Generating URLs

How to generate URLs and send Emails from the Console
=====================================================

Unfortunately, the command line context does not know about your VirtualHost
or domain name. This means that if if you generate absolute URLs within a
Console Command you'll probably end up with something like ``http://localhost/foo/bar``
which is not very useful.

To fix this, you need to configure the "request context", which is a fancy
way of saying that you need to configure your environment so that it knows
what URL it should use when generating URLs.

There are two ways of configuring the request context: at the application level
(only available in Symfony 2.1+) and per Command.

Configuring the Request Context per Command
-------------------------------------------

To change it only in one command you can simply fetch the Request Context
service and override its settings::

   // src/Acme/DemoBundle/Command/DemoCommand.php

   // ...
   class DemoCommand extends ContainerAwareCommand
   {
       protected function execute(InputInterface $input, OutputInterface $output)
       {
           $context = $this->getContainer()->get('router')->getContext();
           $context->setHost('example.com');
           $context->setScheme('https');

           // ... your code here
       }
   }

Using Memory Spooling
---------------------

Sending emails in a console command works the same way as described in the 
:doc:`/cookbook/email/email` cookbook except if memory spooling is used.

When using memory spooling (see the :doc:`/cookbook/email/spool` cookbook for more
information), you must be aware that because of how symfony handles console 
commands, emails are not sent automatically. You must take care of flushing 
the queue yourself. Use the following code to send emails inside your 
console command::

    $container = $this->getContainer();
    $mailer = $container->get('mailer');
    $spool = $mailer->getTransport()->getSpool();
    $transport = $container->get('swiftmailer.transport.real');

    $spool->flushQueue($transport);
    
Another option is to create an environment which is only used by console
commands and uses a different spooling method. 
    
.. note::

    Taking care of the spooling is only needed when memory spooling is used. 
    If you are using file spooling (or no spooling at all), there is no need
    to flush the queue manually within the command.