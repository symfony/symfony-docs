.. index::
   single: Console; Generating URLs

How to generate URLs with a custom host in Console Commands
===========================================================

The command line context does not know about your VirtualHost or domain name,
therefore if you generate absolute URLs within a Console Command you generally
end up with something like ``http://localhost/foo/bar`` which is not very
useful.

There are two ways of configuring the request context, at the application level
and per Command.

Configuring the Request Context globally
----------------------------------------

To configure the Request Context - which is used by the URL Generator - you can
redefine the parameters it uses as default value to change the default host and
scheme. Note that this does not impact URL generated via normal web requests,
since those will override the defaults.

.. configuration-block::

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            router.request_context.host: example.org
            router.request_context.scheme: https

    .. code-block:: xml

        <!-- app/config/parameters.xml -->

        <?xml version="1.0" encoding="UTF-8"?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <parameters>
                <parameter key="router.request_context.host">example.org</parameter>
                <parameter key="router.request_context.scheme">https</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // app/config/config_test.php
        $container->setParameter('router.request_context.host', 'example.org');
        $container->setParameter('router.request_context.scheme', 'https');

Configuring the Request Context per Command
-------------------------------------------

To change it only in one command you can simply fetch the Request Context
service and override its settings:

.. code-block:: php

    // src/Acme/DemoBundle/Command/DemoCommand.php
    class DemoCommand extends ContainerAwareCommand
    {
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $context = $this->getContainer()->get('router')->getContext();
            $context->setHost('example.com');
            $context->setScheme('https');

            // your code here
        }
    }

