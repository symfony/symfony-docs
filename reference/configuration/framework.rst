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

.. rst-class:: list-config-options list-config-options--complex

* `annotations`_

  * :ref:`cache <reference-annotations-cache>`
  * `debug`_
  * `file_cache_dir`_

* `assets`_

  * `base_path`_
  * `base_urls`_
  * `json_manifest_path`_
  * `packages`_
  * `version_format`_
  * `version_strategy`_
  * `version`_

* :ref:`cache <reference-cache>`

  * :ref:`app <reference-cache-app>`
  * `default_doctrine_provider`_
  * `default_memcached_provider`_
  * `default_pdo_provider`_
  * `default_psr6_provider`_
  * `default_redis_provider`_
  * `directory`_
  * `pools`_

    * :ref:`name <reference-cache-pools-name>`

      * `adapter`_
      * `clearer`_
      * `default_lifetime`_
      * `provider`_
      * `public`_
      * `tags`_

  * `prefix_seed`_
  * `system`_

* `csrf_protection`_

  * :ref:`enabled <reference-csrf_protection-enabled>`

* `default_locale`_
* `disallow_search_engine_index`_
* `error_controller`_
* `esi`_

  * :ref:`enabled <reference-esi-enabled>`

* :ref:`form <reference-framework-form>`

  * :ref:`enabled <reference-form-enabled>`

* `fragments`_

  * :ref:`enabled <reference-fragments-enabled>`
  * `hinclude_default_template`_
  * :ref:`path <reference-fragments-path>`

* `http_client`_

  * :ref:`default_options <reference-http-client-default-options>`

    * `bindto`_
    * `buffer`_
    * `cafile`_
    * `capath`_
    * `ciphers`_
    * :ref:`headers <http-headers>`
    * `http_version`_
    * `local_cert`_
    * `local_pk`_
    * `max_redirects`_
    * `no_proxy`_
    * `passphrase`_
    * `peer_fingerprint`_
    * `proxy`_
    * `resolve`_
    * `timeout`_
    * `max_duration`_
    * `verify_host`_
    * `verify_peer`_

  * `max_host_connections`_
  * :ref:`scoped_clients <reference-http-client-scoped-clients>`

    * `scope`_
    * `auth_basic`_
    * `auth_bearer`_
    * `auth_ntlm`_
    * `base_uri`_
    * `bindto`_
    * `buffer`_
    * `cafile`_
    * `capath`_
    * `ciphers`_
    * :ref:`headers <http-headers>`
    * `http_version`_
    * `local_cert`_
    * `local_pk`_
    * `max_redirects`_
    * `no_proxy`_
    * `passphrase`_
    * `peer_fingerprint`_
    * `proxy`_
    * `query`_
    * `resolve`_

    * :ref:`retry_failed <reference-http-client-retry-failed>`

      * `retry_strategy`_
      * :ref:`enabled <reference-http-client-retry-enabled>`
      * `delay`_
      * `http_codes`_
      * `max_delay`_
      * `max_retries`_
      * `multiplier`_
      * `jitter`_

    * `timeout`_
    * `max_duration`_
    * `verify_host`_
    * `verify_peer`_

  * :ref:`retry_failed <reference-http-client-retry-failed>`

    * `retry_strategy`_
    * :ref:`enabled <reference-http-client-retry-enabled>`
    * `delay`_
    * `http_codes`_
    * `max_delay`_
    * `max_retries`_
    * `multiplier`_
    * `jitter`_

* `http_method_override`_
* `ide`_
* :ref:`lock <reference-lock>`

  * :ref:`enabled <reference-lock-enabled>`
  * :ref:`resources <reference-lock-resources>`

    * :ref:`name <reference-lock-resources-name>`

* `mailer`_

  * :ref:`dsn <mailer-dsn>`
  * `transports`_
  * `message_bus`_
  * `envelope`_

    * `sender`_
    * `recipients`_

  * :ref:`headers <mailer-headers>`

* `php_errors`_

  * `log`_
  * `throw`_

* `profiler`_

  * `collect`_
  * :ref:`dsn <profiler-dsn>`
  * :ref:`enabled <reference-profiler-enabled>`
  * `only_exceptions`_
  * `only_main_requests`_

* `property_access`_

  * `magic_call`_
  * `magic_get`_
  * `magic_set`_
  * `throw_exception_on_invalid_index`_
  * `throw_exception_on_invalid_property_path`_

* `property_info`_

  * :ref:`enabled <reference-property-info-enabled>`

* `rate_limiter`_:

  * :ref:`name <reference-rate-limiter-name>`

    * `lock_factory`_
    * `policy`_

* `request`_:

  * `formats`_

* `router`_

  * `default_uri`_
  * `http_port`_
  * `https_port`_
  * `resource`_
  * `strict_requirements`_
  * :ref:`type <reference-router-type>`
  * `utf8`_

* `secret`_
* `secrets`_

  * `decryption_env_var`_
  * `local_dotenv_file`_
  * `vault_directory`_

* `serializer`_

  * :ref:`circular_reference_handler <reference-serializer-circular_reference_handler>`
  * :ref:`enable_annotations <reference-serializer-enable_annotations>`
  * :ref:`enabled <reference-serializer-enabled>`
  * :ref:`mapping <reference-serializer-mapping>`

    * :ref:`paths <reference-serializer-mapping-paths>`

  * :ref:`name_converter <reference-serializer-name_converter>`

* `session`_

  * `cache_limiter`_
  * `cookie_domain`_
  * `cookie_httponly`_
  * `cookie_lifetime`_
  * `cookie_path`_
  * `cookie_samesite`_
  * `cookie_secure`_
  * :ref:`enabled <reference-session-enabled>`
  * `gc_divisor`_
  * `gc_maxlifetime`_
  * `gc_probability`_
  * `handler_id`_
  * `metadata_update_threshold`_
  * `name`_
  * `save_path`_
  * `sid_length`_
  * `sid_bits_per_character`_
  * `storage_factory_id`_
  * `use_cookies`_

* `test`_
* `translator`_

  * `cache_dir`_
  * :ref:`default_path <reference-translator-default_path>`
  * :ref:`enabled <reference-translator-enabled>`
  * :ref:`enabled_locales <reference-translator-enabled-locales>`
  * `fallbacks`_
  * `formatter`_
  * `logging`_
  * :ref:`paths <reference-translator-paths>`

* `trusted_headers`_
* `trusted_hosts`_
* `trusted_proxies`_
* `validation`_

  * :ref:`cache <reference-validation-cache>`
  * `email_validation_mode`_
  * :ref:`enable_annotations <reference-validation-enable_annotations>`
  * :ref:`enabled <reference-validation-enabled>`
  * :ref:`mapping <reference-validation-mapping>`

    * :ref:`paths <reference-validation-mapping-paths>`

  * :ref:`not_compromised_password <reference-validation-not-compromised-password>`

    * :ref:`enabled <reference-validation-not-compromised-password-enabled>`
    * `endpoint`_

  * `static_method`_
  * `translation_domain`_

* `web_link`_
* `workflows`_

  * :ref:`enabled <reference-workflows-enabled>`
  * :ref:`name <reference-workflows-name>`

    * `audit_trail`_
    * `initial_marking`_
    * `marking_store`_
    * `metadata`_
    * `places`_
    * `supports`_
    * `support_strategy`_
    * `transitions`_
    * :ref:`type <reference-workflows-type>`

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

This option becomes the service container parameter named ``kernel.secret``,
which you can use whenever the application needs an immutable random string
to add more entropy.

As with any other security-related parameter, it is a good practice to change
this value from time to time. However, keep in mind that changing this value
will invalidate all signed URIs and Remember Me cookies. That's why, after
changing this value, you should regenerate the application cache and log
out all the application users.

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

.. _reference-framework-trusted-headers:

trusted_headers
~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``trusted_headers`` option was introduced in Symfony 5.2.

The ``trusted_headers`` option is needed to configure which client information
should be trusted (e.g. their host) when running Symfony behind a load balancer
or a reverse proxy. See :doc:`/deployment/proxies`.

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``trusted_proxies`` option was reintroduced in Symfony 5.2 (it had been
    removed in Symfony 3.3).

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
feature is to configure it on a system level. First, you can set its value to
some environment variable that stores the name of the IDE/editor:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # the env var stores the IDE/editor name (e.g. 'phpstorm', 'vscode', etc.)
            ide: '%env(resolve:CODE_EDITOR)%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- the env var stores the IDE/editor name (e.g. 'phpstorm', 'vscode', etc.) -->
            <framework:config ide="%env(resolve:CODE_EDITOR)%"/>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // the env var stores the IDE/editor name (e.g. 'phpstorm', 'vscode', etc.)
            $framework->ide('%env(resolve:CODE_EDITOR)%');
        };

.. versionadded:: 5.3

    The option to use env vars in the ``framework.ide`` option was introduced
    in Symfony 5.3.

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
attacks is to whitelist the hosts that your Symfony application can respond
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

    See :doc:`/templating/hinclude` for more information about hinclude.

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

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            max_host_connections: 10
            default_options:
                headers: { 'X-Powered-By': 'ACME App' }
                max_redirects: 7

.. _reference-http-client-scoped-clients:

Multiple pre-configured HTTP client services can be defined, each with its
service name defined as a key under ``scoped_clients``. Scoped clients inherit
the default options defined for the ``http_client`` service. You can override
these options and can define a few others:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            scoped_clients:
                my_api.client:
                    auth_bearer: secret_bearer_token
                    # ...

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

.. versionadded:: 5.2

    The ``retry_failed`` option was introduced in Symfony 5.2.

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

=======================  ==================  ==========================
``base_uri``             Relative URI        Actual Requested URI
=======================  ==================  ==========================
http://example.org       /bar                http://example.org/bar
http://example.org/foo   /bar                http://example.org/bar
http://example.org/foo   bar                 http://example.org/bar
http://example.org/foo/  bar                 http://example.org/foo/bar
http://example.org       http://symfony.com  http://symfony.com
http://example.org/?bar  bar                 http://example.org/bar
=======================  ==================  ==========================

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

.. versionadded:: 5.2

    The ``delay`` option was introduced in Symfony 5.2.

The initial delay in milliseconds used to compute the waiting time between retries.

.. _reference-http-client-retry-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the support for retry failed HTTP request or not.
This setting is automatically set to true when one of the child settings is configured.

.. _http-headers:

headers
.......

**type**: ``array``

An associative array of the HTTP headers added before making the request. This
value must use the format ``['header-name' => 'value0, value1, ...']``.

http_codes
..........

**type**: ``array`` **default**: :method:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy::DEFAULT_RETRY_STATUS_CODES`

.. versionadded:: 5.2

    The ``http_codes`` option was introduced in Symfony 5.2.

The list of HTTP status codes that triggers a retry of the request.

http_version
............

**type**: ``string`` | ``null`` **default**: ``null``

The HTTP version to use, typically ``'1.1'``  or ``'2.0'``. Leave it to ``null``
to let Symfony select the best version automatically.

jitter
......

**type**: ``float`` **default**: ``0.1`` (must be between 0.0 and 1.0)

.. versionadded:: 5.2

    The ``jitter`` option was introduced in Symfony 5.2.

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

.. versionadded:: 5.2

    The ``max_delay`` option was introduced in Symfony 5.2.

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

.. versionadded:: 5.2

    The ``max_retries`` option was introduced in Symfony 5.2.

The maximum number of retries for failing requests. When the maximum is reached,
the client returns the last received response.

multiplier
..........

**type**: ``float`` **default**: ``2``

.. versionadded:: 5.2

    The ``multiplier`` option was introduced in Symfony 5.2.

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
...............

**type**: ``string``

.. versionadded:: 5.2

    The ``retry_strategy`` option was introduced in Symfony 5.2.

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

Time, in seconds, to wait for a response. If the response stales for longer, a
:class:`Symfony\\Component\\HttpClient\\Exception\\TransportException` is thrown.
Its default value is the same as the value of PHP's `default_socket_timeout`_
config option.

verify_host
...........

**type**: ``boolean``

If ``true``, the certificate sent by other servers is verified to ensure that
their common name matches the host included in the URL. This is usually
combined with ``verify_peer`` to also verify the certificate authenticity.

verify_peer
...........

**type**: ``boolean``

If ``true``, the certificate sent by other servers when negotiating a TLS or SSL
connection is verified for authenticity. Authenticating the certificate is not
enough to be sure about the server, so you should combine this with the
``verify_host`` option.

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

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request.

.. _only_master_requests:

only_main_requests
..................

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 5.3

    The ``only_main_requests`` option was introduced in Symfony 5.3. In previous
    versions it was called ``only_master_requests``.

When this is set to ``true``, the profiler will only be enabled on the main
requests (and not on the subrequests).

.. _profiler-dsn:

dsn
...

**type**: ``string`` **default**: ``'file:%kernel.cache_dir%/profiler'``

The DSN where to store the profiling information.

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

.. versionadded:: 5.1

    The ``default_uri`` option was introduced in Symfony 5.1.

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

**type**: ``boolean`` **default**: ``false``

.. deprecated:: 5.1

    Not setting this option is deprecated since Symfony 5.1. Moreover, the
    default value of this option will change to ``true`` in Symfony 6.0.

When this option is set to ``true``, the regular expressions used in the
:ref:`requirements of route parameters <routing-requirements>` will be run
using the `utf-8 modifier`_. This will for example match any UTF-8 character
when using ``.``, instead of matching only a single byte.

If the charset of your application is UTF-8 (as defined in the
:ref:`getCharset() method <configuration-kernel-charset>` of your kernel) it's
recommended to set it to ``true``. This will make non-UTF8 URLs to generate 404
errors.

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

.. versionadded:: 5.3

    The ``storage_factory_id`` option was introduced in Symfony 5.3.

.. _config-framework-session-handler-id:

handler_id
..........

**type**: ``string`` **default**: ``'session.handler.native_file'``

The service id used for session storage. The default value ``'session.handler.native_file'``
will let Symfony manage the sessions itself using files to store the session metadata.
Set it to ``null`` to use the native PHP session mechanism.
You can also :doc:`store sessions in a database </session/database>`.

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

It controls the way cookies are sent when the HTTP request was not originated
from the same domain the cookies are associated to. Setting this option is
recommended to mitigate `CSRF security attacks`_.

By default, browsers send all cookies related to the domain of the HTTP request.
This may be a problem for example when you visit a forum and some malicious
comment includes a link like ``https://some-bank.com/?send_money_to=attacker&amount=1000``.
If you were previously logged into your bank website, the browser will send all
those cookies when making that HTTP request.

The possible values for this option are:

* ``null``, use it to disable this protection. Same behavior as in older Symfony
  versions.
* ``'none'`` (or the ``Cookie::SAMESITE_NONE`` constant), use it to allow
  sending of cookies when the HTTP request originated from a different domain
  (previously this was the default behavior of null, but in newer browsers ``'lax'``
  would be applied when the header has not been set)
* ``'strict'`` (or the ``Cookie::SAMESITE_STRICT`` constant), use it to never
  send any cookie when the HTTP request is not originated from the same domain.
* ``'lax'`` (or the ``Cookie::SAMESITE_LAX`` constant), use it to allow sending
  cookies when the request originated from a different domain, but only when the
  user consciously made the request (by clicking a link or submitting a form
  with the ``GET`` method).

.. note::

    This option is available starting from PHP 7.3, but Symfony has a polyfill
    so you can use it with any older PHP version as well.

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

This determines the number of bits in encoded session ID character. The possible
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
                    <framework:package
                        name="foo_package"
                        json-manifest-path="%kernel.project_dir%/public/build/a_different_manifest.json"/>
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
                ->jsonManifestPath('%kernel.project_dir%/public/build/a_different_manifest.json');

            $framework->assets()->package('bar_package')
                // this package uses the global manifest (the default file is used)
                ->basePath('/images');
        };

.. versionadded:: 5.1

    The option to use an absolute URL in  ``json_manifest_path`` was introduced
    in Symfony 5.1.

.. note::

    This parameter cannot be set at the same time as ``version`` or ``version_strategy``.
    Additionally, this option cannot be nullified at the package scope if a global manifest
    file is specified.

.. tip::

    If you request an asset that is *not found* in the ``manifest.json`` file, the original -
    *unmodified* - asset path will be returned.

.. note::

    If an URL is set, the JSON manifest is downloaded on each request using the `http_client`_.

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

.. _reference-translator-enabled-locales:

enabled_locales
...............

**type**: ``array`` **default**: ``[]`` (empty array = enable all locales)

.. versionadded:: 5.1

    The ``enabled_locales`` option was introduced in Symfony 5.1.

Symfony applications generate by default the translation files for validation
and security messages in all locales. If your application only uses some
locales, use this option to restrict the files generated by Symfony and improve
performance a bit:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            translator:
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
                <framework:translator>
                    <enabled-locale>en</enabled-locale>
                    <enabled-locale>es</enabled-locale>
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->translator()
                ->enabledLocales(['en', 'es']);
        };

If some user makes requests with a locale not included in this option, the
application won't display any error because Symfony will display contents using
the fallback locale.

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
for a given key. The logs are made to the ``translation`` channel and at the
``debug`` for level for keys where there is a translation in the fallback
locale and the ``warning`` level if there is no translation to use at all.

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

.. versionadded:: 5.2

    The ``magic_get`` option was introduced in Symfony 5.2.

magic_set
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __set() method <components-property-access-writing-to-objects>` when
its ``setValue()`` method is called.

.. versionadded:: 5.2

    The ``magic_set`` option was introduced in Symfony 5.2.

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

validation
~~~~~~~~~~

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable validation support.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. _reference-validation-cache:

cache
.....

**type**: ``string``

The service that is used to persist class metadata in a cache. The service
has to implement the :class:`Symfony\\Component\\Validator\\Mapping\\Cache\\CacheInterface`.

Set this option to ``validator.mapping.cache.doctrine.apc`` to use the APC
cache provide from the Doctrine project.

.. _reference-validation-enable_annotations:

enable_annotations
..................

**type**: ``boolean`` **default**: ``false``

If this option is enabled, validation constraints can be defined using annotations.

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
intranet without public access to Internet.

static_method
.............

**type**: ``string | array`` **default**: ``['loadValidatorMetadata']``

Defines the name of the static method which is called to load the validation
metadata of the class. You can define an array of strings with the names of
several methods. In that case, all of them will be called in that order to load
the metadata.

email_validation_mode
.....................

**type**: ``string`` **default**: ``loose``

It controls the way email addresses are validated by the
:doc:`/reference/constraints/Email` validator. The possible values are:

* ``loose``, it uses a simple regular expression to validate the address (it
  checks that at least one ``@`` character is present, etc.). This validation is
  too simple and it's recommended to use the ``html5`` validation instead;
* ``html5``, it validates email addresses using the same regular expression
  defined in the HTML5 standard, making the backend validation consistent with
  the one provided by browsers;
* ``strict``, it uses the `egulias/email-validator`_ library (which you must
  install separately) to validate the addresses according to the `RFC 5322`_.

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

**type**: ``string`` **default**: ``'file'``

This option can be one of the following values:

file
    Use the filesystem to cache annotations
none
    Disable the caching of annotations
a service id
    A service id referencing a `Doctrine Cache`_ implementation

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

**type**: ``boolean`` **default**: ``false``

If this option is enabled, serialization groups can be defined using annotations.

.. seealso::

    For more information, see :ref:`serializer-using-serialization-groups-annotations`.

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

php_errors
~~~~~~~~~~

log
...

**type**: ``boolean|int`` **default**: ``%kernel.debug%``

Use the application logger instead of the PHP logger for logging PHP errors.
When an integer value is used, it also sets the log level. Those integer
values must be the same used in the `error_reporting PHP option`_.

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

    For more information about how pools works, see :ref:`cache pools <component-cache-cache-pools>`.

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
description of the default provider setting above for the type of adapter
you use for information on how to specify the provider.

clearer
"""""""

**type**: ``string``

The cache clearer used to clear your PSR-6 cache.

.. seealso::

    For more information, see :class:`Symfony\\Component\\HttpKernel\\CacheClearer\\Psr6CacheClearer`.

.. _reference-cache-prefix-seed:

prefix_seed
...........

**type**: ``string`` **default**: ``null``

If defined, this value is used as part of the "namespace" generated for the
cache item keys. A common practice is to use the unique name of the application
(e.g. ``symfony.com``) because that prevents naming collisions when deploying
multiple applications into the same path (on different servers) that share the
same cache backend.

It's also useful when using `blue/green deployment`_ strategies and more
generally, when you need to abstract out the actual deployment directory (for
example, when warming caches offline).

.. versionadded:: 5.2

    Starting from Symfony 5.2, the ``%kernel.container_class%`` parameter is no
    longer appended automatically to the value of this option. This allows
    sharing caches between applications or different environments.

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

A list of lock stores to be created by the framework extension.

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
                    <framework:resource>%env(LOCK_DSN)%</framework:resource>
                </framework:lock>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/lock.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->lock()
                ->resource('default', ['%env(LOCK_DSN)%']);
        };

.. seealso::

    For more details, see :doc:`/lock`.

.. _reference-lock-resources-name:

name
""""

**type**: ``prototype``

Name of the lock you want to create.

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

.. versionadded:: 5.1

    The ``message_bus`` option was introduced in Symfony 5.1.

**type**: ``string`` **default**: ``null`` or default bus if Messenger component is installed

Service identifier of the message bus to use when using the
:doc:`Messenger component </messenger>` (e.g. ``messenger.default_bus``).

envelope
........

sender
""""""

**type**: ``string``

Sender used by the ``Mailer``. Keep in mind that this setting override a
sender set in the code.

recipients
""""""""""

**type**: ``array``

Recipients used by the ``Mailer``. Keep in mind that this setting override
recipients set in the code.

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
                        ]
                    ]
                ]
            ]);
        };

.. _mailer-headers:

headers
.......

.. versionadded:: 5.2

    The ``headers`` mailer option was introduced in Symfony 5.2.

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

.. _`HTTP Host header attacks`: https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
.. _`Doctrine Cache`: https://www.doctrine-project.org/projects/doctrine-cache/en/current/index.html
.. _`egulias/email-validator`: https://github.com/egulias/EmailValidator
.. _`RFC 5322`: https://tools.ietf.org/html/rfc5322
.. _`PhpStormProtocol`: https://github.com/aik099/PhpStormProtocol
.. _`phpstorm-url-handler`: https://github.com/sanduhrs/phpstorm-url-handler
.. _`blue/green deployment`: https://martinfowler.com/bliki/BlueGreenDeployment.html
.. _`gulp-rev`: https://www.npmjs.com/package/gulp-rev
.. _`webpack-manifest-plugin`: https://www.npmjs.com/package/webpack-manifest-plugin
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
