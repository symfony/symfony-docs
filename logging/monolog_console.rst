.. index::
   single: Logging; Console messages

How to Configure Monolog to Display Console Messages
====================================================

It is possible to use the console to print messages for certain
:ref:`verbosity levels <verbosity-levels>` using the
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` instance that
is passed when a command gets executed.

.. seealso::
    Alternatively, you can use the
    :doc:`standalone PSR-3 logger </components/console/logger>` provided with
    the console component.

When a lot of logging has to happen, it's cumbersome to print information
depending on the verbosity settings (``-v``, ``-vv``, ``-vvv``) because the
calls need to be wrapped in conditions. The code quickly gets verbose or dirty.
For example::

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln('Some info');
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Some more info');
        }
    }

Instead of using these semantic methods to test for each of the verbosity
levels, the `MonologBridge`_ provides a `ConsoleHandler`_ that listens to
console events and writes log messages to the console output depending on the
current log level and the console verbosity.

The example above could then be rewritten as::

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // assuming the Command extends ContainerAwareCommand...
        $logger = $this->getContainer()->get('logger');
        $logger->debug('Some info');

        $logger->notice('Some more info');
    }

Depending on the verbosity level that the command is run in and the user's
configuration (see below), these messages may or may not be displayed to
the console. If they are displayed, they are timestamped and colored appropriately.
Additionally, error logs are written to the error output (php://stderr).
There is no need to conditionally handle the verbosity settings anymore.

The Monolog console handler is enabled in the Monolog configuration. This is
the default in Symfony Standard Edition 2.4 too.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                console:
                    type: console

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:monolog="http://symfony.com/schema/dic/monolog">

            <monolog:config>
                <monolog:handler name="console" type="console" />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'console' => array(
                   'type' => 'console',
                ),
            ),
        ));

With the ``verbosity_levels`` option you can adapt the mapping between
verbosity and log level. In the given example it will also show notices in
normal verbosity mode (instead of warnings only). Additionally, it will only
use messages logged with the custom ``my_channel`` channel and it changes the
display style via a custom formatter (see the
:doc:`MonologBundle reference </reference/configuration/monolog>` for more
information):

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                console:
                    type:   console
                    verbosity_levels:
                        VERBOSITY_NORMAL: NOTICE
                    channels: my_channel
                    formatter: my_formatter

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:monolog="http://symfony.com/schema/dic/monolog">

            <monolog:config>
                <monolog:handler name="console" type="console" formatter="my_formatter">
                    <monolog:verbosity-level verbosity-normal="NOTICE" />
                    <monolog:channel>my_channel</monolog:channel>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'console' => array(
                    'type' => 'console',
                    'verbosity_levels' => array(
                        'VERBOSITY_NORMAL' => 'NOTICE',
                    ),
                    'channels' => 'my_channel',
                    'formatter' => 'my_formatter',
                ),
            ),
        ));

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            my_formatter:
                class: Symfony\Bridge\Monolog\Formatter\ConsoleFormatter
                arguments:
                    - "[%%datetime%%] %%start_tag%%%%message%%%%end_tag%% (%%level_name%%) %%context%% %%extra%%\n"

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

             <services>
                <service id="my_formatter" class="Symfony\Bridge\Monolog\Formatter\ConsoleFormatter">
                    <argument>[%%datetime%%] %%start_tag%%%%message%%%%end_tag%% (%%level_name%%) %%context%% %%extra%%\n</argument>
                </service>
             </services>

        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('my_formatter', 'Symfony\Bridge\Monolog\Formatter\ConsoleFormatter')
            ->addArgument('[%%datetime%%] %%start_tag%%%%message%%%%end_tag%% (%%level_name%%) %%context%% %%extra%%\n')
        ;

.. _ConsoleHandler: https://github.com/symfony/MonologBridge/blob/master/Handler/ConsoleHandler.php
.. _MonologBridge: https://github.com/symfony/MonologBridge
