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

        public function processRecord(array $record)
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
                autowire: true
                tags:
                    - { name: monolog.processor, method: processRecord }

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

                <service id="App\Logger\SessionRequestProcessor" autowire="true">
                    <tag name="monolog.processor" method="processRecord" />
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
            ->autowire(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('method' => 'processRecord'));

Finally, set the formatter to be used on whatever handler you want:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
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

            <monolog:config>
                <monolog:handler
                    name="main"
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                    formatter="app.logger.session_request_processor"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    'type'      => 'stream',
                    'path'      => '%kernel.logs_dir%/%kernel.environment%.log',
                    'level'     => 'debug',
                    'formatter' => 'app.logger.session_request_processor',
                ),
            ),
        ));

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
            App\Logger\SessionRequestProcessor:
                autowire: true
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
                <service id="App\Logger\SessionRequestProcessor" autowire="true">
                    <tag name="monolog.processor" method="processRecord" handler="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php

        // ...
        $container
            ->autowire(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('method' => 'processRecord', 'handler' => 'main'));

Registering Processors per Channel
----------------------------------

You can register a processor per channel using the ``channel`` option of
the ``monolog.processor`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            App\Logger\SessionRequestProcessor:
                autowire: true
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
                <service id="App\Logger\SessionRequestProcessor" autowire="true">
                    <tag name="monolog.processor" method="processRecord" channel="main" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php

        // ...
        $container
            ->autowire(SessionRequestProcessor::class)
            ->addTag('monolog.processor', array('method' => 'processRecord', 'channel' => 'main'));
