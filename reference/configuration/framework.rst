.. index::
    single: Configuration reference; Framework

FrameworkBundle Configuration ("framework")
===========================================

The FrameworkBundle contains most of the "base" framework functionality
and can be configured under the ``framework`` key in your application
configuration. When using XML, you must use the
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
* `ide`_
* `test`_
* `default_locale`_
* `trusted_hosts`_
* :ref:`form <reference-framework-form>`
    * :ref:`enabled <reference-form-enabled>`
* `csrf_protection`_
    * :ref:`enabled <reference-csrf_protection-enabled>`
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
    * `matcher`_
        * `ip`_
        * :ref:`path <reference-profiler-matcher-path>`
        * `service`_
* `request`_:
    * `formats`_
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
    * `use_strict_mode`_
    * `save_path`_
    * `metadata_update_threshold`_
* `assets`_
    * `base_path`_
    * `base_urls`_
    * `packages`_
    * `version_strategy`_
    * `version`_
    * `version_format`_
    * `json_manifest_path`_
* `templating`_
    * `hinclude_default_template`_
    * :ref:`form <reference-templating-form>`
        * `resources`_
    * :ref:`cache <reference-templating-cache>`
    * `engines`_
    * `loaders`_
* `translator`_
    * :ref:`enabled <reference-translator-enabled>`
    * `fallbacks`_
    * `logging`_
    * :ref:`paths <reference-translator-paths>`
* `property_access`_
    * `magic_call`_
    * `throw_exception_on_invalid_index`_
* `validation`_
    * :ref:`enabled <reference-validation-enabled>`
    * :ref:`cache <reference-validation-cache>`
    * :ref:`enable_annotations <reference-validation-enable_annotations>`
    * `translation_domain`_
    * `strict_email`_
    * `mapping`_
        * :ref:`paths <reference-validation-mapping-paths>`
* `annotations`_
    * :ref:`cache <reference-annotations-cache>`
    * `file_cache_dir`_
    * `debug`_
* `serializer`_
    * :ref:`enabled <reference-serializer-enabled>`
    * :ref:`cache <reference-serializer-cache>`
    * :ref:`enable_annotations <reference-serializer-enable_annotations>`
    * :ref:`name_converter <reference-serializer-name_converter>`
    * :ref:`circular_reference_handler <reference-serializer-circular_reference_handler>`
* `php_errors`_
    * `log`_
    * `throw`_
* :ref:`cache <reference-cache>`
    * :ref:`app <reference-cache-app>`
    * `system`_
    * `directory`_
    * `default_doctrine_provider`_
    * `default_psr6_provider`_
    * `default_redis_provider`_
    * `default_memcached_provider`_
    * `pools`_
        * :ref:`name <reference-cache-pools-name>`
            * `adapter`_
            * `public`_
            * `default_lifetime`_
            * `provider`_
            * `clearer`_
    * `prefix_seed`_

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

    For more information, see :doc:`/form/action_method`.

.. caution::

    If you're using the :ref:`AppCache Reverse Proxy <symfony2-reverse-proxy>`
    with this option, the kernel will ignore the ``_method`` parameter,
    which could lead to errors.

    To fix this, invoke the ``enableHttpMethodParameterOverride()`` method
    before creating the ``Request`` object::

        // web/app.php

        // ...
        $kernel = new AppCache($kernel);

        Request::enableHttpMethodParameterOverride(); // <-- add this line
        $request = Request::createFromGlobals();
        // ...

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

The ``trusted_proxies`` option was removed in Symfony 3.3. See :doc:`/deployment/proxies`.

ide
~~~

**type**: ``string`` **default**: ``null``

Symfony turns file paths seen in variable dumps and exception messages into
links that open those files right inside your browser. If you prefer to open
those files in your favorite IDE or text editor, set this option to any of the
following values: ``phpstorm``, ``sublime``, ``textmate``, ``macvim`` and ``emacs``.

.. note::

    The ``phpstorm`` option is supported natively by PhpStorm on MacOS,
    Windows requires `PhpStormProtocol`_ and Linux requires `phpstorm-url-handler`_.

If you use another editor, the expected configuration value is a URL template
that contains an ``%f`` placeholder where the file path is expected and ``%l``
placeholder for the line number (percentage signs (``%``) must be escaped by
doubling them to prevent Symfony from interpreting them as container parameters).

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            ide: 'myide://open?url=file://%%f&line=%%l'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ide="myide://open?url=file://%%f&line=%%l" />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'ide' => 'myide://open?url=file://%%f&line=%%l',
        ));

Since every developer uses a different IDE, the recommended way to enable this
feature is to configure it on a system level. This can be done by setting the
``xdebug.file_link_format`` option in your ``php.ini`` configuration file. The
format to use is the same as for the ``framework.ide`` option, but without the
need to escape the percent signs (``%``) by doubling them.

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
    ``>`` as guest-to-host separators::

        // /path/to/guest/.../file will be opened
        // as /path/to/host/.../file on the host
        // and /foo/.../file as /bar/.../file also
        'myide://%f:%l&/path/to/guest/>/path/to/host/&/foo/>/bar/&...'

    .. versionadded:: 3.2
        Guest to host mappings were introduced in Symfony 3.2.

.. _reference-framework-test:

test
~~~~

**type**: ``boolean``

If this configuration setting is present (and not ``false``), then the services
related to testing your application (e.g. ``test.client``) are loaded. This
setting should be present in your ``test`` environment (usually via
``app/config/config_test.yml``).

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

trusted_hosts
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``array()``

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
request's hostname doesn't match one in this list, the application won't
respond and the user will receive a 400 response.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            trusted_hosts:  ['example.com', 'example.org']

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:trusted-host>example.com</framework:trusted-host>
                <framework:trusted-host>example.org</framework:trusted-host>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_hosts' => array('example.com', 'example.org'),
        ));

Hosts can also be configured using regular expressions (e.g.  ``^(.+\.)?example.com$``),
which make it easier to respond to any subdomain.

In addition, you can also set the trusted hosts in the front controller
using the ``Request::setTrustedHosts()`` method::

    // web/app.php
    Request::setTrustedHosts(array('^(.+\.)?example.com$', '^(.+\.)?example.org$'));

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

Whether to enable the form services or not in the service container. If
you don't use forms, setting this to ``false`` may increase your application's
performance because less services will be loaded into the container.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. note::

    This will automatically enable the `validation`_.

.. seealso::

    For more details, see :doc:`/forms`.

csrf_protection
~~~~~~~~~~~~~~~

.. seealso::

    For more information about CSRF protection in forms, see :doc:`/form/csrf_protection`.

.. _reference-csrf_protection-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` if form support is enabled, ``false``
otherwise

This option can be used to disable CSRF protection on *all* forms. But you
can also :ref:`disable CSRF protection on individual forms <form-disable-csrf>`.

If you're using forms, but want to avoid starting your session (e.g. using
forms in an API-only website), ``csrf_protection`` will need to be set to
``false``.

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
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:esi />
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
    :ref:`HTTP Cache article <http_cache-fragments>`.

.. _reference-fragments-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the fragment listener or not. The fragment listener is
used to render ESI fragments independently of the rest of the page.

This setting is automatically set to ``true`` when one of the child settings
is configured.

.. _reference-fragments-path:

path
....

**type**: ``string`` **default**: ``'/_fragment'``

The path prefix for fragments. The fragment listener will only be executed
when the request starts with this path.

profiler
~~~~~~~~

.. _reference-profiler-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

The profiler can be enabled by setting this option to ``true``. When you
are using the Symfony Standard Edition, the profiler is enabled in the ``dev``
and ``test`` environments.

.. note::

    The profiler works independently from the Web Developer Toolbar, see
    the :doc:`WebProfilerBundle configuration </reference/configuration/web_profiler>`
    on how to disable/enable the toolbar.

collect
.......

**type**: ``boolean`` **default**: ``true``

This option configures the way the profiler behaves when it is enabled.
If set to ``true``, the profiler collects data for all requests (unless
you configure otherwise, like a custom `matcher`_). If you want to only
collect information on-demand, you can set the ``collect`` flag to ``false``
and activate the data collectors manually::

    $profiler->enable();

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request.

only_master_requests
....................

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled on the master
requests (and not on the subrequests).

dsn
...

**type**: ``string`` **default**: ``'file:%kernel.cache_dir%/profiler'``

The DSN where to store the profiling information.

.. seealso::

    See :doc:`/profiler/storage` for more information about the
    profiler storage.

matcher
.......

.. caution::

    This option is deprecated since Symfony 3.4 and will be removed in 4.0.

Matcher options are configured to dynamically enable the profiler. For
instance, based on the `ip`_ or :ref:`path <reference-profiler-matcher-path>`.

.. seealso::

    See :doc:`/profiler/matchers` for more information about using
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

        # app/config/config.yml
        framework:
            request:
                formats:
                    jsonp: 'application/javascript'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:request>
                    <framework:format name="jsonp">
                        <framework:mime-type>application/javascript</framework:mime-type>
                    </framework:format>
                </framework:request>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'request' => array(
                'formats' => array(
                    'jsonp' => 'application/javascript',
                ),
            ),
        ));

router
~~~~~~

resource
........

**type**: ``string`` **required**

The path the main routing resource (e.g. a YAML file) that contains the
routes and imports the router should load.

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

https_port
..........

**type**: ``integer`` **default**: ``443``

The port for https requests (this is used when matching the scheme).

strict_requirements
...................

**type**: ``mixed`` **default**: ``true``

Determines the routing generator behaviour. When generating a route that
has specific :doc:`requirements </routing/requirements>`, the generator
can behave differently in case the used parameters do not meet these requirements.

The value can be one of:

``true``
    Throw an exception when the requirements are not met;
``false``
    Disable exceptions when the requirements are not met and return ``null``
    instead;
``null``
    Disable checking the requirements (thus, match the route even when the
    requirements don't match).

``true`` is recommended in the development environment, while ``false``
or ``null`` might be preferred in production.

session
~~~~~~~

storage_id
..........

**type**: ``string`` **default**: ``'session.storage.native'``

The service id used for session storage. The ``session.storage`` service
alias will be set to this service id. This class has to implement
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageInterface`.

handler_id
..........

**type**: ``string`` **default**: ``'session.handler.native_file'``

The service id used for session storage. The ``session.handler`` service
alias will be set to this service id.

You can also set it to ``null``, to default to the handler of your PHP
installation.

.. seealso::

    You can see an example of the usage of this in
    :doc:`/doctrine/pdo_session_storage`.

.. _name:

name
....

**type**: ``string`` **default**: ``null``

This specifies the name of the session cookie. By default it will use the
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

This determines the path to set in the session cookie. By default it will
use ``/``.

cookie_domain
.............

**type**: ``string`` **default**: ``''``

This determines the domain to set in the session cookie. By default it's
blank, meaning the host name of the server which generated the cookie according
to the cookie specification.

cookie_secure
.............

**type**: ``boolean`` **default**: ``false``

This determines whether cookies should only be sent over secure connections.

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

use_strict_mode
...............

**type**: ``boolean`` **default**: ``false``

This specifies whether the session module will use the strict session id mode.
If this mode is enabled, the module does not accept uninitialized session IDs.
If an uninitialized session ID is sent from browser, a new session ID is sent
to browser. Applications are protected from session fixation via session
adoption with strict mode.

save_path
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/sessions``

This determines the argument to be passed to the save handler. If you choose
the default file handler, this is the path where the session files are created.
For more information, see :doc:`/session/sessions_directory`.

You can also set this value to the ``save_path`` of your ``php.ini`` by
setting the value to ``null``:

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
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
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

metadata_update_threshold
.........................

**type**: ``integer`` **default**: ``0``

This is how many seconds to wait between two session metadata updates. It will
also prevent the session handler to write if the session has not changed.

.. seealso::

    You can see an example of the usage of this in
    :doc:`/session/limit_metadata_writes`.

assets
~~~~~~

.. _reference-assets-base-path:

base_path
.........

**type**: ``string``

This option allows you to define a base path to be used for assets:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            assets:
                base_path: '/images'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-path="/images" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'assets' => array(
                'base_path' => '/images',
            ),
        ));

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

        # app/config/config.yml
        framework:
            # ...
            assets:
                base_urls:
                    - 'http://cdn.example.com/'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-url="http://cdn.example.com/" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'assets' => array(
                'base_urls' => array('http://cdn.example.com/'),
            ),
        ));

.. _reference-framework-assets-packages:

packages
........

You can group assets into packages, to specify different base URLs for them:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            assets:
                packages:
                    avatars:
                        base_urls: 'http://static_cdn.example.com/avatars'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets>
                    <framework:package
                        name="avatars"
                        base-url="http://static_cdn.example.com/avatars" />
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'assets' => array(
                'packages' => array(
                    'avatars' => array(
                        'base_urls' => 'http://static_cdn.example.com/avatars',
                    ),
                ),
            ),
        ));

Now you can use the ``avatars`` package in your templates:

.. configuration-block:: php

    .. code-block:: html+twig

        <img src="{{ asset('...', 'avatars') }}">

    .. code-block:: html+php

        <img src="<?php echo $view['assets']->getUrl('...', 'avatars') ?>">

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
equivalent) as well as assets rendered with Assetic.

For example, suppose you have the following:

.. configuration-block::

    .. code-block:: html+twig

        <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

    .. code-block:: php

        <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" alt="Symfony!" />

By default, this will render a path to your image such as ``/images/logo.png``.
Now, activate the ``version`` option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            assets:
                version: 'v2'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version="v2" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'assets' => array(
                'version' => 'v2',
            ),
        ));

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

        # app/config/config.yml
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

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version-strategy="app.asset.my_versioning_strategy">
                    <!-- this package removes any versioning (its assets won't be versioned) -->
                    <framework:package
                        name="foo_package"
                        version="null" />
                    <!-- this package uses its own strategy (the default strategy is ignored) -->
                    <framework:package
                        name="bar_package"
                        version-strategy="app.asset.another_version_strategy" />
                    <!-- this package inherits the default strategy -->
                    <framework:package
                        name="baz_package"
                        base_path="/images" />
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'assets' => array(
                'version_strategy' => 'app.asset.my_versioning_strategy',
                'packages' => array(
                    'foo_package' => array(
                        // this package removes any versioning (its assets won't be versioned)
                        'version' => null,
                    ),
                    'bar_package' => array(
                        // this package uses its own strategy (the default strategy is ignored)
                        'version_strategy' => 'app.asset.another_version_strategy',
                    ),
                    'baz_package' => array(
                        // this package inherits the default strategy
                        'base_path' => '/images',
                    ),
                ),
            ),
        ));

.. note::

    This parameter cannot be set at the same time as ``version`` or ``json_manifest_path``.

.. _reference-assets-json-manifest-path:
.. _reference-templating-json-manifest-path:

json_manifest_path
..................

**type**: ``string`` **default**: ``null``

.. versionadded:: 3.3

    The ``json_manifest_path`` option was introduced in Symfony 3.3.

The file path to a ``manifest.json`` file containing an associative array of asset
names and their respective compiled names. A common cache-busting technique using
a "manifest" file works by writing out assets with a "hash" appended to their
file names (e.g. ``main.ae433f1cb.css``) during a front-end compilation routine.

.. tip::

    Symfony's :ref:`Webpack Encore <frontend-webpack-encore>` supports
    :ref:`outputting hashed assets <encore-long-term-caching>`. Moreover, this
    can be incorporated into many other workflows, including Webpack and
    Gulp using `webpack-manifest-plugin`_ and `gulp-rev`_, respectively.

This option can be set globally for all assets and individually for each asset
package:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
       framework:
            assets:
                # this manifest is applied to every asset (including packages)
                json_manifest_path: "%kernel.project_dir%/web/assets/manifest.json"
                packages:
                    foo_package:
                        # this package uses its own manifest (the default file is ignored)
                        json_manifest_path: "%kernel.project_dir%/web/assets/a_different_manifest.json"
                    bar_package:
                        # this package uses the global manifest (the default file is used)
                        base_path: '/images'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- this manifest is applied to every asset (including packages) -->
                <framework:assets json-manifest-path="%kernel.project_dir%/web/assets/manifest.json">
                    <!-- this package uses its own manifest (the default file is ignored) -->
                    <framework:package
                        name="foo_package"
                        json-manifest-path="%kernel.project_dir%/web/assets/a_different_manifest.json" />
                    <!-- this package uses the global manifest (the default file is used) -->
                    <framework:package
                        name="bar_package"
                        base-path="/images" />
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'assets' => array(
                // this manifest is applied to every asset (including packages)
                'json_manifest_path' => '%kernel.project_dir%/web/assets/manifest.json',
                'packages' => array(
                    'foo_package' => array(
                        // this package uses its own manifest (the default file is ignored)
                        'json_manifest_path' => '%kernel.project_dir%/web/assets/a_different_manifest.json',
                    ),
                    'bar_package' => array(
                        // this package uses the global manifest (the default file is used)
                        'base_path' => '/images',
                    ),
                ),
            ),
        ));

.. note::

    This parameter cannot be set at the same time as ``version`` or ``version_strategy``.
    Additionally, this option cannot be nullified at the package scope if a global manifest
    file is specified.

.. tip::

    If you request an asset that is *not found* in the ``manifest.json`` file, the original -
    *unmodified* - asset path will be returned.

templating
~~~~~~~~~~

hinclude_default_template
.........................

**type**: ``string`` **default**: ``null``

Sets the content shown during the loading of the fragment or when JavaScript
is disabled. This can be either a template name or the content itself.

.. seealso::

    See :doc:`/templating/hinclude` for more information about hinclude.

.. _reference-templating-form:

form
....

resources
"""""""""

**type**: ``string[]`` **default**: ``['FrameworkBundle:Form']``

A list of all resources for form theming in PHP. This setting is not required
if you're using the Twig format for your templates, in that case refer to
:ref:`the form article <forms-theming-twig>`.

Assume you have custom global form themes in
``src/WebsiteBundle/Resources/views/Form``, you can configure this like:

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
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
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

    The default form templates from ``FrameworkBundle:Form`` will always
    be included in the form resources.

.. seealso::

    See :ref:`forms-theming-global` for more information.

.. _reference-templating-cache:

cache
.....

**type**: ``string``

The path to the cache directory for templates. When this is not set, caching
is disabled.

.. note::

    When using Twig templating, the caching is already handled by the
    TwigBundle and doesn't need to be enabled for the FrameworkBundle.

engines
.......

**type**: ``string[]`` / ``string`` **required**

The Templating Engine to use. This can either be a string (when only one
engine is configured) or an array of engines.

At least one engine is required.

loaders
.......

**type**: ``string[]``

An array (or a string when configuring just one loader) of service ids for
templating loaders. Templating loaders are used to find and load templates
from a resource (e.g. a filesystem or database). Templating loaders must
implement :class:`Symfony\\Component\\Templating\\Loader\\LoaderInterface`.

translator
~~~~~~~~~~

.. _reference-translator-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether or not to enable the ``translator`` service in the service container.

.. _fallback:

fallbacks
.........

**type**: ``string|array`` **default**: ``array('en')``

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

.. _reference-translator-paths:

paths
.....

**type**: ``array`` **default**: ``[]``

This option allows to define an array of paths where the component will look
for translation files.

property_access
~~~~~~~~~~~~~~~

magic_call
..........

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __call() method <components-property-access-magic-call>` when
its ``getValue()`` method is called.

throw_exception_on_invalid_index
................................

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid index of an array.

validation
~~~~~~~~~~

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` if :ref:`form support is enabled <reference-form-enabled>`,
``false`` otherwise

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

**type**: ``string`` **default**: ``validators``

The translation domain that is used when translating validation constraint
error messages.

strict_email
............

**type**: ``Boolean`` **default**: ``false``

If this option is enabled, the `egulias/email-validator`_ library will be
used by the :doc:`/reference/constraints/Email` constraint validator. Otherwise,
the validator uses a simple regular expression to validate email addresses.

mapping
.......

.. _reference-validation-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``[]``

This option allows to define an array of paths with files or directories where
the component will look for additional validation files.

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

.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _reference-serializer-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the ``serializer`` service or not in the service container.

.. _reference-serializer-cache:

cache
.....

**type**: ``string``

The service that is used to persist class metadata in a cache. The service
has to implement the ``Doctrine\Common\Cache\Cache`` interface.

.. seealso::

    For more information, see :ref:`serializer-enabling-metadata-cache`.

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

php_errors
~~~~~~~~~~

log
...

.. versionadded:: 3.2
    The ``log`` option was introduced in Symfony 3.2.

**type**: ``boolean`` **default**: ``false``

Use the application logger instead of the PHP logger for logging PHP errors.

throw
.....

.. versionadded:: 3.2
    The ``throw`` option was introduced in Symfony 3.2.

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
ships with multiple adapters: ``apcu``, ``doctrine``, ``system``, ``filesystem``,
``psr6``, ``redis`` and ``memcached``.

.. tip::

    It might be tough to understand at the beginning, so to avoid confusion remember that all pools perform the
    same actions but on different medium given the adapter they are based on. Internally, a pool wraps the definition
    of an adapter.

system
......

**type**: ``string`` **default**: ``cache.adapter.system``

The cache adapter used by the ``cache.system`` service.

directory
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/pools``

The path to the cache directory used by services inheriting from the
``cache.adapter.filesystem`` adapter (including ``cache.app``).

default_doctrine_provider
.........................

**type**: ``string``

The service name to use as your default Doctrine provider. The provider is
available as the ``cache.doctrine`` service.

default_psr6_provider
.....................

**type**: ``string``

The service name to use as your default PSR-6 provider. It is available as
the ``cache.psr6`` service.

default_redis_provider
......................

**type**: ``string`` **default**: ``redis://localhost``

The DSN to use by the Redis provider. The provider is available as the ``cache.redis``
service.

default_memcached_provider
..........................

.. versionadded:: 3.3
    The ``default_memcached_provider`` option was introduced in Symfony 3.3.

**type**: ``string`` **default**: ``memcached://localhost``

The DSN to use by the Memcached provider. The provider is available as the ``cache.memcached``
service.

pools
.....

**type**: ``array``

A list of cache pools to be created by the framework extension.

.. seealso::

    For more information about how pools works, see :ref:`cache pools <component-cache-cache-pools>`.

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

The name of the adapter to use. You could also use your own implementation.

.. note::

    Your service MUST implement the :class:`Psr\\Cache\\CacheItemPoolInterface` interface.

public
""""""

**type**: ``boolean`` **default**: ``false``

Whether your service should be public or not.

default_lifetime
""""""""""""""""

**type**: ``integer``

Default lifetime of your cache items in seconds.

provider
""""""""

**type**: ``string``

The service name to use as provider when the specified adapter needs one.

clearer
"""""""

**type**: ``string``

The cache clearer used to clear your PSR-6 cache.

.. seealso::

    For more information, see :class:`Symfony\\Component\\HttpKernel\\CacheClearer\\Psr6CacheClearer`.

prefix_seed
...........

.. versionadded:: 3.2
    The ``prefix_seed`` option was introduced in Symfony 3.2.

**type**: ``string`` **default**: ``null``

If defined, this value is used as part of the "namespace" generated for the
cache item keys. A common practice is to use the unique name of the application
(e.g. ``symfony.com``) because that prevents naming collisions when deploying
multiple applications into the same path (on different servers) that share the
same cache backend.

It's also useful when using `blue/green deployment`_ strategies and more
generally, when you need to abstract out the actual deployment directory (for
example, when warming caches offline).

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        framework:
            secret:               ~
            http_method_override: true
            trusted_proxies:      []
            ide:                  ~
            test:                 ~
            default_locale:       en

            csrf_protection:
                enabled:              false

            # form configuration
            form:
                enabled:              false
                csrf_protection:
                    enabled:          true
                    field_name:       ~

            # esi configuration
            esi:
                enabled:              false

            # fragments configuration
            fragments:
                enabled:              false
                path:                 /_fragment

            # profiler configuration
            profiler:
                enabled:              false
                collect:              true
                only_exceptions:      false
                only_master_requests: false
                dsn:                  file:%kernel.cache_dir%/profiler
                matcher:
                    ip:                   ~

                    # use the urldecoded format
                    path:                 ~ # Example: ^/path to resource/
                    service:              ~

            # router configuration
            router:
                resource:             ~ # Required
                type:                 ~
                http_port:            80
                https_port:           443

                # * set to true to throw an exception when a parameter does not
                #   match the requirements
                # * set to false to disable exceptions when a parameter does not
                #   match the requirements (and return null instead)
                # * set to null to disable parameter checks against requirements
                #
                # 'true' is the preferred configuration in development mode, while
                # 'false' or 'null' might be preferred in production
                strict_requirements:  true

            # session configuration
            session:
                storage_id:           session.storage.native
                handler_id:           session.handler.native_file
                name:                 ~
                cookie_lifetime:      ~
                cookie_path:          ~
                cookie_domain:        ~
                cookie_secure:        ~
                cookie_httponly:      ~
                gc_divisor:           ~
                gc_probability:       ~
                gc_maxlifetime:       ~
                save_path:            '%kernel.cache_dir%/sessions'

            # serializer configuration
            serializer:
               enabled:                   false
               cache:                      ~
               name_converter:             ~
               circular_reference_handler: ~

            # assets configuration
            assets:
                base_path:          ~
                base_urls:          []
                version:            ~
                version_format:     '%%s?%%s'
                packages:

                    # Prototype
                    name:
                        base_path:            ~
                        base_urls:            []
                        version:              ~
                        version_format:       '%%s?%%s'

            # templating configuration
            templating:
                hinclude_default_template:  ~
                form:
                    resources:

                        # Default:
                        - FrameworkBundle:Form
                cache:                ~
                engines:              # Required

                    # Example:
                    - twig
                loaders:              []

            # translator configuration
            translator:
                enabled:              false
                fallbacks:            [en]
                logging:              "%kernel.debug%"
                paths:                []

            # validation configuration
            validation:
                enabled:              false
                cache:                ~
                enable_annotations:   false
                translation_domain:   validators
                mapping:
                    paths:            []

            # annotation configuration
            annotations:
                cache:                file
                file_cache_dir:       '%kernel.cache_dir%/annotations'
                debug:                '%kernel.debug%'

            # PHP errors handling configuration
            php_errors:
                log:                  false
                throw:                '%kernel.debug%'

            # cache configuration
            cache:
                app: cache.app
                system: cache.system
                directory: '%kernel.cache_dir%/pools'
                default_doctrine_provider: ~
                default_psr6_provider: ~
                default_redis_provider: 'redis://localhost'
                default_memcached_provider: 'memcached://localhost'
                pools:
                    # Prototype
                    name:
                        adapter: cache.app
                        public: false
                        default_lifetime: ~
                        provider: ~
                        clearer: ~

.. _`HTTP Host header attacks`: http://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
.. _`Doctrine Cache`: http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/caching.html
.. _`egulias/email-validator`: https://github.com/egulias/EmailValidator
.. _`PhpStormProtocol`: https://github.com/aik099/PhpStormProtocol
.. _`phpstorm-url-handler`: https://github.com/sanduhrs/phpstorm-url-handler
.. _`blue/green deployment`: http://martinfowler.com/bliki/BlueGreenDeployment.html
.. _`gulp-rev`: https://www.npmjs.com/package/gulp-rev
.. _`webpack-manifest-plugin`: https://www.npmjs.com/package/webpack-manifest-plugin
