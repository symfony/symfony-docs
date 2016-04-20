.. index::
   single: Console; Generating URLs

How to Generate URLs from the Console
=====================================

Unfortunately, the command line context does not know about your VirtualHost
or domain name. This means that if you generate absolute URLs within a
console command you'll probably end up with something like ``http://localhost/foo/bar``
which is not very useful.

To fix this, you need to configure the "request context", which is a fancy
way of saying that you need to configure your environment so that it knows
what URL it should use when generating URLs.

There are two ways of configuring the request context: at the application level
and per Command.

Configuring the Request Context globally
----------------------------------------

.. versionadded:: 2.2
    The ``base_url`` parameter was introduced in Symfony 2.2.

To configure the Request Context - which is used by the URL Generator - you can
redefine the parameters it uses as default values to change the default host
(localhost) and scheme (http). Starting with Symfony 2.2 you can also configure
the base path if Symfony is not running in the root directory.

Note that this does not impact URLs generated via normal web requests, since those
will override the defaults.

.. configuration-block::

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            router.request_context.host: example.org
            router.request_context.scheme: https
            router.request_context.base_url: my/path

    .. code-block:: xml

        <!-- app/config/parameters.xml -->
        <?xml version="1.0" encoding="UTF-8"?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <parameters>
                <parameter key="router.request_context.host">example.org</parameter>
                <parameter key="router.request_context.scheme">https</parameter>
                <parameter key="router.request_context.base_url">my/path</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // app/config/config_test.php
        $container->setParameter('router.request_context.host', 'example.org');
        $container->setParameter('router.request_context.scheme', 'https');
        $container->setParameter('router.request_context.base_url', 'my/path');

Configuring the Request Context per Command
-------------------------------------------

To change it only in one command you can simply fetch the Request Context
from the ``router`` service and override its settings::

   // src/AppBundle/Command/DemoCommand.php

   // ...
   class DemoCommand extends ContainerAwareCommand
   {
       protected function execute(InputInterface $input, OutputInterface $output)
       {
           $context = $this->getContainer()->get('router')->getContext();
           $context->setHost('example.com');
           $context->setScheme('https');
           $context->setBaseUrl('my/path');

           // ... your code here
       }
   }
