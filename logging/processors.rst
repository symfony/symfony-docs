How to Add extra Data to Log Messages via a Processor
=====================================================

`Monolog`_ allows you to process every record before logging it by adding some
extra data. This is the role of a processor, which can be applied for the whole
handler stack or only for a specific handler or channel.

A processor is a callable receiving the record as its first argument.
Processors are configured using the ``monolog.processor`` DIC tag. See the
:ref:`reference about it <dic_tags-monolog-processor>`.

Adding a Session/Request Token
------------------------------

Sometimes it is hard to tell which entries in the log belong to which session
and/or request. The following example will add a unique token for each request
using a processor::

    // src/Logger/SessionRequestProcessor.php
    namespace App\Logger;

    use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
    use Symfony\Component\HttpFoundation\RequestStack;

    class SessionRequestProcessor
    {
        private $requestStack;

        public function __construct(RequestStack $requestStack)
        {
            $this->requestStack = $requestStack;
        }

        // this method is called for each log record; optimize it to not hurt performance
        public function __invoke(array $record)
        {
            try {
                $session = $requestStack->getSession();
            } catch (SessionNotFoundException $e) {
                return;
            }
            if (!$session->isStarted()) {
                return $record;
            }

            $sessionId = substr($session->getId(), 0, 8) ?: '????????';

            $record['extra']['token'] = $sessionId.'-'.substr(uniqid('', true), -8);

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="monolog.formatter.session_request"
                    class="Monolog\Formatter\LineFormatter">

                    <argument>[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%&#xA;</argument>
                </service>

                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor"/>
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
            ->addTag('monolog.processor');

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

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
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog) {
            $monolog->handler('main')
                ->type('stream')
                ->path('%kernel.logs_dir%/%kernel.environment%.log')
                ->level('debug')
                ->formatter('monolog.formatter.session_request')
            ;
        };

If you use several handlers, you can also register a processor at the
handler level or at the channel level instead of registering it globally
(see the following sections).

Symfony's MonologBridge provides processors that can be registered inside your application.

:class:`Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor`
    Adds additional information useful for debugging like a timestamp or an
    error message to the record.

:class:`Symfony\\Bridge\\Monolog\\Processor\\TokenProcessor`
    Adds information from the current user's token to the record namely
    username, roles and whether the user is authenticated.

:class:`Symfony\\Bridge\\Monolog\\Processor\\SwitchUserTokenProcessor`
    Adds information about the user who is impersonating the logged in user,
    namely username, roles and whether the user is authenticated.

    .. versionadded:: 5.2

        The ``SwitchUserTokenProcessor`` was introduced in Symfony 5.2.

:class:`Symfony\\Bridge\\Monolog\\Processor\\WebProcessor`
    Overrides data from the request using the data inside Symfony's request
    object.

:class:`Symfony\\Bridge\\Monolog\\Processor\\RouteProcessor`
    Adds information about current route (controller, action, route parameters).

:class:`Symfony\\Bridge\\Monolog\\Processor\\ConsoleCommandProcessor`
    Adds information about current console command.

.. seealso::

    Check out the `built-in Monolog processors`_ to learn more about how to
    create these processors.

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" handler="main"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', ['handler' => 'main']);

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="App\Logger\SessionRequestProcessor">
                    <tag name="monolog.processor" channel="main"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php

        // ...
        $container
            ->register(SessionRequestProcessor::class)
            ->addTag('monolog.processor', ['channel' => 'main']);

.. _`Monolog`: https://github.com/Seldaek/monolog
.. _`built-in Monolog processors`: https://github.com/Seldaek/monolog/tree/master/src/Monolog/Processor
