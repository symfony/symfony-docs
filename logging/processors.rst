How to Add extra Data to Log Messages via a Processor
=====================================================

Monolog allows you to process the record before logging it to add some
extra data. A processor can be applied for the whole handler stack or
only for a specific handler.

A processor is simply a callable receiving the record as its first argument.
Processors are configured using the ``monolog.processor`` DIC tag. See the
:ref:`reference about it <dic_tags-monolog-processor>`.

Adding a Session/Request Token
------------------------------

Sometimes it is hard to tell which entries in the log belong to which session
and/or request. The following example will add a unique token for each request
using a processor.

.. code-block:: php

    namespace AppBundle;

    use Symfony\Component\HttpFoundation\Session\Session;

    class SessionRequestProcessor
    {
        private $session;
        private $token;

        public function __construct(Session $session)
        {
            $this->session = $session;
        }

        public function processRecord(array $record)
        {
            if (null === $this->token) {
                try {
                    $this->token = substr($this->session->getId(), 0, 8);
                } catch (\RuntimeException $e) {
                    $this->token = '????????';
                }
                $this->token .= '-' . substr(uniqid(), -8);
            }
            $record['extra']['token'] = $this->token;

            return $record;
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.formatter.session_request:
                class: Monolog\Formatter\LineFormatter
                arguments:
                    - "[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"

            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord }

        monolog:
            handlers:
                main:
                    type: stream
                    path: '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                    formatter: monolog.formatter.session_request

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="monolog.formatter.session_request"
                    class="Monolog\Formatter\LineFormatter">

                    <argument>[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%&#xA;</argument>
                </service>

                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" />
                </service>
            </services>

            <monolog:config>
                <monolog:handler
                    name="main"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    formatter="monolog.formatter.session_request"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'monolog.formatter.session_request',
                'Monolog\Formatter\LineFormatter'
            )
            ->addArgument('[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n');

        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord'));

        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    'type'      => 'stream',
                    'path'      => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level'     => 'debug',
                    'formatter' => 'monolog.formatter.session_request',
                ),
            ),
        ));

.. note::

    If you use several handlers, you can also register a processor at the
    handler level or at the channel level instead of registering it globally
    (see the following sections).

Registering Processors per Handler
----------------------------------

You can register a processor per handler using the ``handler`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord, handler: main }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" handler="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord', 'handler' => 'main'));

Registering Processors per Channel
----------------------------------

You can register a processor per channel using the ``channel`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            monolog.processor.session_request:
                class: AppBundle\SessionRequestProcessor
                arguments:  ['@session']
                tags:
                    - { name: monolog.processor, method: processRecord, channel: main }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="monolog.processor.session_request"
                    class="AppBundle\SessionRequestProcessor">

                    <argument type="service" id="session" />
                    <tag name="monolog.processor" method="processRecord" channel="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'monolog.processor.session_request',
                'AppBundle\SessionRequestProcessor'
            )
            ->addArgument(new Reference('session'))
            ->addTag('monolog.processor', array('method' => 'processRecord', 'channel' => 'main'));
