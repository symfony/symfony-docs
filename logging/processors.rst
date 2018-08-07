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
using a processor::

    namespace App\Logger;

    use Symfony\Component\HttpFoundation\Session\SessionInterface;

    class SessionRequestProcessor
    {
        private $session;
        private $sessionId;

        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        public function __invoke(array $record)
        {
            if (!$this->session->isStarted()) {
                return $record;
            }

            if (!$this->sessionId) {
                $this->sessionId = substr($this->session->getId(), 0, 8) ?: '????????';
            }

            $record['extra']['token'] = $this->sessionId.'-'.substr(uniqid('', true), -8);

            return $record;
        }
    }

Next, register your class as a service, as well as a formatter that uses the extra
information:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            monolog.formatter.session_request:
                class: Monolog\Formatter\LineFormatter
                arguments:
                    - "[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"

            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor }

    .. code-block:: xml

        <!-- config/services.xml -->
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

                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Logger\SessionRequestProcessor;
        use Monolog\Formatter\LineFormatter;

        $container
            ->register('monolog.formatter.session_request', LineFormatter::class)
            ->addArgument('[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n');

        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('method' => 'processRecord'));

Finally, set the formatter to be used on whatever handler you want:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type: stream
                    path: '%kernel.logs_dir%/%kernel.environment%.log'
                    level: debug
                    formatter: monolog.formatter.session_request

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

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

        // config/packages/prod/monolog.php
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

If you use several handlers, you can also register a processor at the
handler level or at the channel level instead of registering it globally
(see the following sections).

.. tip::

    .. versionadded:: 4.2
        The autoconfiguration of Monolog processors was introduced in Symfony 4.2.

    If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
    processors implementing :class:`Symfony\\Bridge\\Monolog\\Processor\\ProcessorInterface`
    are automatically registered as services and tagged with ``monolog.processor``,
    so you can use them without adding any configuration. The same applies to the
    built-in :class:`Symfony\\Bridge\\Monolog\\Processor\\TokenProcessor` and
    :class:`Symfony\\Bridge\\Monolog\\Processor\\WebProcessor` processors, which
    can be enabled as follows:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # Adds the current security token to log entries
                Symfony\Bridge\Monolog\Processor\TokenProcessor: ~
                # Adds the real client IP to log entries
                Symfony\Bridge\Monolog\Processor\WebProcessor: ~

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- Adds the current security token to log entries -->
                    <service id="Symfony\Bridge\Monolog\Processor\TokenProcessor" />
                    <!-- Adds the real client IP to log entries -->
                    <service id="Symfony\Bridge\Monolog\Processor\WebProcessor" />
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use Symfony\Bridge\Monolog\Processor\TokenProcessor;
            use Symfony\Bridge\Monolog\Processor\WebProcessor;

            // Adds the current security token to log entries
            $container->register(TokenProcessor::class);
            // Adds the real client IP to log entries
            $container->register(WebProcessor::class);

Registering Processors per Handler
----------------------------------

You can register a processor per handler using the ``handler`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor, handler: main }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" handler="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('handler' => 'main'));

Registering Processors per Channel
----------------------------------

You can register a processor per channel using the ``channel`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Logger\SessionRequestProcessor:
                tags:
                    - { name: monolog.processor, channel: main }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" channel="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('channel' => 'main'));
