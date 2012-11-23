.. index::
   single: Console; Generating URLs

How to generate URLs with a custom Host in Console Commands
===========================================================

Unfortunately, the command line context does not know about your VirtualHost
or domain name. This means that if if you generate absolute URLs within a
Console Command you'll probably end up with something like ``http://localhost/foo/bar``
which is not very useful.

To fix this, you need to configure the "request context", which is a fancy
way of saying that you need to configure your environment so that it knows
what URL it should use when generating URLs.

There are two ways of configuring the request context: at the application level
and per Command.

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

