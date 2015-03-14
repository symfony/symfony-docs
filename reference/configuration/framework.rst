.. index::
    single: Configuration reference; Framework

FrameworkBundle Configuration ("framework")
===========================================

This reference document is a work in progress. It should be accurate, but
all options are not yet fully covered.

The FrameworkBundle contains most of the "base" framework functionality
and can be configured under the ``framework`` key in your application configuration.
This includes settings related to sessions, translation, forms, validation,
routing and more.

Configuration
-------------

* `secret`_
* `http_method_override`_
* `ide`_
* `test`_
* `default_locale`_
* `trusted_proxies`_
* `csrf_protection`_
    * enabled
    * field_name (deprecated)
* `form`_
    * :ref:`enabled <form-enabled>`
    * csrf_protection
        * :ref:`enabled <csrf-protection-enabled>`
        * `field_name`_
* `session`_
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
* `serializer`_
    * :ref:`enabled<serializer.enabled>`
* `templating`_
    * `assets_base_urls`_
    * `assets_version`_
    * `assets_version_format`_
* `profiler`_
    * `collect`_
    * :ref:`enabled <profiler.enabled>`
* `translator`_
    * :ref:`enabled <translator.enabled>`
    * `fallbacks`_
    * `logging`_
* `property_accessor`_
    * `magic_call`_
    * `throw_exception_on_invalid_index`_
* `validation`_
    * :ref:`enabled <validation-enabled>`
    * `cache`_
    * `enable_annotations`_
    * `translation_domain`_
    * `strict_email`_
    * `api`_

secret
~~~~~~

**type**: ``string`` **required**

This is a string that should be unique to your application. In practice,
it's used for generating the CSRF tokens, but it could be used in any other
context where having a unique string is useful. It becomes the service container
parameter named ``kernel.secret``.

.. _configuration-framework-http_method_override:

http_method_override
~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    The ``http_method_override`` option was introduced in Symfony 2.3.

**type**: ``Boolean`` **default**: ``true``

This determines whether the ``_method`` request parameter is used as the intended
HTTP method on POST requests. If enabled, the
:method:`Request::enableHttpMethodParameterOverride <Symfony\\Component\\HttpFoundation\\Request::enableHttpMethodParameterOverride>`
method gets called automatically. It becomes the service container parameter
named ``kernel.http_method_override``. For more information, see
:doc:`/cookbook/routing/method_parameters`.

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
signs (``%``) must be doubled to escape that character. For example, if you
have installed `PhpStormOpener`_ and use PHPstorm, you will do something like:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            ide: "pstorm://%%f:%%l"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ide="pstorm://%%f:%%l" />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'ide' => 'pstorm://%%f:%%l',
        ));

Of course, since every developer uses a different IDE, it's better to set
this on a system level. This can be done by setting the ``xdebug.file_link_format``
in the ``php.ini`` configuration to the url string. If this configuration value
is set, then the ``ide`` option will be ignored.

.. _reference-framework-test:

test
~~~~

**type**: ``Boolean``

If this configuration parameter is present (and not ``false``), then the
services related to testing your application (e.g. ``test.client``) are loaded.
This setting should be present in your ``test`` environment (usually via
``app/config/config_test.yml``). For more information, see :doc:`/book/testing`.

.. _reference-framework-trusted-proxies:

default_locale
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``en``

The default locale is used if no ``_locale`` routing parameter has been set. It
becomes the service container parameter named ``kernel.default_locale`` and it
is also available with the
:method:`Request::getDefaultLocale <Symfony\\Component\\HttpFoundation\\Request::getDefaultLocale>`
method.

trusted_proxies
~~~~~~~~~~~~~~~

**type**: ``array``

Configures the IP addresses that should be trusted as proxies. For more details,
see :doc:`/cookbook/request/load_balancer_reverse_proxy`.

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

            <framework:config trusted-proxies="192.0.0.1, 10.0.0.0/8" />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_proxies' => array('192.0.0.1', '10.0.0.0/8'),
        ));

.. _reference-framework-form:

form
~~~~

.. _form-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether or not to enable support for the Form component.

If you don't use forms, setting this to ``false`` may increase your application's
performance because less services will be loaded into the container.

If this is activated, the :ref:`validation system <validation-enabled>`
is also enabled automatically.

csrf_protection
~~~~~~~~~~~~~~~

.. _csrf-protection-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` if form support is enabled, ``false``
otherwise

This option can be used to disable CSRF protection on *all* forms. But you
can also :ref:`disable CSRF protection on individual forms <form-disable-csrf>`.

If you're using forms, but want to avoid starting your session (e.g. using
forms in an API-only website), ``csrf_protection`` will need to be set to
``false``.

field_name
..........

**type**: ``string`` **default**: ``"_token"``

The name of the hidden field used to render the :ref:`CSRF token <forms-csrf>`.

session
~~~~~~~

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

**type**: ``Boolean`` **default**: ``false``

This determines whether cookies should only be sent over secure connections.

cookie_httponly
...............

**type**: ``Boolean`` **default**: ``false``

This determines whether cookies should only be accessible through the HTTP protocol.
This means that the cookie won't be accessible by scripting languages, such
as JavaScript. This setting can effectively help to reduce identity theft
through XSS attacks.

gc_probability
..............

**type**: ``integer`` **default**: ``1``

This defines the probability that the garbage collector (GC) process is started
on every session initialization. The probability is calculated by using
``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1% chance
that the GC process will start on each request.

gc_divisor
..........

**type**: ``integer`` **default**: ``100``

See `gc_probability`_.

gc_maxlifetime
..............

**type**: ``integer`` **default**: ``1440``

This determines the number of seconds after which data will be seen as "garbage"
and potentially cleaned up. Garbage collection may occur during session start
and depends on `gc_divisor`_ and `gc_probability`_.

save_path
.........

**type**: ``string`` **default**: ``%kernel.cache.dir%/sessions``

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
                save_path: null

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

.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _serializer.enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the ``serializer`` service or not in the service container.

For more details, see :doc:`/cookbook/serializer`.

templating
~~~~~~~~~~

assets_base_urls
................

**default**: ``{ http: [], ssl: [] }``

This option allows you to define base URLs to be used for assets referenced
from ``http`` and ``ssl`` (``https``) pages. A string value may be provided in
lieu of a single-element array. If multiple base URLs are provided, Symfony
will select one from the collection each time it generates an asset's path.

For your convenience, ``assets_base_urls`` can be set directly with a string or
array of strings, which will be automatically organized into collections of base
URLs for ``http`` and ``https`` requests. If a URL starts with ``https://`` or
is `protocol-relative`_ (i.e. starts with `//`) it will be added to both
collections. URLs starting with ``http://`` will only be added to the
``http`` collection.

.. _ref-framework-assets-version:

assets_version
..............

**type**: ``string``

This option is used to *bust* the cache on assets by globally adding a query
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

It's also possible to set the version value on an asset-by-asset basis (instead
of using the global version - e.g. ``v2`` - set here). See
:ref:`Versioning by Asset <book-templating-version-by-asset>` for details.

You can also control how the query string works via the `assets_version_format`_
option.

assets_version_format
.....................

**type**: ``string`` **default**: ``%%s?%%s``

This specifies a :phpfunction:`sprintf` pattern that will be used with the `assets_version`_
option to construct an asset's path. By default, the pattern adds the asset's
version as a query string. For example, if ``assets_version_format`` is set to
``%%s?version=%%s`` and ``assets_version`` is set to ``5``, the asset's path
would be ``/images/logo.png?version=5``.

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

profiler
~~~~~~~~

.. _profiler.enabled:

enabled
.......

.. versionadded:: 2.2
    The ``enabled`` option was introduced in Symfony 2.2. Prior to Symfony
    2.2, the profiler could only be disabled by omitting the ``framework.profiler``
    configuration entirely.

**type**: ``boolean`` **default**: ``false``

The profiler can be enabled by setting this key to ``true``. When you are
using the Symfony Standard Edition, the profiler is enabled in the ``dev``
and ``test`` environments.

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
and activate the data collectors by hand::

    $profiler->enable();

translator
~~~~~~~~~~

.. _translator.enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether or not to enable the ``translator`` service in the service container.

.. _fallback:

fallbacks
.........

**type**: ``string|array`` **default**: ``array('en')``

.. versionadded:: 2.3.25
    The ``fallbacks`` option was introduced in Symfony 2.3.25. Prior
    to Symfony 2.3.25, it was called ``fallback`` and only allowed one fallback
    language defined as a string.
    Please note that you can still use the old ``fallback`` option if you want
    define only one fallback.

This option is used when the translation key for the current locale wasn't found.

For more details, see :doc:`/book/translation`.

.. _reference-framework-translator-logging:

logging
.......

.. versionadded:: 2.6
    The ``logging`` option was introduced in Symfony 2.6.

**default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

When ``true``, a log entry is made whenever the translator cannot find a translation
for a given key. The logs are made to the ``translation`` channel and at the
``debug`` for level for keys where there is a translation in the fallback
locale and the ``warning`` level if there is no translation to use at all.

property_accessor
~~~~~~~~~~~~~~~~~

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

.. _validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` if :ref:`form support is enabled <form-enabled>`,
``false`` otherwise

Whether or not to enable validation support.

cache
.....

**type**: ``string``

The service that is used to persist class metadata in a cache. The service
has to implement the :class:`Symfony\\Component\\Validator\\Mapping\\Cache\\CacheInterface`.

enable_annotations
..................

**type**: ``Boolean`` **default**: ``false``

If this option is enabled, validation constraints can be defined using annotations.

translation_domain
..................

**type**: ``string`` **default**: ``validators``

The translation domain that is used when translating validation constraint
error messages.

strict_email
............

.. versionadded:: 2.5
    The ``strict_email`` option was introduced in Symfony 2.5.

**type**: ``Boolean`` **default**: ``false``

If this option is enabled, the `egulias/email-validator`_ library will be
used by the :doc:`/reference/constraints/Email` constraint validator. Otherwise,
the validator uses a simple regular expression to validate email addresses.

api
...

.. versionadded:: 2.5
    The ``api`` option was introduced in Symfony 2.5.

**type**: ``string``

Starting with Symfony 2.5, the Validator component introduced a new validation
API. The ``api`` option is used to switch between the different implementations:

``2.4``
    Use the vaidation API that is compatible with older Symfony versions.

``2.5``
    Use the validation API introduced in Symfony 2.5.

``2.5-bc`` or ``auto``
    If you omit a value or set the ``api`` option to ``2.5-bc`` or ``auto``,
    Symfony will use an API implementation that is compatible with both the
    legacy implementation and the ``2.5`` implementation. You have to use
    PHP 5.3.9 or higher to be able to use this implementation.

To capture these logs in the ``prod`` environment, configure a
:doc:`channel handler </cookbook/logging/channels_handlers>` in ``config_prod.yml`` for
the ``translation`` channel and set its ``level`` to ``debug``.

Full default Configuration
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
                field_name:           _token # Deprecated since 2.4, to be removed in 3.0. Use form.csrf_protection.field_name instead

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
                username:
                password:
                lifetime:             86400
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

                # set to true to throw an exception when a parameter does not match the requirements
                # set to false to disable exceptions when a parameter does not match the requirements (and return null instead)
                # set to null to disable parameter checks against requirements
                # 'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production
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
                save_path:            "%kernel.cache_dir%/sessions"

            # serializer configuration
            serializer:
               enabled: false

            # templating configuration
            templating:
                assets_version:       ~
                assets_version_format:  "%%s?%%s"
                hinclude_default_template:  ~
                form:
                    resources:

                        # Default:
                        - FrameworkBundle:Form
                assets_base_urls:
                    http:                 []
                    ssl:                  []
                cache:                ~
                engines:              # Required

                    # Example:
                    - twig
                loaders:              []
                packages:

                    # Prototype
                    name:
                        version:              ~
                        version_format:       "%%s?%%s"
                        base_urls:
                            http:                 []
                            ssl:                  []

            # translator configuration
            translator:
                enabled:              false
                fallbacks:            [en]
                logging:              "%kernel.debug%"

            # validation configuration
            validation:
                enabled:              false
                cache:                ~
                enable_annotations:   false
                translation_domain:   validators

            # annotation configuration
            annotations:
                cache:                file
                file_cache_dir:       "%kernel.cache_dir%/annotations"
                debug:                "%kernel.debug%"

.. _`protocol-relative`: http://tools.ietf.org/html/rfc3986#section-4.2
.. _`PhpStormOpener`: https://github.com/pinepain/PhpStormOpener
.. _`egulias/email-validator`: https://github.com/egulias/EmailValidator
