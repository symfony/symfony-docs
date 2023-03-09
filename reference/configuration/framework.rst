.. index::
    single: Configuration reference; Framework

.. _framework-bundle-configuration:

Framework Configuration Reference (FrameworkBundle)
===================================================

The FrameworkBundle defines the main framework configuration, from sessions and
translations to forms, validation, routing and more. All these options are
configured under the ``framework`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference framework

    # displays the actual config values used by your application
    $ php bin/console debug:config framework

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/symfony``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/symfony/symfony-1.0.xsd``

Configuration
-------------

secret
~~~~~~

**type**: ``string`` **required**

This is a string that should be unique to your application and it's commonly
used to add more entropy to security related operations. Its value should
be a series of characters, numbers and symbols chosen randomly and the
recommended length is around 32 characters.

In practice, Symfony uses this value for encrypting the cookies used
in the :doc:`remember me functionality </security/remember_me>` and for
creating signed URIs when using :ref:`ESI (Edge Side Includes) <edge-side-includes>`.
That's why you should treat this value as if it were a sensitive credential and
**never make it public**.

This option becomes the service container parameter named ``kernel.secret``,
which you can use whenever the application needs an immutable random string
to add more entropy.

As with any other security-related parameter, it is a good practice to change
this value from time to time. However, keep in mind that changing this value
will invalidate all signed URIs and Remember Me cookies. That's why, after
changing this value, you should regenerate the application cache and log
out all the application users.

handle_all_throwables
~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, the Symfony kernel will catch all ``\Throwable`` exceptions
thrown by the application and will turn them into HTTP reponses.

Starting from Symfony 7.0, the default value of this option will be ``true``.

.. versionadded:: 6.2

    The ``handle_all_throwables`` option was introduced in Symfony 6.2.

.. _configuration-framework-http_cache:

http_cache
~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``false``

debug
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

If true, exceptions are thrown when things go wrong. Otherwise, the cache will
try to carry on and deliver a meaningful response.

trace_level
...........

**type**: ``string`` **possible values**: ``'none'``, ``'short'`` or ``'full'``

For 'short', a concise trace of the main request will be added as an HTTP header.
'full' will add traces for all requests (including ESI subrequests).
(default: 'full' if in debug; 'none' otherwise)

trace_header
............

**type**: ``string``

Header name to use for traces. (default: X-Symfony-Cache)

default_ttl
...........

**type**: ``integer``

The number of seconds that a cache entry should be considered fresh when no
explicit freshness information is provided in a response. Explicit
Cache-Control or Expires headers override this value. (default: 0)

private_headers
...............

**type**: ``array``

Set of request headers that trigger "private" cache-control behavior on responses
that don't explicitly state whether the response is public or private via a
Cache-Control directive. (default: Authorization and Cookie)

skip_response_headers
.....................

**type**: ``array`` **default**: ``Set-Cookie``

Set of response headers that will never be cached even when the response is cacheable
and public.

.. versionadded:: 6.3

    The ``skip_response_headers`` option was introduced in Symfony 6.3.

allow_reload
............

**type**: ``string``

Specifies whether the client can force a cache reload by including a
Cache-Control "no-cache" directive in the request. Set it to ``true``
for compliance with RFC 2616. (default: false)

allow_revalidate
................

**type**: ``string``

Specifies whether the client can force a cache revalidate by including a
Cache-Control "max-age=0" directive in the request. Set it to ``true``
for compliance with RFC 2616. (default: false)

stale_while_revalidate
......................

**type**: ``integer``

Specifies the default number of seconds (the granularity is the second as the
Response TTL precision is a second) during which the cache can immediately return
a stale response while it revalidates it in the background (default: 2).
This setting is overridden by the stale-while-revalidate HTTP Cache-Control
extension (see RFC 5861).

stale_if_error
..............

**type**: ``integer``

Specifies the default number of seconds (the granularity is the second) during
which the cache can serve a stale response when an error is encountered
(default: 60). This setting is overridden by the stale-if-error HTTP
Cache-Control extension (see RFC 5861).

terminate_on_cache_hit
......................

**type**: ``boolean`` **default**: ``true``

If ``true``, the :ref:`kernel.terminate <component-http-kernel-kernel-terminate>`
event is dispatched even when the cache is hit.

Unless your application needs to process events on cache hits, it's recommended
to set this to ``false`` to improve performance, because it avoids having to
bootstrap the Symfony framework on a cache hit.

.. versionadded:: 6.2

    The ``terminate_on_cache_hit`` option was introduced in Symfony 6.2.

 .. _configuration-framework-http_method_override:

http_method_override
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

This determines whether the ``_method`` request parameter is used as the
intended HTTP method on POST requests. If enabled, the
:method:`Request::enableHttpMethodParameterOverride <Symfony\\Component\\HttpFoundation\\Request::enableHttpMethodParameterOverride>`
method gets called automatically. It becomes the service container parameter
named ``kernel.http_method_override``.

.. seealso::

    :ref:`Changing the Action and HTTP Method <forms-change-action-method>` of
    Symfony forms.

.. caution::

    If you're using the :ref:`HttpCache Reverse Proxy <symfony2-reverse-proxy>`
    with this option, the kernel will ignore the ``_method`` parameter,
    which could lead to errors.

    To fix this, invoke the ``enableHttpMethodParameterOverride()`` method
    before creating the ``Request`` object::

        // public/index.php

        // ...
        $kernel = new CacheKernel($kernel);

        Request::enableHttpMethodParameterOverride(); // <-- add this line
        $request = Request::createFromGlobals();
        // ...


 .. _configuration-framework-http_method_override:

trust_x_sendfile_type_header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 6.1

    The ``trust_x_sendfile_type_header`` option was introduced in Symfony 6.1.

``X-Sendfile`` is a special HTTP header that tells web servers to replace the
response contents by the file that is defined in that header. This improves
performance because files are no longer served by your application but directly
by the web server.

This configuration option determines whether to trust ``x-sendfile`` header for
BinaryFileResponse. If enabled, Symfony calls the
:method:`BinaryFileResponse::trustXSendfileTypeHeader <Symfony\\Component\\HttpFoundation\\BinaryFileResponse::trustXSendfileTypeHeader>`
method automatically. It becomes the service container parameter named
``kernel.trust_x_sendfile_type_header``.

.. _reference-framework-trusted-headers:

trusted_headers
~~~~~~~~~~~~~~~

The ``trusted_headers`` option is needed to configure which client information
should be trusted (e.g. their host) when running Symfony behind a load balancer
or a reverse proxy. See :doc:`/deployment/proxies`.

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

The ``trusted_proxies`` option is needed to get precise information about the
client (e.g. their IP address) when running Symfony behind a load balancer or a
reverse proxy. See :doc:`/deployment/proxies`.

ide
~~~

**type**: ``string`` **default**: ``null``

Symfony turns file paths seen in variable dumps and exception messages into
links that open those files right inside your browser. If you prefer to open
those files in your favorite IDE or text editor, set this option to any of the
following values: ``phpstorm``, ``sublime``, ``textmate``, ``macvim``, ``emacs``,
``atom`` and ``vscode``.

.. note::

    The ``phpstorm`` option is supported natively by PhpStorm on MacOS,
    Windows requires `PhpStormProtocol`_ and Linux requires `phpstorm-url-handler`_.

If you use another editor, the expected configuration value is a URL template
that contains an ``%f`` placeholder where the file path is expected and ``%l``
placeholder for the line number (percentage signs (``%``) must be escaped by
doubling them to prevent Symfony from interpreting them as container parameters).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            ide: 'myide://open?url=file://%%f&line=%%l'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ide="myide://open?url=file://%%f&line=%%l"/>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->ide('myide://open?url=file://%%f&line=%%l');
        };

Since every developer uses a different IDE, the recommended way to enable this
feature is to configure it on a system level. First, you can define this option
in the ``SYMFONY_IDE`` environment variable, which Symfony reads automatically
when ``framework.ide`` config is not set.

.. versionadded:: 6.1

    ``SYMFONY_IDE`` environment variable support was introduced in Symfony 6.1.

Another alternative is to set the ``xdebug.file_link_format`` option in your
``php.ini`` configuration file. The format to use is the same as for the
``framework.ide`` option, but without the need to escape the percent signs
(``%``) by doubling them:

.. code-block:: ini

    // example for PhpStorm
    xdebug.file_link_format="phpstorm://open?file=%f&line=%l"

    // example for Sublime
    xdebug.file_link_format="subl://open?url=file://%f&line=%l"

.. note::

    If both ``framework.ide`` and ``xdebug.file_link_format`` are defined,
    Symfony uses the value of the ``xdebug.file_link_format`` option.

.. tip::

    Setting the ``xdebug.file_link_format`` ini option works even if the Xdebug
    extension is not enabled.

.. tip::

    When running your app in a container or in a virtual machine, you can tell
    Symfony to map files from the guest to the host by changing their prefix.
    This map should be specified at the end of the URL template, using ``&`` and
    ``>`` as guest-to-host separators:

    .. code-block:: text

        // /path/to/guest/.../file will be opened
        // as /path/to/host/.../file on the host
        // and /var/www/app/ as /projects/my_project/ also
        'myide://%%f:%%l&/path/to/guest/>/path/to/host/&/var/www/app/>/projects/my_project/&...'

        // example for PhpStorm
        'phpstorm://open?file=%%f&line=%%l&/var/www/app/>/projects/my_project/'

.. _reference-framework-test:

test
~~~~

**type**: ``boolean``

If this configuration setting is present (and not ``false``), then the services
related to testing your application (e.g. ``test.client``) are loaded. This
setting should be present in your ``test`` environment (usually via
``config/packages/test/framework.yaml``).

.. seealso::

    For more information, see :doc:`/testing`.

.. _config-framework-default_locale:

default_locale
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``en``

The default locale is used if no ``_locale`` routing parameter has been
set. It is available with the
:method:`Request::getDefaultLocale <Symfony\\Component\\HttpFoundation\\Request::getDefaultLocale>`
method.

.. seealso::

    You can read more information about the default locale in
    :ref:`translation-default-locale`.

.. _reference-translator-enabled-locales:
.. _reference-enabled-locales:

enabled_locales
...............

**type**: ``array`` **default**: ``[]`` (empty array = enable all locales)

Symfony applications generate by default the translation files for validation
and security messages in all locales. If your application only uses some
locales, use this option to restrict the files generated by Symfony and improve
performance a bit:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            enabled_locales: ['en', 'es']

    .. code-block:: xml

        <!-- config/packages/translation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <enabled-locale>en</enabled-locale>
                <enabled-locale>es</enabled-locale>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->enabledLocales(['en', 'es']);
        };

If some user makes requests with a locale not included in this option, the
application won't display any error because Symfony will display contents using
the fallback locale.

set_content_language_from_locale
................................

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the response will have a ``Content-Language``
HTTP header set with the ``Request`` locale.

set_locale_from_accept_language
...............................

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the ``Request`` locale will automatically be
set to the value of the ``Accept-Language`` HTTP header.

When the ``_locale`` request attribute is passed, the ``Accept-Language`` header
is ignored.

disallow_search_engine_index
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

If ``true``, Symfony adds a ``X-Robots-Tag: noindex`` HTTP tag to all responses
(unless your own app adds that header, in which case it's not modified). This
`X-Robots-Tag HTTP header`_ tells search engines to not index your web site.
This option is a protection measure in case you accidentally publish your site
in debug mode.

trusted_hosts
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``[]``

A lot of different attacks have been discovered relying on inconsistencies
in handling the ``Host`` header by various software (web servers, reverse
proxies, web frameworks, etc.). Basically, every time the framework is
generating an absolute URL (when sending an email to reset a password for
instance), the host might have been manipulated by an attacker.

.. seealso::

    You can read "`HTTP Host header attacks`_" for more information about
    these kinds of attacks.

The Symfony :method:`Request::getHost() <Symfony\\Component\\HttpFoundation\\Request::getHost>`
method might be vulnerable to some of these attacks because it depends on
the configuration of your web server. One simple solution to avoid these
attacks is to configure a list of hosts that your Symfony application can respond
to. That's the purpose of this ``trusted_hosts`` option. If the incoming
request's hostname doesn't match one of the regular expressions in this list,
the application won't respond and the user will receive a 400 response.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            trusted_hosts:  ['^example\.com$', '^example\.org$']

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:trusted-host>^example\.com$</framework:trusted-host>
                <framework:trusted-host>^example\.org$</framework:trusted-host>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->trustedHosts(['^example\.com$', '^example\.org$']);
        };

Hosts can also be configured to respond to any subdomain, via
``^(.+\.)?example\.com$`` for instance.

In addition, you can also set the trusted hosts in the front controller
using the ``Request::setTrustedHosts()`` method::

    // public/index.php
    Request::setTrustedHosts(['^(.+\.)?example\.com$', '^(.+\.)?example\.org$']);

The default value for this option is an empty array, meaning that the application
can respond to any given host.

.. seealso::

    Read more about this in the `Security Advisory Blog post`_.

.. _reference-framework-form:

form
~~~~

.. _reference-form-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the form services or not in the service container. If
you don't use forms, setting this to ``false`` may increase your application's
performance because less services will be loaded into the container.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. note::

    This will automatically enable the `validation`_.

.. seealso::

    For more details, see :doc:`/forms`.

.. _reference-form-field-name:

field_name
..........

**type**: ``string`` **default**: ``_token``

This is the field name that you should give to the CSRF token field of your forms.

.. _reference-framework-csrf-protection:

csrf_protection
~~~~~~~~~~~~~~~

.. seealso::

    For more information about CSRF protection, see :doc:`/security/csrf`.

.. _reference-csrf_protection-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

This option can be used to disable CSRF protection on *all* forms. But you
can also :ref:`disable CSRF protection on individual forms <form-csrf-customization>`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            csrf_protection: true

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:csrf-protection enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;
        return static function (FrameworkConfig $framework) {
            $framework->csrfProtection()
                ->enabled(true)
            ;
        };

If you're using forms, but want to avoid starting your session (e.g. using
forms in an API-only website), ``csrf_protection`` will need to be set to
``false``.

.. _config-framework-error_controller:

error_controller
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``error_controller``

This is the controller that is called when an exception is thrown anywhere in
your application. The default controller
(:class:`Symfony\\Component\\HttpKernel\\Controller\\ErrorController`)
renders specific templates under different error conditions (see
:doc:`/controller/error_pages`).

esi
~~~

.. seealso::

    You can read more about Edge Side Includes (ESI) in :ref:`edge-side-includes`.

.. _reference-esi-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the edge side includes support in the framework.

You can also set ``esi`` to ``true`` to enable it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            esi: true

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:esi/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->esi()->enabled(true);
        };

fragments
~~~~~~~~~

.. seealso::

    Learn more about fragments in the
    :ref:`HTTP Cache article <http_cache-fragments>`.

.. _reference-fragments-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the fragment listener or not. The fragment listener is
used to render ESI fragments independently of the rest of the page.

This setting is automatically set to ``true`` when one of the child settings
is configured.

hinclude_default_template
.........................

**type**: ``string`` **default**: ``null``

Sets the content shown during the loading of the fragment or when JavaScript
is disabled. This can be either a template name or the content itself.

.. seealso::

    See :ref:`templates-hinclude` for more information about hinclude.

.. _reference-fragments-path:

path
....

**type**: ``string`` **default**: ``'/_fragment'``

The path prefix for fragments. The fragment listener will only be executed
when the request starts with this path.

.. _reference-http-client:

http_client
~~~~~~~~~~~

When the HttpClient component is installed, an HTTP client is available
as a service named ``http_client`` or using the autowiring alias
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`.

.. _reference-http-client-default-options:

This service can be configured using ``framework.http_client.default_options``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                max_host_connections: 10
                default_options:
                    headers: { 'X-Powered-By': 'ACME App' }
                    max_redirects: 7

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:http-client max-host-connections="10">
                    <framework:default-options max-redirects="7">
                        <framework:header name="X-Powered-By">ACME App</framework:header>
                    </framework:default-options>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'http_client' => [
                'max_host_connections' => 10,
                'default_options' => [
                    'headers' => [
                        'X-Powered-By' => 'ACME App',
                    ],
                    'max_redirects' => 7,
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::create([
            'headers' => [
                'X-Powered-By' => 'ACME App',
            ],
            'max_redirects' => 7,
        ], 10);

.. _reference-http-client-scoped-clients:

Multiple pre-configured HTTP client services can be defined, each with its
service name defined as a key under ``scoped_clients``. Scoped clients inherit
the default options defined for the ``http_client`` service. You can override
these options and can define a few others:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                scoped_clients:
                    my_api.client:
                        auth_bearer: secret_bearer_token
                        # ...

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:http-client>
                    <framework:scoped-client name="my_api.client" auth-bearer="secret_bearer_token"/>
                </framework:http-client>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'http_client' => [
                'scoped_clients' => [
                    'my_api.client' => [
                        'auth_bearer' => 'secret_bearer_token',
                        // ...
                    ],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::createForBaseUri('https://...', [
            'auth_bearer' => 'secret_bearer_token',
            // ...
        ]);

Options defined for scoped clients apply only to URLs that match either their
`base_uri`_ or the `scope`_ option when it is defined. Non-matching URLs always
use default options.

Each scoped client also defines a corresponding named autowiring alias.
If you use for example
``Symfony\Contracts\HttpClient\HttpClientInterface $myApiClient``
as the type and name of an argument, autowiring will inject the ``my_api.client``
service into your autowired classes.

.. _reference-http-client-retry-failed:

By enabling the optional ``retry_failed`` configuration, the HTTP client service
will automatically retry failed HTTP requests.

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            # ...
            default_options:
                retry_failed:
                    # retry_strategy: app.custom_strategy
                    http_codes:
                        0: ['GET', 'HEAD']   # retry network errors if request method is GET or HEAD
                        429: true            # retry all responses with 429 status code
                        500: ['GET', 'HEAD']
                    max_retries: 2
                    delay: 1000
                    multiplier: 3
                    max_delay: 5000
                    jitter: 0.3

            scoped_clients:
                my_api.client:
                    # ...
                    retry_failed:
                        max_retries: 4

auth_basic
..........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header
used in HTTP Basic authentication. The value of this option must follow the
format ``username:password``.

auth_bearer
...........

**type**: ``string``

The token used to create the ``Authorization`` HTTP header used in HTTP Bearer
authentication (also called token authentication).

auth_ntlm
.........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header used
in the `Microsoft NTLM authentication protocol`_. The value of this option must
follow the format ``username:password``. This authentication mechanism requires
using the cURL-based transport.

.. _reference-http-client-base-uri:

base_uri
........

**type**: ``string``

URI that is merged into relative URIs, following the rules explained in the
`RFC 3986`_ standard. This is useful when all the requests you make share a
common prefix (e.g. ``https://api.github.com/``) so you can avoid adding it to
every request.

Here are some common examples of how ``base_uri`` merging works in practice:

==========================  ==================  =============================
``base_uri``                Relative URI        Actual Requested URI
==========================  ==================  =============================
http://example.org          /bar                http://example.org/bar
http://example.org/foo      /bar                http://example.org/bar
http://example.org/foo      bar                 http://example.org/bar
http://example.org/foo/     /bar                http://example.org/bar
http://example.org/foo/     bar                 http://example.org/foo/bar
http://example.org          http://symfony.com  http://symfony.com
http://example.org/?bar     bar                 http://example.org/bar
http://example.org/api/v4   /bar                http://example.org/bar
http://example.org/api/v4/  /bar                http://example.org/bar
http://example.org/api/v4   bar                 http://example.org/api/bar
http://example.org/api/v4/  bar                 http://example.org/api/v4/bar
==========================  ==================  =============================

bindto
......

**type**: ``string``

A network interface name, IP address, a host name or a UNIX socket to use as the
outgoing network interface.

buffer
......

**type**: ``boolean`` | ``Closure``

Buffering the response means that you can access its content multiple times
without performing the request again. Buffering is enabled by default when the
content type of the response is ``text/*``, ``application/json`` or ``application/xml``.

If this option is a boolean value, the response is buffered when the value is
``true``. If this option is a closure, the response is buffered when the
returned value is ``true`` (the closure receives as argument an array with the
response headers).

cafile
......

**type**: ``string``

The path of the certificate authority file that contains one or more
certificates used to verify the other servers' certificates.

capath
......

**type**: ``string``

The path to a directory that contains one or more certificate authority files.

ciphers
.......

**type**: ``string``

A list of the names of the ciphers allowed for the SSL/TLS connections. They
can be separated by colons, commas or spaces (e.g. ``'RC4-SHA:TLS13-AES-128-GCM-SHA256'``).

delay
.....

**type**: ``integer`` **default**: ``1000``

The initial delay in milliseconds used to compute the waiting time between retries.

.. _reference-http-client-retry-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the support for retry failed HTTP request or not.
This setting is automatically set to true when one of the child settings is configured.

extra
.....

**type**: ``array``

Arbitrary additional data to pass to the HTTP client for further use.
This can be particularly useful when :ref:`decorating an existing client <extensibility>`.

.. versionadded:: 6.3

    The ``extra`` option has been introduced in Symfony 6.3.

.. _http-headers:

headers
.......

**type**: ``array``

An associative array of the HTTP headers added before making the request. This
value must use the format ``['header-name' => 'value0, value1, ...']``.

http_codes
..........

**type**: ``array`` **default**: :method:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy::DEFAULT_RETRY_STATUS_CODES`

The list of HTTP status codes that triggers a retry of the request.

http_version
............

**type**: ``string`` | ``null`` **default**: ``null``

The HTTP version to use, typically ``'1.1'``  or ``'2.0'``. Leave it to ``null``
to let Symfony select the best version automatically.

jitter
......

**type**: ``float`` **default**: ``0.1`` (must be between 0.0 and 1.0)

This option adds some randomness to the delay. It's useful to avoid sending
multiple requests to the server at the exact same time. The randomness is
calculated as ``delay * jitter``. For example: if delay is ``1000ms`` and jitter
is ``0.2``, the actual delay will be a number between ``800`` and ``1200`` (1000 +/- 20%).

local_cert
..........

**type**: ``string``

The path to a file that contains the `PEM formatted`_ certificate used by the
HTTP client. This is often combined with the ``local_pk`` and ``passphrase``
options.

local_pk
........

**type**: ``string``

The path of a file that contains the `PEM formatted`_ private key of the
certificate defined in the ``local_cert`` option.

max_delay
.........

**type**: ``integer`` **default**: ``0``

The maximum amount of milliseconds initial to wait between retries.
Use ``0`` to not limit the duration.

max_duration
............

**type**: ``float`` **default**: 0

The maximum execution time, in seconds, that the request and the response are
allowed to take. A value lower than or equal to 0 means it is unlimited.

max_host_connections
....................

**type**: ``integer`` **default**: ``6``

Defines the maximum amount of simultaneously open connections to a single host
(considering a "host" the same as a "host name + port number" pair). This limit
also applies for proxy connections, where the proxy is considered to be the host
for which this limit is applied.

max_redirects
.............

**type**: ``integer`` **default**: ``20``

The maximum number of redirects to follow. Use ``0`` to not follow any
redirection.

max_retries
...........

**type**: ``integer`` **default**: ``3``

The maximum number of retries for failing requests. When the maximum is reached,
the client returns the last received response.

multiplier
..........

**type**: ``float`` **default**: ``2``

This value is multiplied to the delay each time a retry occurs, to distribute
retries in time instead of making all of them sequentially.

no_proxy
........

**type**: ``string`` | ``null`` **default**: ``null``

A comma separated list of hosts that do not require a proxy to be reached, even
if one is configured. Use the ``'*'`` wildcard to match all hosts and an empty
string to match none (disables the proxy).

passphrase
..........

**type**: ``string``

The passphrase used to encrypt the certificate stored in the file defined in the
``local_cert`` option.

peer_fingerprint
................

**type**: ``array``

When negotiating a TLS or SSL connection, the server sends a certificate
indicating its identity. A public key is extracted from this certificate and if
it does not exactly match any of the public keys provided in this option, the
connection is aborted before sending or receiving any data.

The value of this option is an associative array of ``algorithm => hash``
(e.g ``['pin-sha256' => '...']``).

proxy
.....

**type**: ``string`` | ``null``

The HTTP proxy to use to make the requests. Leave it to ``null`` to detect the
proxy automatically based on your system configuration.

query
.....

**type**: ``array``

An associative array of the query string values added to the URL before making
the request. This value must use the format ``['parameter-name' => parameter-value, ...]``.

resolve
.......

**type**: ``array``

A list of hostnames and their IP addresses to pre-populate the DNS cache used by
the HTTP client in order to avoid a DNS lookup for those hosts. This option is
useful to improve security when IPs are checked before the URL is passed to the
client and to make your tests easier.

The value of this option is an associative array of ``domain => IP address``
(e.g ``['symfony.com' => '46.137.106.254', ...]``).

retry_strategy
..............

**type**: ``string``

The service is used to decide if a request should be retried and to compute the
time to wait between retries. By default, it uses an instance of
:class:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy` configured
with ``http_codes``, ``delay``, ``max_delay``, ``multiplier`` and ``jitter``
options. This class has to implement
:class:`Symfony\\Component\\HttpClient\\Retry\\RetryStrategyInterface`.

scope
.....

**type**: ``string``

For scoped clients only: the regular expression that the URL must match before
applying all other non-default options. By default, the scope is derived from
`base_uri`_.

timeout
.......

**type**: ``float`` **default**: depends on your PHP config

Time, in seconds, to wait for a response. If the response takes longer, a
:class:`Symfony\\Component\\HttpClient\\Exception\\TransportException` is thrown.
Its default value is the same as the value of PHP's `default_socket_timeout`_
config option.

verify_host
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers is verified to ensure that
their common name matches the host included in the URL. This is usually
combined with ``verify_peer`` to also verify the certificate authenticity.

verify_peer
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers when negotiating a TLS or SSL
connection is verified for authenticity. Authenticating the certificate is not
enough to be sure about the server, so you should combine this with the
``verify_host`` option.

html_sanitizer
~~~~~~~~~~~~~~

.. versionadded:: 6.1

    The HTML sanitizer configuration was introduced in Symfony 6.1.

The ``html_sanitizer`` option (and its children) are used to configure
custom HTML sanitizers. Read more about the options in the
:ref:`HTML sanitizer documentation <html-sanitizer-configuration>`.

profiler
~~~~~~~~

.. _reference-profiler-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

The profiler can be enabled by setting this option to ``true``. When you
install it using Symfony Flex, the profiler is enabled in the ``dev``
and ``test`` environments.

.. note::

    The profiler works independently from the Web Developer Toolbar, see
    the :doc:`WebProfilerBundle configuration </reference/configuration/web_profiler>`
    on how to disable/enable the toolbar.

collect
.......

**type**: ``boolean`` **default**: ``true``

This option configures the way the profiler behaves when it is enabled. If set
to ``true``, the profiler collects data for all requests. If you want to only
collect information on-demand, you can set the ``collect`` flag to ``false`` and
activate the data collectors manually::

    $profiler->enable();

collect_parameter
.................

**type**: ``string`` **default**: ``null``

This specifies name of a query parameter, a body parameter or a request attribute
used to enable or disable collection of data by the profiler for each request.
Combine it with the ``collect`` option to enable/disable the profiler on demand:

* If the ``collect`` option is set to ``true`` but this parameter exists in a
  request and has any value other than ``true``, ``yes``, ``on`` or ``1``, the
  request data will not be collected;
* If the ``collect`` option is set to ``false``, but this parameter exists in a
  request and has value of ``true``, ``yes``, ``on`` or ``1``, the request data
  will be collected.

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request.

.. _only_master_requests:

only_main_requests
..................

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled on the main
requests (and not on the subrequests).

.. _profiler-dsn:

dsn
...

**type**: ``string`` **default**: ``'file:%kernel.cache_dir%/profiler'``

The DSN where to store the profiling information.

.. _collect_serializer_data:

collect_serializer_data
.......................

**type**: ``boolean`` **default**: ``false``

Set this option to ``true`` to enable the serializer data collector and its
profiler panel. When this option is ``true``, all normalizers and encoders are
decorated by traceable implementations that collect profiling information about them.

.. versionadded:: 6.1

    The ``collect_serializer_data`` option was introduced in Symfony 6.1.

rate_limiter
~~~~~~~~~~~~

.. _reference-rate-limiter-name:

name
....

**type**: ``prototype``

Name of the rate limiter you want to create.

lock_factory
""""""""""""

**type**: ``string`` **default:** ``lock.factory``

The service that is used to create a lock. The service has to be an instance of
the :class:`Symfony\\Component\\Lock\\LockFactory` class.

policy
""""""

**type**: ``string`` **required**

The name of the rate limiting algorithm to use. Example names are ``fixed_window``,
``sliding_window`` and ``no_limit``. See :ref:`Rate Limiter Policies <rate-limiter-policies>`)
for more information.

request
~~~~~~~

formats
.......

**type**: ``array`` **default**: ``[]``

This setting is used to associate additional request formats (e.g. ``html``)
to one or more mime types (e.g. ``text/html``), which will allow you to use the
format & mime types to call
:method:`Request::getFormat($mimeType) <Symfony\\Component\\HttpFoundation\\Request::getFormat>` or
:method:`Request::getMimeType($format) <Symfony\\Component\\HttpFoundation\\Request::getMimeType>`.

In practice, this is important because Symfony uses it to automatically set the
``Content-Type`` header on the ``Response`` (if you don't explicitly set one).
If you pass an array of mime types, the first will be used for the header.

To configure a ``jsonp`` format:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            request:
                formats:
                    jsonp: 'application/javascript'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:request>
                    <framework:format name="jsonp">
                        <framework:mime-type>application/javascript</framework:mime-type>
                    </framework:format>
                </framework:request>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->request()
                ->format('jsonp', 'application/javascript');
        };

router
~~~~~~

resource
........

**type**: ``string`` **required**

The path the main routing resource (e.g. a YAML file) that contains the
routes and imports the router should load.

.. _reference-router-type:

type
....

**type**: ``string``

The type of the resource to hint the loaders about the format. This isn't
needed when you use the default routers with the expected file extensions
(``.xml``, ``.yaml``, ``.php``).

default_uri
...........

**type**: ``string``

The default URI used to generate URLs in a non-HTTP context (see
:ref:`Generating URLs in Commands <router-generate-urls-commands>`).

http_port
.........

**type**: ``integer`` **default**: ``80``

The port for normal http requests (this is used when matching the scheme).

https_port
..........

**type**: ``integer`` **default**: ``443``

The port for https requests (this is used when matching the scheme).

strict_requirements
...................

**type**: ``mixed`` **default**: ``true``

Determines the routing generator behavior. When generating a route that
has specific :ref:`parameter requirements <routing-requirements>`, the generator
can behave differently in case the used parameters do not meet these requirements.

The value can be one of:

``true``
    Throw an exception when the requirements are not met;
``false``
    Disable exceptions when the requirements are not met and return ``''``
    instead;
``null``
    Disable checking the requirements (thus, match the route even when the
    requirements don't match).

``true`` is recommended in the development environment, while ``false``
or ``null`` might be preferred in production.

utf8
....

**type**: ``boolean`` **default**: ``true``

When this option is set to ``true``, the regular expressions used in the
:ref:`requirements of route parameters <routing-requirements>` will be run
using the `utf-8 modifier`_. This will for example match any UTF-8 character
when using ``.``, instead of matching only a single byte.

If the charset of your application is UTF-8 (as defined in the
:ref:`getCharset() method <configuration-kernel-charset>` of your kernel) it's
recommended setting it to ``true``. This will make non-UTF8 URLs to generate 404
errors.

cache_dir
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%``

The directory where routing information will be cached. Can be set to
``~`` (``null``) to disable route caching.

.. versionadded:: 6.2

    The ``cache_dir`` setting was introduced in Symfony 6.2.

.. _config-framework-session:

session
~~~~~~~

.. _storage_id:

storage_factory_id
..................

**type**: ``string`` **default**: ``'session.storage.factory.native'``

The service ID used for creating the ``SessionStorageInterface`` that stores
the session. This service is available in the Symfony application via the
``session.storage.factory`` service alias. The class has to implement
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageFactoryInterface`.
To see a list of all available storages, run:

.. code-block:: terminal

    $ php bin/console debug:container session.storage.factory.

.. _config-framework-session-handler-id:

handler_id
..........

**type**: ``string`` **default**: ``'session.handler.native_file'``

The service id used for session storage. The default value ``'session.handler.native_file'``
will let Symfony manage the sessions itself using files to store the session metadata.
Set it to ``null`` to use the native PHP session mechanism.
You can also :ref:`store sessions in a database <session-database>`.

.. _name:

name
....

**type**: ``string`` **default**: ``null``

This specifies the name of the session cookie. By default, it will use the
cookie name which is defined in the ``php.ini`` with the ``session.name``
directive.

cookie_lifetime
...............

**type**: ``integer`` **default**: ``null``

This determines the lifetime of the session - in seconds. The default value
- ``null`` - means that the ``session.cookie_lifetime`` value from ``php.ini``
will be used. Setting this value to ``0`` means the cookie is valid for
the length of the browser session.

cookie_path
...........

**type**: ``string`` **default**: ``/``

This determines the path to set in the session cookie. By default, it will
use ``/``.

cache_limiter
.............

**type**: ``string`` or ``int`` **default**: ``''``

If set to ``0``, Symfony won't set any particular header related to the cache
and it will rely on the cache control method configured in the
`session.cache-limiter`_ PHP.ini option.

Unlike the other session options, ``cache_limiter`` is set as a regular
:ref:`container parameter <configuration-parameters>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            session.storage.options:
                cache_limiter: 0

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="session.storage.options" type="collection">
                    <parameter key="cache_limiter">0</parameter>
                </parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('session.storage.options', [
            'cache_limiter' => 0,
        ]);

cookie_domain
.............

**type**: ``string`` **default**: ``''``

This determines the domain to set in the session cookie. By default, it's
blank, meaning the host name of the server which generated the cookie according
to the cookie specification.

cookie_samesite
...............

**type**: ``string`` or ``null`` **default**: ``'lax'``

It controls the way cookies are sent when the HTTP request did not originate
from the same domain that is associated with the cookies. Setting this option is
recommended to mitigate `CSRF security attacks`_.

By default, browsers send all cookies related to the domain of the HTTP request.
This may be a problem for example when you visit a forum and some malicious
comment includes a link like ``https://some-bank.com/?send_money_to=attacker&amount=1000``.
If you were previously logged into your bank website, the browser will send all
those cookies when making that HTTP request.

The possible values for this option are:

* ``null``, use it to disable this protection. Same behavior as in older Symfony
  versions.
* ``'none'`` (or the ``Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE`` constant), use it to allow
  sending of cookies when the HTTP request originated from a different domain
  (previously this was the default behavior of null, but in newer browsers ``'lax'``
  would be applied when the header has not been set)
* ``'strict'`` (or the ``Cookie::SAMESITE_STRICT`` constant), use it to never
  send any cookie when the HTTP request did not originate from the same domain.
* ``'lax'`` (or the ``Cookie::SAMESITE_LAX`` constant), use it to allow sending
  cookies when the request originated from a different domain, but only when the
  user consciously made the request (by clicking a link or submitting a form
  with the ``GET`` method).

cookie_secure
.............

**type**: ``boolean`` or ``'auto'`` **default**: ``'auto'``

This determines whether cookies should only be sent over secure connections. In
addition to ``true`` and ``false``, there's a special ``'auto'`` value that
means ``true`` for HTTPS requests and ``false`` for HTTP requests.

cookie_httponly
...............

**type**: ``boolean`` **default**: ``true``

This determines whether cookies should only be accessible through the HTTP
protocol. This means that the cookie won't be accessible by scripting
languages, such as JavaScript. This setting can effectively help to reduce
identity theft through XSS attacks.

gc_divisor
..........

**type**: ``integer`` **default**: ``100``

See `gc_probability`_.

gc_probability
..............

**type**: ``integer`` **default**: ``1``

This defines the probability that the garbage collector (GC) process is
started on every session initialization. The probability is calculated by
using ``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1%
chance that the GC process will start on each request.

gc_maxlifetime
..............

**type**: ``integer`` **default**: ``1440``

This determines the number of seconds after which data will be seen as "garbage"
and potentially cleaned up. Garbage collection may occur during session
start and depends on `gc_divisor`_ and `gc_probability`_.

sid_length
..........

**type**: ``integer`` **default**: ``32``

This determines the length of session ID string, which can be an integer between
``22`` and ``256`` (both inclusive), being ``32`` the recommended value. Longer
session IDs are harder to guess.

This option is related to the `session.sid_length PHP option`_.

sid_bits_per_character
......................

**type**: ``integer`` **default**: ``4``

This determines the number of bits in the encoded session ID character. The possible
values are ``4`` (0-9, a-f), ``5`` (0-9, a-v), and ``6`` (0-9, a-z, A-Z, "-", ",").
The more bits results in stronger session ID. ``5`` is recommended value for
most environments.

This option is related to the `session.sid_bits_per_character PHP option`_.

save_path
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/sessions``

This determines the argument to be passed to the save handler. If you choose
the default file handler, this is the path where the session files are created.

You can also set this value to the ``save_path`` of your ``php.ini`` by
setting the value to ``null``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                save_path: ~

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session save-path="null"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                ->savePath(null);
        };

.. _reference-session-metadata-update-threshold:

metadata_update_threshold
.........................

**type**: ``integer`` **default**: ``0``

This is how many seconds to wait between updating/writing the session metadata.
This can be useful if, for some reason, you want to limit the frequency at which
the session persists, instead of doing that on every request.

.. _reference-session-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the session support in the framework.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                enabled: true

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                ->enabled(true);
        };

use_cookies
...........

**type**: ``boolean`` **default**: ``null``

This specifies if the session ID is stored on the client side using cookies or
not. By default, it will use the value defined in the ``php.ini`` with the
``session.use_cookies`` directive.

assets
~~~~~~

.. _reference-assets-base-path:

base_path
.........

**type**: ``string``

This option allows you to define a base path to be used for assets:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                base_path: '/images'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-path="/images"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                ->basePath('/images');
        };

.. _reference-templating-base-urls:
.. _reference-assets-base-urls:

base_urls
.........

**type**: ``array``

This option allows you to define base URLs to be used for assets.
If multiple base URLs are provided, Symfony will select one from the
collection each time it generates an asset's path:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                base_urls:
                    - 'http://cdn.example.com/'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-url="http://cdn.example.com/"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                ->baseUrls(['http://cdn.example.com/']);
        };

.. _reference-framework-assets-packages:

packages
........

You can group assets into packages, to specify different base URLs for them:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                packages:
                    avatars:
                        base_urls: 'http://static_cdn.example.com/avatars'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets>
                    <framework:package
                        name="avatars"
                        base-url="http://static_cdn.example.com/avatars"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                ->package('avatars')
                    ->baseUrls(['http://static_cdn.example.com/avatars']);
        };

Now you can use the ``avatars`` package in your templates:

.. code-block:: html+twig

    <img src="{{ asset('...', 'avatars') }}">

Each package can configure the following options:

* :ref:`base_path <reference-assets-base-path>`
* :ref:`base_urls <reference-assets-base-urls>`
* :ref:`version_strategy <reference-assets-version-strategy>`
* :ref:`version <reference-framework-assets-version>`
* :ref:`version_format <reference-assets-version-format>`
* :ref:`json_manifest_path <reference-assets-json-manifest-path>`
* :ref:`strict_mode <reference-assets-strict-mode>`

.. _reference-framework-assets-version:
.. _ref-framework-assets-version:

version
.......

**type**: ``string``

This option is used to *bust* the cache on assets by globally adding a query
parameter to all rendered asset paths (e.g. ``/images/logo.png?v2``). This
applies only to assets rendered via the Twig ``asset()`` function (or PHP
equivalent).

For example, suppose you have the following:

.. code-block:: html+twig

    <img src="{{ asset('images/logo.png') }}" alt="Symfony!"/>

By default, this will render a path to your image such as ``/images/logo.png``.
Now, activate the ``version`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                version: 'v2'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version="v2"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                ->version('v2');
        };

Now, the same asset will be rendered as ``/images/logo.png?v2`` If you use
this feature, you **must** manually increment the ``version`` value
before each deployment so that the query parameters change.

You can also control how the query string works via the `version_format`_
option.

.. note::

    This parameter cannot be set at the same time as ``version_strategy`` or ``json_manifest_path``.

.. tip::

    As with all settings, you can use a parameter as value for the
    ``version``. This makes it easier to increment the cache on each
    deployment.

.. _reference-templating-version-format:
.. _reference-assets-version-format:

version_format
..............

**type**: ``string`` **default**: ``%%s?%%s``

This specifies a :phpfunction:`sprintf` pattern that will be used with the
`version`_ option to construct an asset's path. By default, the pattern
adds the asset's version as a query string. For example, if
``version_format`` is set to ``%%s?version=%%s`` and ``version``
is set to ``5``, the asset's path would be ``/images/logo.png?version=5``.

.. note::

    All percentage signs (``%``) in the format string must be doubled to
    escape the character. Without escaping, values might inadvertently be
    interpreted as :ref:`service-container-parameters`.

.. tip::

    Some CDN's do not support cache-busting via query strings, so injecting
    the version into the actual file path is necessary. Thankfully,
    ``version_format`` is not limited to producing versioned query
    strings.

    The pattern receives the asset's original path and version as its first
    and second parameters, respectively. Since the asset's path is one
    parameter, you cannot modify it in-place (e.g. ``/images/logo-v5.png``);
    however, you can prefix the asset's path using a pattern of
    ``version-%%2$s/%%1$s``, which would result in the path
    ``version-5/images/logo.png``.

    URL rewrite rules could then be used to disregard the version prefix
    before serving the asset. Alternatively, you could copy assets to the
    appropriate version path as part of your deployment process and forgot
    any URL rewriting. The latter option is useful if you would like older
    asset versions to remain accessible at their original URL.

.. _reference-assets-version-strategy:
.. _reference-templating-version-strategy:

version_strategy
................

**type**: ``string`` **default**: ``null``

The service id of the :doc:`asset version strategy </frontend/custom_version_strategy>`
applied to the assets. This option can be set globally for all assets and
individually for each asset package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            assets:
                # this strategy is applied to every asset (including packages)
                version_strategy: 'app.asset.my_versioning_strategy'
                packages:
                    foo_package:
                        # this package removes any versioning (its assets won't be versioned)
                        version: ~
                    bar_package:
                        # this package uses its own strategy (the default strategy is ignored)
                        version_strategy: 'app.asset.another_version_strategy'
                    baz_package:
                        # this package inherits the default strategy
                        base_path: '/images'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version-strategy="app.asset.my_versioning_strategy">
                    <!-- this package removes any versioning (its assets won't be versioned) -->
                    <framework:package
                        name="foo_package"
                        version="null"/>
                    <!-- this package uses its own strategy (the default strategy is ignored) -->
                    <framework:package
                        name="bar_package"
                        version-strategy="app.asset.another_version_strategy"/>
                    <!-- this package inherits the default strategy -->
                    <framework:package
                        name="baz_package"
                        base_path="/images"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                ->versionStrategy('app.asset.my_versioning_strategy');

            $framework->assets()->package('foo_package')
                // this package removes any versioning (its assets won't be versioned)
                ->version(null);

            $framework->assets()->package('bar_package')
                // this package uses its own strategy (the default strategy is ignored)
                ->versionStrategy('app.asset.another_version_strategy');

            $framework->assets()->package('baz_package')
                // this package inherits the default strategy
                ->basePath('/images');
        };

.. note::

    This parameter cannot be set at the same time as ``version`` or ``json_manifest_path``.

.. _reference-assets-json-manifest-path:
.. _reference-templating-json-manifest-path:

json_manifest_path
..................

**type**: ``string`` **default**: ``null``

The file path or absolute URL to a ``manifest.json`` file containing an
associative array of asset names and their respective compiled names. A common
cache-busting technique using a "manifest" file works by writing out assets with
a "hash" appended to their file names (e.g. ``main.ae433f1cb.css``) during a
front-end compilation routine.

.. tip::

    Symfony's :ref:`Webpack Encore <frontend-webpack-encore>` supports
    :ref:`outputting hashed assets <encore-long-term-caching>`. Moreover, this
    can be incorporated into many other workflows, including Webpack and
    Gulp using `webpack-manifest-plugin`_ and `gulp-rev`_, respectively.

This option can be set globally for all assets and individually for each asset
package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            assets:
                # this manifest is applied to every asset (including packages)
                json_manifest_path: "%kernel.project_dir%/public/build/manifest.json"
                # you can use absolute URLs too and Symfony will download them automatically
                # json_manifest_path: 'https://cdn.example.com/manifest.json'
                packages:
                    foo_package:
                        # this package uses its own manifest (the default file is ignored)
                        json_manifest_path: "%kernel.project_dir%/public/build/a_different_manifest.json"
                        # Throws an exception when an asset is not found in the manifest
                        strict_mode: %kernel.debug%
                    bar_package:
                        # this package uses the global manifest (the default file is used)
                        base_path: '/images'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- this manifest is applied to every asset (including packages) -->
                <framework:assets json-manifest-path="%kernel.project_dir%/public/build/manifest.json">
                <!-- you can use absolute URLs too and Symfony will download them automatically -->
                <!-- <framework:assets json-manifest-path="https://cdn.example.com/manifest.json"> -->
                    <!-- this package uses its own manifest (the default file is ignored) -->
                    <!-- Throws an exception when an asset is not found in the manifest -->
                    <framework:package
                        name="foo_package"
                        json-manifest-path="%kernel.project_dir%/public/build/a_different_manifest.json" strict-mode="%kernel.debug%"/>
                    <!-- this package uses the global manifest (the default file is used) -->
                    <framework:package
                        name="bar_package"
                        base-path="/images"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->assets()
                // this manifest is applied to every asset (including packages)
                ->jsonManifestPath('%kernel.project_dir%/public/build/manifest.json');

            // you can use absolute URLs too and Symfony will download them automatically
            // 'json_manifest_path' => 'https://cdn.example.com/manifest.json',
            $framework->assets()->package('foo_package')
                // this package uses its own manifest (the default file is ignored)
                ->jsonManifestPath('%kernel.project_dir%/public/build/a_different_manifest.json')
                // Throws an exception when an asset is not found in the manifest
                ->setStrictMode('%kernel.debug%');

            $framework->assets()->package('bar_package')
                // this package uses the global manifest (the default file is used)
                ->basePath('/images');
        };

.. note::

    This parameter cannot be set at the same time as ``version`` or ``version_strategy``.
    Additionally, this option cannot be nullified at the package scope if a global manifest
    file is specified.

.. tip::

    If you request an asset that is *not found* in the ``manifest.json`` file, the original -
    *unmodified* - asset path will be returned.
    Since Symfony 5.4, you can set ``strict_mode`` to ``true`` to get an exception when an asset is *not found*.

.. note::

    If a URL is set, the JSON manifest is downloaded on each request using the `http_client`_.

.. _reference-assets-strict-mode:

strict_mode
...........

**type**: ``boolean`` **default**: ``false``

When enabled, the strict mode asserts that all requested assets are in the
manifest file. This option is useful to detect typos or missing assets, the
recommended value is ``%kernel.debug%``.

translator
~~~~~~~~~~

cache_dir
.........

**type**: ``string`` | ``null`` **default**: ``%kernel.cache_dir%/translations/``

Defines the directory where the translation cache is stored. Use ``null`` to
disable this cache.

.. _reference-translator-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable the ``translator`` service in the service container.

.. _fallback:

fallbacks
.........

**type**: ``string|array`` **default**: value of `default_locale`_

This option is used when the translation key for the current locale wasn't
found.

.. seealso::

    For more details, see :doc:`/translation`.

.. _reference-framework-translator-logging:

logging
.......

**default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

When ``true``, a log entry is made whenever the translator cannot find a translation
for a given key. The logs are made to the ``translation`` channel at the
``debug`` level for keys where there is a translation in the fallback
locale, and the ``warning`` level if there is no translation to use at all.

.. _reference-framework-translator-formatter:

formatter
.........

**type**: ``string`` **default**: ``translator.formatter.default``

The ID of the service used to format translation messages. The service class
must implement the :class:`Symfony\\Component\\Translation\\Formatter\\MessageFormatterInterface`.

.. _reference-translator-paths:

paths
.....

**type**: ``array`` **default**: ``[]``

This option allows to define an array of paths where the component will look
for translation files. The later a path is added, the more priority it has
(translations from later paths overwrite earlier ones). Translations from the
:ref:`default_path <reference-translator-default_path>` have more priority than
translations from all these paths.

.. _reference-translator-default_path:

default_path
............

**type**: ``string`` **default**: ``%kernel.project_dir%/translations``

This option allows to define the path where the application translations files
are stored.

.. _reference-translator-providers:

providers
.........

**type**: ``array`` **default**: ``[]``

This option enables and configures :ref:`translation providers <translation-providers>`
to push and pull your translations to/from third party translation services.

property_access
~~~~~~~~~~~~~~~

magic_call
..........

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __call() method <components-property-access-magic-call>` when
its ``getValue()`` method is called.

magic_get
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __get() method <components-property-access-magic-get>` when
its ``getValue()`` method is called.

magic_set
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __set() method <components-property-access-writing-to-objects>` when
its ``setValue()`` method is called.

throw_exception_on_invalid_index
................................

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid index of an array.

throw_exception_on_invalid_property_path
........................................

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid property path of an object.

property_info
~~~~~~~~~~~~~

.. _reference-property-info-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-validation:

validation
~~~~~~~~~~

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable validation support.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. _reference-validation-enable_annotations:

enable_annotations
..................

**type**: ``boolean`` **default**: ``true``

If this option is enabled, validation constraints can be defined using annotations or attributes.

translation_domain
..................

**type**: ``string | false`` **default**: ``validators``

The translation domain that is used when translating validation constraint
error messages. Use false to disable translations.

.. _reference-validation-not-compromised-password:

not_compromised_password
........................

The :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint makes HTTP requests to a public API to check if the given password
has been compromised in a data breach.

.. _reference-validation-not-compromised-password-enabled:

enabled
"""""""

**type**: ``boolean`` **default**: ``true``

If you set this option to ``false``, no HTTP requests will be made and the given
password will be considered valid. This is useful when you don't want or can't
make HTTP requests, such as in ``dev`` and ``test`` environments or in
continuous integration servers.

endpoint
""""""""

**type**: ``string`` **default**: ``null``

By default, the :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint uses the public API provided by `haveibeenpwned.com`_. This option
allows to define a different, but compatible, API endpoint to make the password
checks. It's useful for example when the Symfony application is run in an
intranet without public access to the internet.

static_method
.............

**type**: ``string | array`` **default**: ``['loadValidatorMetadata']``

Defines the name of the static method which is called to load the validation
metadata of the class. You can define an array of strings with the names of
several methods. In that case, all of them will be called in that order to load
the metadata.

.. _reference-validation-email_validation_mode:

email_validation_mode
.....................

**type**: ``string`` **default**: ``loose``

.. deprecated:: 6.2

    The ``loose`` default value is deprecated since Symfony 6.2. Starting from
    Symfony 7.0, the default value of this option will be ``html5``.

Sets the default value for the
:ref:`"mode" option of the Email validator <reference-constraint-email-mode>`.

.. _reference-validation-mapping:

mapping
.......

.. _reference-validation-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``['config/validation/']``

This option allows to define an array of paths with files or directories where
the component will look for additional validation files:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            validation:
                mapping:
                    paths:
                        - "%kernel.project_dir%/config/validation/"

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation>
                    <framework:mapping>
                        <framework:path>%kernel.project_dir%/config/validation/</framework:path>
                    </framework:mapping>
                </framework:validation>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->validation()
                ->mapping()
                    ->paths(['%kernel.project_dir%/config/validation/']);
        };

annotations
~~~~~~~~~~~

.. _reference-annotations-cache:

cache
.....

**type**: ``string`` **default**: ``'php_array'``

This option can be one of the following values:

php_array
    Use a PHP array to cache annotations in memory
file
    Use the filesystem to cache annotations
none
    Disable the caching of annotations

file_cache_dir
..............

**type**: ``string`` **default**: ``'%kernel.cache_dir%/annotations'``

The directory to store cache files for annotations, in case
``annotations.cache`` is set to ``'file'``.

debug
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

Whether to enable debug mode for caching. If enabled, the cache will
automatically update when the original file is changed (both with code and
annotation changes). For performance reasons, it is recommended to disable
debug mode in production, which will happen automatically if you use the
default value.


secrets
~~~~~~~

decryption_env_var
..................

**type**: ``string`` **default**: ``base64:default::SYMFONY_DECRYPTION_SECRET``

The environment variable that contains the decryption key.

local_dotenv_file
.................

**type**: ``string`` **default**: ``%kernel.project_dir%/.env.%kernel.environment%.local``

Path to an dotenv file that holds secrets. This is primarily used for testing.

vault_directory
...............

**type**: ``string`` **default**: ``%kernel.project_dir%/config/secrets/%kernel.environment%``

The directory where the vault of secrets is stored.

.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _reference-serializer-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the ``serializer`` service or not in the service container.

.. _reference-serializer-enable_annotations:

enable_annotations
..................

**type**: ``boolean`` **default**: ``true``

If this option is enabled, serialization groups can be defined using annotations or attributes.

.. seealso::

    For more information, see :ref:`serializer-using-serialization-groups-attributes`.

.. _reference-serializer-name_converter:

name_converter
..............

**type**: ``string``

The name converter to use.
The :class:`Symfony\\Component\\Serializer\\NameConverter\\CamelCaseToSnakeCaseNameConverter`
name converter can enabled by using the ``serializer.name_converter.camel_case_to_snake_case``
value.

.. seealso::

    For more information, see
    :ref:`component-serializer-converting-property-names-when-serializing-and-deserializing`.

.. _reference-serializer-circular_reference_handler:

circular_reference_handler
..........................

**type** ``string``

The service id that is used as the circular reference handler of the default
serializer. The service has to implement the magic ``__invoke($object)``
method.

.. seealso::

    For more information, see
    :ref:`component-serializer-handling-circular-references`.

.. _reference-serializer-mapping:

mapping
.......

.. _reference-serializer-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``[]``

This option allows to define an array of paths with files or directories where
the component will look for additional serialization files.

default_context
...............

**type**: ``array`` **default**: ``[]``

A map with default context options that will be used with each ``serialize`` and ``deserialize``
call. This can be used for example to set the json encoding behavior by setting ``json_encode_options``
to a `json_encode flags bitmask`_.

You can inspect the :ref:`serializer context builders <serializer-using-context-builders>`
to discover the available settings.

php_errors
~~~~~~~~~~

log
...

**type**: ``boolean|int`` **default**: ``%kernel.debug%``

Use the application logger instead of the PHP logger for logging PHP errors.
When an integer value is used, it also sets the log level. Those integer
values must be the same used in the `error_reporting PHP option`_.

This option also accepts a map of PHP errors to log levels:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            php_errors:
                log:
                    '!php/const \E_DEPRECATED': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_USER_DEPRECATED': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_NOTICE': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_USER_NOTICE': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_STRICT': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_WARNING': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_USER_WARNING': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_COMPILE_WARNING': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_CORE_WARNING': !php/const Psr\Log\LogLevel::ERROR
                    '!php/const \E_USER_ERROR': !php/const Psr\Log\LogLevel::CRITICAL
                    '!php/const \E_RECOVERABLE_ERROR': !php/const Psr\Log\LogLevel::CRITICAL
                    '!php/const \E_COMPILE_ERROR': !php/const Psr\Log\LogLevel::CRITICAL
                    '!php/const \E_PARSE': !php/const Psr\Log\LogLevel::CRITICAL
                    '!php/const \E_ERROR': !php/const Psr\Log\LogLevel::CRITICAL
                    '!php/const \E_CORE_ERROR': !php/const Psr\Log\LogLevel::CRITICAL

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- in XML configuration you cannot use PHP constants as the value of
                     the 'type' attribute, which makes this format way less readable.
                     Consider using YAML or PHP for this configuration -->
                <framework:log type="8" logLevel="error"/>
                <framework:log type="2" logLevel="error"/>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Psr\Log\LogLevel;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->phpErrors()->log(\E_DEPRECATED, LogLevel::ERROR);
            $framework->phpErrors()->log(\E_USER_DEPRECATED, LogLevel::ERROR);
            // ...
        };

throw
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

Throw PHP errors as ``\ErrorException`` instances. The parameter
``debug.error_handler.throw_at`` controls the threshold.

.. _reference-cache:

cache
~~~~~

.. _reference-cache-app:

app
...

**type**: ``string`` **default**: ``cache.adapter.filesystem``

The cache adapter used by the ``cache.app`` service. The FrameworkBundle
ships with multiple adapters: ``cache.adapter.apcu``, ``cache.adapter.doctrine``,
``cache.adapter.system``, ``cache.adapter.filesystem``, ``cache.adapter.psr6``,
``cache.adapter.redis``, ``cache.adapter.memcached`` and ``cache.adapter.pdo``.

There's also a special adapter called ``cache.adapter.array`` which stores
contents in memory using a PHP array and it's used to disable caching (mostly on
the ``dev`` environment).

.. tip::

    It might be tough to understand at the beginning, so to avoid confusion
    remember that all pools perform the same actions but on different medium
    given the adapter they are based on. Internally, a pool wraps the definition
    of an adapter.

.. _reference-cache-system:

system
......

**type**: ``string`` **default**: ``cache.adapter.system``

The cache adapter used by the ``cache.system`` service. It supports the same
adapters available for the ``cache.app`` service.

directory
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/pools``

The path to the cache directory used by services inheriting from the
``cache.adapter.filesystem`` adapter (including ``cache.app``).

default_doctrine_provider
.........................

**type**: ``string``

The service name to use as your default Doctrine provider. The provider is
available as the ``cache.default_doctrine_provider`` service.

default_psr6_provider
.....................

**type**: ``string``

The service name to use as your default PSR-6 provider. It is available as
the ``cache.default_psr6_provider`` service.

default_redis_provider
......................

**type**: ``string`` **default**: ``redis://localhost``

The DSN to use by the Redis provider. The provider is available as the ``cache.default_redis_provider``
service.

default_memcached_provider
..........................

**type**: ``string`` **default**: ``memcached://localhost``

The DSN to use by the Memcached provider. The provider is available as the ``cache.default_memcached_provider``
service.

default_pdo_provider
....................

**type**: ``string`` **default**: ``doctrine.dbal.default_connection``

The service id of the database connection, which should be either a PDO or a
Doctrine DBAL instance. The provider is available as the ``cache.default_pdo_provider``
service.

pools
.....

**type**: ``array``

A list of cache pools to be created by the framework extension.

.. seealso::

    For more information about how pools work, see :ref:`cache pools <component-cache-cache-pools>`.

To configure a Redis cache pool with a default lifetime of 1 hour, do the following:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            cache:
                pools:
                    cache.mycache:
                        adapter: cache.adapter.redis
                        default_lifetime: 3600

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:cache>
                    <framework:pool
                        name="cache.mycache"
                        adapter="cache.adapter.redis"
                        default-lifetime="3600"
                    />
                </framework:cache>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->cache()
                ->pool('cache.mycache')
                    ->adapters(['cache.adapter.redis'])
                    ->defaultLifetime(3600);
        };

.. _reference-cache-pools-name:

name
""""

**type**: ``prototype``

Name of the pool you want to create.

.. note::

    Your pool name must differ from ``cache.app`` or ``cache.system``.

adapter
"""""""

**type**: ``string`` **default**: ``cache.app``

The service name of the adapter to use. You can specify one of the default
services that follow the pattern ``cache.adapter.[type]``. Alternatively you
can specify another cache pool as base, which will make this pool inherit the
settings from the base pool as defaults.

.. note::

    Your service MUST implement the ``Psr\Cache\CacheItemPoolInterface`` interface.

public
""""""

**type**: ``boolean`` **default**: ``false``

Whether your service should be public or not.

tags
""""

**type**: ``boolean`` | ``string`` **default**: ``null``

Whether your service should be able to handle tags or not.
Can also be the service id of another cache pool where tags will be stored.

default_lifetime
""""""""""""""""

**type**: ``integer`` | ``string``

Default lifetime of your cache items. Give an integer value to set the default
lifetime in seconds. A string value could be ISO 8601 time interval, like ``"PT5M"``
or a PHP date expression that is accepted by ``strtotime()``, like ``"5 minutes"``.

If no value is provided, the cache adapter will fallback to the default value on
the actual cache storage.

provider
""""""""

**type**: ``string``

Overwrite the default service name or DSN respectively, if you do not want to
use what is configured as ``default_X_provider`` under ``cache``. See the
description of the default provider setting above for information on how to
specify your specific provider.

clearer
"""""""

**type**: ``string``

The cache clearer used to clear your PSR-6 cache.

.. seealso::

    For more information, see :class:`Symfony\\Component\\HttpKernel\\CacheClearer\\Psr6CacheClearer`.

.. _reference-cache-prefix-seed:

prefix_seed
...........

**type**: ``string`` **default**: ``_%kernel.project_dir%.%kernel.container_class%``

This value is used as part of the "namespace" generated for the
cache item keys. A common practice is to use the unique name of the application
(e.g. ``symfony.com``) because that prevents naming collisions when deploying
multiple applications into the same path (on different servers) that share the
same cache backend.

It's also useful when using `blue/green deployment`_ strategies and more
generally, when you need to abstract out the actual deployment directory (for
example, when warming caches offline).

.. _reference-lock:

lock
~~~~

**type**: ``string`` | ``array``

The default lock adapter. If not defined, the value is set to ``semaphore`` when
available, or to ``flock`` otherwise. Store's DSN are also allowed.

.. _reference-lock-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for lock or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-lock-resources:

resources
.........

**type**: ``array``

A map of lock stores to be created by the framework extension, with
the name as key and DSN as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock: '%env(LOCK_DSN)%'

    .. code-block:: xml

        <!-- config/packages/lock.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:lock>
                    <framework:resource name="default">%env(LOCK_DSN)%</framework:resource>
                </framework:lock>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/lock.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->lock()
                ->resource('default', [env('LOCK_DSN')]);
        };

.. seealso::

    For more details, see :doc:`/lock`.

.. _reference-lock-resources-name:

name
""""

**type**: ``prototype``

Name of the lock you want to create.

semaphore
~~~~~~~~~

.. versionadded:: 6.1

    The ``semaphore`` option was introduced in Symfony 6.1.

**type**: ``string`` | ``array``

The default semaphore adapter. Store's DSN are also allowed.

.. _reference-semaphore-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for semaphore or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-semaphore-resources:

resources
.........

**type**: ``array``

A map of semaphore stores to be created by the framework extension, with
the name as key and DSN as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/semaphore.yaml
        framework:
            semaphore: '%env(SEMAPHORE_DSN)%'

    .. code-block:: xml

        <!-- config/packages/semaphore.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:semaphore>
                    <framework:resource name="default">%env(SEMAPHORE_DSN)%</framework:resource>
                </framework:semaphore>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/semaphore.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->semaphore()
                ->resource('default', ['%env(SEMAPHORE_DSN)%']);
        };

.. _reference-semaphore-resources-name:

name
""""

**type**: ``prototype``

Name of the semaphore you want to create.

mailer
~~~~~~

.. _mailer-dsn:

dsn
...

**type**: ``string`` **default**: ``null``

The DSN used by the mailer. When several DSN may be used, use
``transports`` option (see below) instead.

transports
..........

**type**: ``array``

A :ref:`list of DSN <multiple-email-transports>` that can be used by the
mailer. A transport name is the key and the dsn is the value.

message_bus
...........

**type**: ``string`` **default**: ``null`` or default bus if Messenger component is installed

Service identifier of the message bus to use when using the
:doc:`Messenger component </messenger>` (e.g. ``messenger.default_bus``).

envelope
........

sender
""""""

**type**: ``string``

The "envelope sender" which is used as the value of ``MAIL FROM`` during the
`SMTP session`_. This value overrides any other sender set in the code.

recipients
""""""""""

**type**: ``array``

The "envelope recipient" which is used as the value of ``RCPT TO`` during the
the `SMTP session`_. This value overrides any other recipient set in the code.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                dsn: 'smtp://localhost:25'
                envelope:
                    recipients: ['admin@symfony.com', 'lead@symfony.com']

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:mailer dsn="smtp://localhost:25">
                    <framework:envelope>
                        <framework:recipient>admin@symfony.com</framework:recipient>
                        <framework:recipient>lead@symfony.com</framework:recipient>
                    </framework:envelope>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $containerConfigurator): void {
            $containerConfigurator->extension('framework', [
                'mailer' => [
                    'dsn' => 'smtp://localhost:25',
                    'envelope' => [
                        'recipients' => [
                            'admin@symfony.com',
                            'lead@symfony.com',
                        ],
                    ],
                ],
            ]);
        };

.. _mailer-headers:

headers
.......

**type**: ``array``

Headers to add to emails. The key (``name`` attribute in xml format) is the
header name and value the header value.

.. seealso::

    For more information, see :ref:`Configuring Emails Globally <mailer-configure-email-globally>`

web_link
~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Adds a `Link HTTP header`_ to the response.

workflows
~~~~~~~~~

**type**: ``array``

A list of workflows to be created by the framework extension:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                my_workflow:
                    # ...

    .. code-block:: xml

        <!-- config/packages/workflow.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:workflows>
                    <framework:workflow
                        name="my_workflow"/>
                </framework:workflows>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/workflow.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->workflows()
                ->workflows('my_workflow')
                    // ...
            ;
        };

.. seealso::

    See also the article about :doc:`using workflows in Symfony applications </workflow>`.

.. _reference-workflows-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the support for workflows or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-workflows-name:

name
....

**type**: ``prototype``

Name of the workflow you want to create.

audit_trail
"""""""""""

**type**: ``boolean``

If set to ``true``, the :class:`Symfony\\Component\\Workflow\\EventListener\\AuditTrailListener`
will be enabled.

initial_marking
"""""""""""""""

**type**: ``string`` | ``array``

One of the ``places`` or ``empty``. If not null and the supported object is not
already initialized via the workflow, this place will be set.

marking_store
"""""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``arguments`` (**type**: ``array``)
* ``service`` (**type**: ``string``)
* ``type`` (**type**: ``string`` **allow value**: ``'method'``)

metadata
""""""""

**type**: ``array``

Metadata available for the workflow configuration.
Note that ``places`` and ``transitions`` can also have their own
``metadata`` entry.

places
""""""

**type**: ``array``

All available places (**type**: ``string``) for the workflow configuration.

supports
""""""""

**type**: ``string`` | ``array``

The FQCN (fully-qualified class name) of the object supported by the workflow
configuration or an array of FQCN if multiple objects are supported.

support_strategy
""""""""""""""""

**type**: ``string``

transitions
"""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``from`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed for both ``workflow`` and ``state_machine``;
* ``guard`` (**type**: ``string``) an :doc:`ExpressionLanguage </components/expression_language>`
  compatible expression to block the transition;
* ``name`` (**type**: ``string``) the name of the transition;
* ``to`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed only for ``workflow``.

.. _reference-workflows-type:

type
""""

**type**: ``string`` **possible values**: ``'workflow'`` or ``'state_machine'``

Defines the kind of workflow that is going to be created, which can be either
a normal workflow or a state machine. Read :doc:`this article </workflow/workflow-and-state-machine>`
to know their differences.

.. _framework_exceptions:

exceptions
~~~~~~~~~~

**type**: ``array``

Defines the :ref:`log level </logging>` and HTTP status code applied to the
exceptions that match the given exception class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Symfony\Component\HttpKernel\Exception\BadRequestHttpException:
                    log_level: 'debug'
                    status_code: 422

    .. code-block:: xml

        <!-- config/packages/exceptions.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:exception
                    class="Symfony\Component\HttpKernel\Exception\BadRequestHttpException"
                    log-level="debug"
                    status-code="422"
                />
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/exceptions.php
        use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->exception(BadRequestHttpException::class)
                ->logLevel('debug')
                ->statusCode(422)
            ;
        };

The order in which you configure exceptions is important because Symfony will
use the configuration of the first exception that matches ``instanceof``:

.. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Exception:
                    log_level: 'debug'
                    status_code: 404
                # The following configuration will never be used because \RuntimeException extends \Exception
                RuntimeException:
                    log_level: 'debug'
                    status_code: 422

You can map a status code and a set of headers to an exception thanks
to the ``#[WithHttpStatus]`` attribute on the exception class::

    namespace App\Exception;

    use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

    #[WithHttpStatus(422, [
       'Retry-After' => 10,
       'X-Custom-Header' => 'header-value',
    ])]
    class CustomException extends \Exception
    {
    }

.. versionadded:: 6.3

    The ``#[WithHttpStatus]`` attribute was introduced in Symfony 6.3.

It is also possible to map a log level on a custom exception class using
the ``#[WithLogLevel]`` attribute::

    namespace App\Exception;

    use Psr\Log\LogLevel;
    use Symfony\Component\HttpKernel\Attribute\WithLogLevel;

    #[WithLogLevel(LogLevel::WARNING)]
    class CustomException extends \Exception
    {
    }

.. versionadded:: 6.3

    The ``#[WithLogLevel]`` attribute was introduced in Symfony 6.3.

.. _`HTTP Host header attacks`: https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
.. _`PhpStormProtocol`: https://github.com/aik099/PhpStormProtocol
.. _`phpstorm-url-handler`: https://github.com/sanduhrs/phpstorm-url-handler
.. _`blue/green deployment`: https://martinfowler.com/bliki/BlueGreenDeployment.html
.. _`gulp-rev`: https://www.npmjs.com/package/gulp-rev
.. _`webpack-manifest-plugin`: https://www.npmjs.com/package/webpack-manifest-plugin
.. _`json_encode flags bitmask`: https://www.php.net/json_encode
.. _`error_reporting PHP option`: https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
.. _`CSRF security attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`session.sid_length PHP option`: https://www.php.net/manual/session.configuration.php#ini.session.sid-length
.. _`session.sid_bits_per_character PHP option`: https://www.php.net/manual/session.configuration.php#ini.session.sid-bits-per-character
.. _`X-Robots-Tag HTTP header`: https://developers.google.com/search/reference/robots_meta_tag
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`default_socket_timeout`: https://www.php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
.. _`PEM formatted`: https://en.wikipedia.org/wiki/Privacy-Enhanced_Mail
.. _`haveibeenpwned.com`: https://haveibeenpwned.com/
.. _`session.cache-limiter`: https://www.php.net/manual/en/session.configuration.php#ini.session.cache-limiter
.. _`Microsoft NTLM authentication protocol`: https://docs.microsoft.com/en-us/windows/win32/secauthn/microsoft-ntlm
.. _`utf-8 modifier`: https://www.php.net/reference.pcre.pattern.modifiers
.. _`Link HTTP header`: https://tools.ietf.org/html/rfc5988
.. _`SMTP session`: https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol#SMTP_transport_example
