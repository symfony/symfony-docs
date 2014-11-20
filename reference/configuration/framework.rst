.. index::
    single: Configuration reference; Framework

FrameworkBundle Configuration ("framework")
===========================================

The FrameworkBundle contains most of the "base" framework functionality
and can be configured under the ``framework`` key in your application
configuration. When using XML, you can use the
``http://symfony.com/schema/dic/symfony`` namespace.

This includes settings related to sessions, translation, forms, validation,
routing and more.

.. tip::

   The XSD schema is available at
   ``http://symfony.com/schema/dic/symfony/symfony-1.0.xsd``.

Configuration
-------------

* `secret`_
* `http_method_override`_
* `trusted_proxies`_
* `ide`_
* `test`_
* `default_locale`_
* `trusted_hosts`_
* :ref:`form <reference-framework-form>`
    * :ref:`enabled <reference-form-enabled>`
* `csrf_protection`_
    * :ref:`enabled <reference-form-csrf_protection-enabled>`
    * `field_name`_
* `esi`_
    * :ref:`enabled <reference-esi-enabled>`
* `fragments`_
    * :ref:`enabled <reference-fragments-enabled>`
    * :ref:`path <reference-fragments-path>`
* `profiler`_
    * :ref:`enabled <reference-profiler-enabled>`
    * `collect`_
    * `only_exceptions`_
    * `only_master_requests`_
    * `dsn`_
    * `username`_
    * `password`_
    * `lifetime`_
    * `matcher`_
        * `ip`_
        * :ref:`path <reference-profiler-matcher-path>`
        * `service`_
* `router`_
    * `resource`_
    * `type`_
    * `http_port`_
    * `https_port`_
    * `strict_requirements`_
* `session`_
    * `storage_id`_
    * `handler_id`_
    * `name`_
    * `cookie_lifetime`_
    * `cookie_path`_
    * `cookie_domain`_
    * `cookie_secure`_
    * `cookie_httponly`_
    * `gc_divisor`_
    * `gc_probability`_
    * `gc_maxlifetime`_
    * `save_path`_
* `templating`_
    * `assets_version`_
    * `assets_version_format`_
    * `hinclude_default_template`_
    * :ref:`form <reference-templating-form>`
        * `resources`_
    * `assets_base_urls`_
        * http
        * ssl
    * :ref:`cache <reference-templating-cache>`
    * `engines`_
    * `loaders`_
    * `packages`_
* `translator`_
    * :ref:`enabled <reference-translator-enabled>`
    * `fallback`_
* `validation`_
    * :ref:`enabled <reference-validation-enabled>`
    * :ref:`cache <reference-validation-cache>`
    * `enable_annotations`_
    * `translation_domain`_
* `annotations`_
    * :ref:`cache <reference-annotations-cache>`
    * `file_cache_dir`_
    * `debug`_
* `serializer`_
    * :ref:`enabled <reference-serializer-enabled>`

secret
~~~~~~

**type**: ``string``

This is a string that should be unique to your application. In practice,
it's used for generating the CSRF tokens, but it could be used in any other
context where having a unique string is useful. It becomes the service container
parameter named ``kernel.secret``.

.. _configuration-framework-http_method_override:

http_method_override
~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    The ``http_method_override`` option was introduced in Symfony 2.3.

**type**: ``boolean`` **default**: ``true``

This determines whether the ``_method`` request parameter is used as the intended
HTTP method on POST requests. If enabled, the
:method:`Request::enableHttpMethodParameterOverride <Symfony\\Component\\HttpFoundation\\Request::enableHttpMethodParameterOverride>`
method gets called automatically, unless the application uses a kernel based on
the cache, where you need to invoke that method manually (see
:ref:`symfony2-reverse-proxy` for details).

.. seealso::

    For more information, see :doc:`/cookbook/routing/method_parameters`.

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

**type**: ``array``

Configures the IP addresses that should be trusted as proxies. It becomes the
service container parameter called ``kernel.trusted_proxies``.

.. seealso::

    For more details, see :doc:`/cookbook/request/load_balancer_reverse_proxy`.

.. versionadded:: 2.3
    CIDR notation support was introduced in Symfony 2.3, so you can whitelist whole
    subnets (e.g. ``10.0.0.0/8``, ``fc00::/7``).

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            trusted_proxies:  [192.0.0.1, 10.0.0.0/8]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config trusted-proxies="192.0.0.1, 10.0.0.0/8">
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_proxies' => array('192.0.0.1', '10.0.0.0/8'),
        ));

ide
~~~

**type**: ``string`` **default**: ``null``

If you're using an IDE like TextMate or Mac Vim, then Symfony can turn all
of the file paths in an exception message into a link, which will open that
file in your IDE.

Symfony contains preconfigured urls for some popular IDEs, you can set them
using the following keys:

* ``textmate``
* ``macvim``
* ``emacs``
* ``sublime``

.. versionadded:: 2.3.14
    The ``emacs`` and ``sublime`` editors were introduced in Symfony 2.3.14.

You can also specify a custom url string. If you do this, all percentage
signs (``%``) must be doubled to escape that character. For example, if you use
PHPstorm on the Mac OS platform, you will do something like:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            ide: "phpstorm://open?file=%%f&line=%%l"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ide="phpstorm://open?file=%%f&line=%%l" />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'ide' => 'phpstorm://open?file=%%f&line=%%l',
        ));

Of course, since every developer uses a different IDE, it's better to set
this on a system level. This can be done by setting the ``xdebug.file_link_format``
in the ``php.ini`` configuration to the url string. If this configuration value
is set, then the ``ide`` option will be ignored.

.. _reference-framework-test:

test
~~~~

**type**: ``boolean``

If this configuration parameter is present (and not ``false``), then the
services related to testing your application (e.g. ``test.client``) are loaded.
This setting should be present in your ``test`` environment (usually via
``app/config/config_test.yml``).

.. seealso::

   For more information, see :doc:`/book/testing`.

default_locale
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'en'``

The default locale is used if no ``_locale`` routing parameter has been set. It
becomes the service container parameter named ``kernel.default_locale`` and it
is also available with the
:method:`Request::getDefaultLocale <Symfony\\Component\\HttpFoundation\\Request::getDefaultLocale>`
method.

.. seealso::

    You can read more information about the default locale in
    :ref:`book-translation-default-locale`.

trusted_hosts
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``array()``

A lot of different attacks have been discovered relying on inconsistencies
between the handling of the ``Host`` header by various software (web servers,
reverse proxies, web frameworks, etc.). Basically, everytime the framework is
generating an absolute URL (when sending an email to reset a password for
instance), the host might have been manipulated by an attacker.

.. seealso::

    You can read "`HTTP Host header attacks`_" for more information about these
    kinds of attacks.

The Symfony :method:`Request::getHost()
<Symfony\\Component\\HttpFoundation\\Request:getHost>` method might be
vulnerable to some of these attacks because it depends on the configuration of
your web server. One simple solution to avoid these attacks is to whitelist the
hosts that your Symfony application can respond to. That's the purpose of this
``trusted_hosts`` option. If the incoming request's hostname doesn't match one
in this list, the application won't respond and the user will receive a 500
response.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            trusted_hosts:  ['acme.com', 'acme.org']

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <trusted-host>acme.com</trusted-host>
                <trusted-host>acme.org</trusted-host>
                <!-- ... -->
            </framework>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_hosts' => array('acme.com', 'acme.org'),
        ));

Hosts can also be configured using regular expressions, which make it easier to
respond to any subdomain:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            trusted_hosts:  ['.*\.?acme.com$', '.*\.?acme.org$']

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:trusted-host>.*\.?acme.com$</framework:trusted-host>
                <framework:trusted-host>.*\.?acme.org$</framework:trusted-host>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_hosts' => array('.*\.?acme.com$', '.*\.?acme.org$'),
        ));

In addition, you can also set the trusted hosts in the front controller using
the ``Request::setTrustedHosts()`` method::

    // web/app.php
    Request::setTrustedHosts(array('.*\.?acme.com$', '.*\.?acme.org$'));

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

**type**: ``boolean`` **default**: ``false``

Whether to enable the form services or not in the service container.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. note::

    This will automatically enable the validation.

.. seealso::

    For more details, see :doc:`/book/forms`.

csrf_protection
~~~~~~~~~~~~~~~

.. seealso::

    For more information about CSRF protection in forms, see :ref:`forms-csrf`.

.. _reference-form-csrf_protection-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the CSRF support in forms or not. Setting this option to
``true`` requires the `secret`_ to be set and `session`_ to be enabled. It
becomes the service container parameter named ``form.type_extension.csrf.enabled``.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. seealso::

    Read more about CSRF in :ref:`the Book <forms-csrf>`.

field_name
..........

**type**: ``string`` **default**: ``'_token'``

The name of the hidden field containing the CSRF token in the form. It becomes
the service container parameter named ``form.type_extension.csrf.field_name``.

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

        # app/config/config.yml
        framework:
            esi: true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <esi />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'esi' => true,
        ));

fragments
~~~~~~~~~

.. seealso::

    Learn more about fragments in the
    :ref:`HTTP Cache article <book-http_cache-fragments>`.

.. _reference-fragments-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the fragment listener or not. The fragment listener is used
to render ESI fragments independently of the rest of the page.

This setting is automatically set to ``true`` when one of the child settings is
configured.

.. _reference-fragments-path:

path
....

**type**: ``string`` **default**: ``'/_fragment'``

The path prefix for fragments. The fragment listener will only be executed
when the request starts with this path. It becomes the service container
parameter called ``fragment.path``.

profiler
~~~~~~~~

.. _reference-profiler-enabled:

enabled
.......

.. versionadded:: 2.2
    The ``enabled`` option was introduced in Symfony 2.2. Prior to Symfony 2.2,
    the profiler could only be disabled by omitting the ``framework.profiler``
    configuration entirely.

**type**: ``boolean`` **default**: ``true`` in the ``dev`` and ``test`` environments

The profiler can be enabled by setting this option to ``true``. When you are
using the Symfony Standard Edition, the profiler is enabled in the ``dev``
and ``test`` environments.

.. note::

    The profiler works independently from the Web Developer Toolbar, see the
    :doc:`WebProfilerBundle configuration
    </reference/configuration/web_profiler>` on how to disable/enable it.

collect
.......

.. versionadded:: 2.3
    The ``collect`` option was introduced in Symfony 2.3. Previously, when
    ``profiler.enabled`` was ``false``, the profiler *was* actually enabled,
    but the collectors were disabled. Now, the profiler and the collectors
    can be controlled independently.

**type**: ``boolean`` **default**: ``true``

This option configures the way the profiler behaves when it is enabled. If set
to ``true``, the profiler collects data for all requests. If you want to only
collect information on-demand, you can set the ``collect`` flag to ``false``
and activate the data collectors manually::

    $profiler->enable();

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request. It becomes the service
container parameter called ``profiler_listener.only_exceptions``.

only_master_requests
....................

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled on the master
requests (and not on the subrequests). It becomes the service container
parameter called ``profiler_listener.only_master_requests``.

dsn
...

**type**: ``string`` **default**: ``'file:%kernel.cache_dir%/profiler'``

The DSN where to save the profiling information. The supported drivers (part
before the colon) are:

* file
* sqlite
* mysql
* mongodb
* memcache
* memcached
* redis

It becomes the service container parameter called ``profiler.storage.dsn``.

username
........

**type**: ``string`` **default**: ``''``

When needed, the username for the profiling storage. It becomes the service
container parameter called ``profiler.storage.username``.

password
........

**type**: ``string`` **default**: ``''``

When needed, the password for hte profiling storage. It becomes the service
container parameter called ``profiler.storage.password``.

lifetime
........

**type**: ``integer`` **default**: ``86400``

The lifetime of the profiling storage in seconds. The data will be deleted when
the lifetime is expired. It becomes the service container parameter called
``profiler.storage.lifetime``.


matcher
.......

.. seealso::

    See :doc:`/cookbook/profiler/matchers` for more information about using
    matchers to enable/disable the profiler.

ip
""

**type**: ``string``

If set, the profiler will only be enabled when the current IP address matches.

.. _reference-profiler-matcher-path:

path
""""

**type**: ``string``

If set, the profiler will only be enabled when the current path matches.

service
"""""""

**type**: ``string``

This setting contains the service id of a custom matcher.

router
~~~~~~

resource
........

**type**: ``string`` **required**

Specifies the path to the routes used by the default router.

It becomes the service container parameter called ``router.resource``.

type
....

**type**: ``string``

The type of the resource to hint the loaders about the format. This isn't
needed when you use the default routers with the expected file extensions
(``.xml``, ``.yml`` / ``.yaml``, ``.php``).

http_port
.........

**type**: ``integer`` **default**: ``80``

The port for normal http requests (this is used when matching the scheme).

It becomes the service container parameter called ``router.resource``.

https_port
..........

**type**: ``integer`` **default**: ``443``

The port for https requests (this is used when matching the scheme).

strict_requirements
...................

**type**: ``mixed`` **default**: ``true``

Determines the behaviour when a route matches, but the parameters do not match
the specified requirements for that route. Can be one of:

``true``
    Throw an exception when the requirements are not met;
``false``
    Disable exceptions when the requirements are not met and return ``null``
    instead;
``null``
    Disable checking the requirements (thus, match the route even when the
    requirements don't match).

``false`` is recommended in the development environment, while ``false`` or
``null`` might be preferred in production.

session
~~~~~~~

storage_id
..........

**type**: ``string`` **default**: ``'session.storage.native'``

The service id used for session storage. The ``session.storage`` service alias
will be set to this service id.

handler_id
..........

**type**: ``string`` **default**: ``'session.handler.native_file'``

The service id used for session storage. The ``session.handler`` service alias
will be set to this service id.

You can also set it to ``null``, to default to the handler of your PHP
installation.

name
....

**type**: ``string`` **default**: ``null``

This specifies the name of the session cookie. By default it will use the cookie
name which is defined in the ``php.ini`` with the ``session.name`` directive.

cookie_lifetime
...............

**type**: ``integer`` **default**: ``null``

This determines the lifetime of the session - in seconds. It will use ``null`` by
default, which means ``session.cookie_lifetime`` value from ``php.ini`` will be used.
Setting this value to ``0`` means the cookie is valid for the length of the browser
session.

cookie_path
...........

**type**: ``string`` **default**: ``/``

This determines the path to set in the session cookie. By default it will use ``/``.

cookie_domain
.............

**type**: ``string`` **default**: ``''``

This determines the domain to set in the session cookie. By default it's blank,
meaning the host name of the server which generated the cookie according
to the cookie specification.

cookie_secure
.............

**type**: ``boolean`` **default**: ``false``

This determines whether cookies should only be sent over secure connections.

cookie_httponly
...............

**type**: ``boolean`` **default**: ``false``

This determines whether cookies should only be accessible through the HTTP protocol.
This means that the cookie won't be accessible by scripting languages, such
as JavaScript. This setting can effectively help to reduce identity theft
through XSS attacks.

gc_divisor
..........

**type**: ``integer`` **default**: ``100``

See `gc_probability`_.

gc_probability
..............

**type**: ``integer`` **default**: ``1``

This defines the probability that the garbage collector (GC) process is started
on every session initialization. The probability is calculated by using
``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1% chance
that the GC process will start on each request.

gc_maxlifetime
..............

**type**: ``integer`` **default**: ``1440``

This determines the number of seconds after which data will be seen as "garbage"
and potentially cleaned up. Garbage collection may occur during session start
and depends on `gc_divisor`_ and `gc_probability`_.

save_path
.........

**type**: ``string`` **default**: ``'%kernel.cache.dir%/sessions'``

This determines the argument to be passed to the save handler. If you choose
the default file handler, this is the path where the session files are created.
For more information, see :doc:`/cookbook/session/sessions_directory`.

You can also set this value to the ``save_path`` of your ``php.ini`` by setting
the value to ``null``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                save_path: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session save-path="null" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'save_path' => null,
            ),
        ));

templating
~~~~~~~~~~

.. _ref-framework-assets-version:

assets_version
..............

**type**: ``string``

This option is used to bust the cache on assets by globally adding a query
parameter to all rendered asset paths (e.g. ``/images/logo.png?v2``). This
applies only to assets rendered via the Twig ``asset`` function (or PHP equivalent)
as well as assets rendered with Assetic.

For example, suppose you have the following:

.. configuration-block::

    .. code-block:: html+jinja

        <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

    .. code-block:: php

        <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" alt="Symfony!" />

By default, this will render a path to your image such as ``/images/logo.png``.
Now, activate the ``assets_version`` option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating: { engines: ['twig'], assets_version: v2 }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:templating assets-version="v2">
                <!-- ... -->
                <framework:engine>twig</framework:engine>
            </framework:templating>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'templating'      => array(
                'engines'        => array('twig'),
                'assets_version' => 'v2',
            ),
        ));

Now, the same asset will be rendered as ``/images/logo.png?v2`` If you use
this feature, you **must** manually increment the ``assets_version`` value
before each deployment so that the query parameters change.

.. tip::

    As with all settings, you can use a parameter as value for the
    ``assets_version``. This makes it easier to increment the cache on each
    deployment.

.. _reference-templating-version-format:

assets_version_format
.....................

**type**: ``string`` **default**: ``'%%s?%%s'``

This specifies a :phpfunction:`sprintf` pattern that will be used with the
`assets_version`_ option to construct an asset's path. By default, the pattern
adds the asset's version as a query string. For example, if
``assets_version_format`` is set to ``%%s?version=%%s`` and ``assets_version``
is set to ``5``, the asset's path would be ``/images/logo.png?version=5``.

.. note::

    All percentage signs (``%``) in the format string must be doubled to escape
    the character. Without escaping, values might inadvertently be interpreted
    as :ref:`book-service-container-parameters`.

.. tip::

    Some CDN's do not support cache-busting via query strings, so injecting the
    version into the actual file path is necessary. Thankfully, ``assets_version_format``
    is not limited to producing versioned query strings.

    The pattern receives the asset's original path and version as its first and
    second parameters, respectively. Since the asset's path is one parameter, you
    cannot modify it in-place (e.g. ``/images/logo-v5.png``); however, you can
    prefix the asset's path using a pattern of ``version-%%2$s/%%1$s``, which
    would result in the path ``version-5/images/logo.png``.

    URL rewrite rules could then be used to disregard the version prefix before
    serving the asset. Alternatively, you could copy assets to the appropriate
    version path as part of your deployment process and forgot any URL rewriting.
    The latter option is useful if you would like older asset versions to remain
    accessible at their original URL.

hinclude_default_template
.........................

**type**: ``string`` **default**: ``null``

Sets the content shown during the loading of the fragment or when JavaScript is
disabled. This can be either a template name or the content itself. It becomes
the service container parameter named ``fragment.renderer.hinclude.global_template``.

.. seealso::

    See :ref:`book-templating-hinclude` for more information about hinclude.

.. _reference-templating-form:

form
....

resources
"""""""""

**type**: ``string[]`` **default**: ``['FrameworkBundle:Form']``

A list of all resources for form theming in PHP. If you have custom global form
themes in ``src/WebsiteBundle/Resources/views/Form``, you can configure this like:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            templating:
                form:
                    resources:
                        - 'WebsiteBundle:Form'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>

                <framework:templating>

                    <framework:form>

                        <framework:resource>WebsiteBundle:Form</framework:resource>

                    </framework:form>

                </framework:templating>

            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'templating' => array(
                'form' => array(
                    'resources' => array(
                        'WebsiteBundle:Form'
                    ),
                ),
            ),
        ));

.. note::

    The default form templates from ``FrameworkBundle:Form`` will always be
    included in the form resources.

.. _reference-templating-base-urls:

assets_base_urls
................

**type**: ``{ http: [], ssl: [] }``

This option allows you to define base URLs to be used for assets referenced
from ``http`` and ``ssl`` (``https``) pages. A string value may be provided in
lieu of a single-element array. If multiple base URLs are provided, Symfony2
will select one from the collection each time it generates an asset's path.

For your convenience, ``assets_base_urls`` can be set directly with a string or
array of strings, which will be automatically organized into collections of base
URLs for ``http`` and ``https`` requests. If a URL starts with ``https://`` or
is `protocol-relative`_ (i.e. starts with ``//``), it will be added to both
collections. URLs starting with ``http://`` will only be added to the
``http`` collection.

.. _reference-templating-cache:

cache
.....

**type**: ``string``

The path to the cache directory for templates. When this is not set, caching is
disabled. It becomes the service container parameter named
``templating.loader.cache.path``.

engines
.......

**type**: ``string[]`` / ``string`` **required**

The Templating Engine to use. This can either be a string (when only one engine
is configured) or an array of engines. It becomes the service container
parameter named ``templating.engines``.

At least one engine is required.

loaders
.......

**type**: ``string[]``

An array (or a string when configuring just one loader) of service ids for
templating loaders.

packages
........

You can group assets into packages, to specify different base URLs for them:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating:
                packages:
                    avatars:
                        base_urls: 'http://static_cdn.example.com/avatars'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>

                <framework:templating>

                    <framework:package
                        name="avatars"
                        base-url="http://static_cdn.example.com/avatars">

                </framework:templating>

            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'templating' => array(
                'packages' => array(
                    'avatars' => array(
                        'base_urls' => 'http://static_cdn.example.com/avatars',
                    ),
                ),
            ),
        ));

Now you can use the ``avatars`` package in your templates:

.. configuration-block:: php

    .. code-block:: html+jinja

        <img src="{{ asset('...', 'avatars') }}">

    .. code-block:: html+php

        <img src="<?php echo $view['assets']->getUrl('...', 'avatars') ?>">

Each package can configure the following options:

* :ref:`base_urls <reference-templating-base-urls>`
* :ref:`version <ref-framework-assets-version>`
* :ref:`version_format <reference-templating-version-format>`

translator
~~~~~~~~~~

.. _reference-translator-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether or not to enable the ``translator`` service in the service container.

fallback
........

**type**: ``string`` **default**: ``'en'``

This option is used when the translation key for the current locale wasn't found.

For more details, see :doc:`/book/translation`.

validation
~~~~~~~~~~

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` is ``form`` is enabled, ``false``
otherwise

Whether to enable the validation or not.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. _reference-validation-cache:

cache
.....

**type**: ``string``

This value is used to determine the service that is used to persist class
metadata in a cache. The actual service name is built by prefixing the configured
value with ``validator.mapping.cache.`` (e.g. if the value is ``apc``, the
``validator.mapping.cache.apc`` service will be injected). The service has
to implement the :class:`Symfony\\Component\\Validator\\Mapping\\Cache\\CacheInterface`.

enable_annotations
..................

**type**: ``boolean`` **default**: ``false``

If this option is enabled, validation constraints can be defined using annotations.

translation_domain
..................

**type**: ``string`` **default**: ``'validators'``

The translation domain that is used when translating validation constraint
error messages.

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
debug mode in production.

.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _reference-serializer-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the ``serializer`` service or not in the service container.

For more details, see :doc:`/cookbook/serializer`.

.. _`protocol-relative`: http://tools.ietf.org/html/rfc3986#section-4.2
.. _`HTTP Host header attacks`: http://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: http://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
.. _`Doctrine Cache`: http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html
