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
* `ide`_
* `test`_
* `trust_proxy_headers`_
* `trusted_proxies`_
* `form`_
    * enabled
* `csrf_protection`_
    * enabled
    * field_name
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
* `templating`_
    * `assets_base_urls`_
    * `assets_version`_
    * `assets_version_format`_
* `profiler`_
    * :ref:`enabled <profiler.enabled>`

secret
~~~~~~

**type**: ``string`` **required**

This is a string that should be unique to your application. In practice,
it's used for generating the CSRF tokens, but it could be used in any other
context where having a unique string is useful. It becomes the service container
parameter named ``kernel.secret``.

ide
~~~

**type**: ``string`` **default**: ``null``

If you're using an IDE like TextMate or Mac Vim, then Symfony can turn all
of the file paths in an exception message into a link, which will open that
file in your IDE.

If you use TextMate or Mac Vim, you can simply use one of the following built-in
values:

* ``textmate``
* ``macvim``

You can also specify a custom file link string. If you do this, all percentage
signs (``%``) must be doubled to escape that character. For example, the
full TextMate string would look like this:

.. code-block:: yaml

    framework:
        ide:  "txmt://open?url=file://%%f&line=%%l"

Of course, since every developer uses a different IDE, it's better to set
this on a system level. This can be done by setting the ``xdebug.file_link_format``
``php.ini`` value to the file link string. If this configuration value is set, then
the ``ide`` option does not need to be specified.

.. _reference-framework-test:

test
~~~~

**type**: ``Boolean``

If this configuration parameter is present (and not ``false``), then the
services related to testing your application (e.g. ``test.client``) are loaded.
This setting should be present in your ``test`` environment (usually via
``app/config/config_test.yml``). For more information, see :doc:`/book/testing`.

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

**type**: ``array``

Configures the IP addresses that should be trusted as proxies. For more details,
see :doc:`/components/http_foundation/trusting_proxies`.

.. configuration-block::

    .. code-block:: yaml

        framework:
            trusted_proxies:  [192.0.0.1]

    .. code-block:: xml

        <framework:config trusted-proxies="192.0.0.1">
            <!-- ... -->
        </framework>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            'trusted_proxies' => array('192.0.0.1'),
        ));

trust_proxy_headers
~~~~~~~~~~~~~~~~~~~

.. caution::

    The ``trust_proxy_headers`` option is deprecated and will be removed in
    Symfony 2.3. See `trusted_proxies`_ and :doc:`/components/http_foundation/trusting_proxies`
    for details on how to properly trust proxy data.

**type**: ``Boolean``

Configures if HTTP headers (like ``HTTP_X_FORWARDED_FOR``, ``X_FORWARDED_PROTO``, and
``X_FORWARDED_HOST``) are trusted as an indication for an SSL connection. By default, it is
set to ``false`` and only SSL_HTTPS connections are indicated as secure.

You should enable this setting if your application is behind a reverse proxy.

.. _reference-framework-form:

form
~~~~

csrf_protection
~~~~~~~~~~~~~~~

session
~~~~~~~

name
....

**type**: ``string`` **default**: ``null``

This specifies the name of the session cookie. By default it will use the cookie
name which is defined in the ``php.ini`` with the ``session.name`` directive.

cookie_lifetime
...............

.. versionadded:: 2.1
    This option was formerly known as ``lifetime``

**type**: ``integer`` **default**: ``0``

This determines the lifetime of the session - in seconds. By default it will use
``0``, which means the cookie is valid for the length of the browser session.

cookie_path
...........

.. versionadded:: 2.1
    This option was formerly known as ``path``

**type**: ``string`` **default**: ``/``

This determines the path to set in the session cookie. By default it will use ``/``.

cookie_domain
.............

.. versionadded:: 2.1
    This option was formerly known as ``domain``

**type**: ``string`` **default**: ``''``

This determines the domain to set in the session cookie. By default it's blank,
meaning the host name of the server which generated the cookie according
to the cookie specification.

cookie_secure
.............

.. versionadded:: 2.1
    This option was formerly known as ``secure``

**type**: ``Boolean`` **default**: ``false``

This determines whether cookies should only be sent over secure connections.

cookie_httponly
...............

.. versionadded:: 2.1
    This option was formerly known as ``httponly``

**type**: ``Boolean`` **default**: ``false``

This determines whether cookies should only accessible through the HTTP protocol.
This means that the cookie won't be accessible by scripting languages, such
as JavaScript. This setting can effectively help to reduce identity theft
through XSS attacks.

gc_probability
..............

.. versionadded:: 2.1
    The ``gc_probability`` option is new in version 2.1

**type**: ``integer`` **default**: ``1``

This defines the probability that the garbage collector (GC) process is started
on every session initialization. The probability is calculated by using
``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1% chance
that the GC process will start on each request.

gc_divisor
..........

.. versionadded:: 2.1
    The ``gc_divisor`` option is new in version 2.1

**type**: ``integer`` **default**: ``100``

See `gc_probability`_.

gc_maxlifetime
..............

.. versionadded:: 2.1
    The ``gc_maxlifetime`` option is new in version 2.1

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
        <framework:config>
            <framework:session save-path="null" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'save_path' => null,
            ),
        ));

templating
~~~~~~~~~~

assets_base_urls
................

**default**: ``{ http: [], ssl: [] }``

This option allows you to define base URLs to be used for assets referenced
from ``http`` and ``ssl`` (``https``) pages. A string value may be provided in
lieu of a single-element array. If multiple base URLs are provided, Symfony2
will select one from the collection each time it generates an asset's path.

For your convenience, ``assets_base_urls`` can be set directly with a string or
array of strings, which will be automatically organized into collections of base
URLs for ``http`` and ``https`` requests. If a URL starts with ``https://`` or
is `protocol-relative`_ (i.e. starts with `//`) it will be added to both
collections. URLs starting with ``http://`` will only be added to the
``http`` collection.

.. versionadded:: 2.1
    Unlike most configuration blocks, successive values for ``assets_base_urls``
    will overwrite each other instead of being merged. This behavior was chosen
    because developers will typically define base URL's for each environment.
    Given that most projects tend to inherit configurations
    (e.g. ``config_test.yml`` imports ``config_dev.yml``) and/or share a common
    base configuration (i.e. ``config.yml``), merging could yield a set of base
    URL's for multiple environments.

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
        <framework:templating assets-version="v2">
            <framework:engine id="twig" />
        </framework:templating>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            ...,
            'templating'      => array(
                'engines'        => array('twig'),
                'assets_version' => 'v2',
            ),
        ));

Now, the same asset will be rendered as ``/images/logo.png?v2`` If you use
this feature, you **must** manually increment the ``assets_version`` value
before each deployment so that the query parameters change.

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
    version path as part of your deployment process and forgo any URL rewriting.
    The latter option is useful if you would like older asset versions to remain
    accessible at their original URL.

profiler
~~~~~~~~

.. versionadded:: 2.2
    The ``enabled`` option was added in Symfony 2.2. Previously, the profiler
    could only be disabled by omitting the ``framework.profiler`` configuration
    entirely.

.. _profiler.enabled:

enabled
.......

**default**: ``true`` in the ``dev`` and ``test`` environments

The profiler can be disabled by setting this key to ``false``. In reality,
the profiler still exists, but the data collectors are not activated.

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        framework:
            charset:              ~
            secret:               ~
            trust_proxy_headers:  false
            trusted_proxies:      []
            ide:                  ~
            test:                 ~
            default_locale:       en

            # form configuration
            form:
                enabled:              false
            csrf_protection:
                enabled:              false
                field_name:           _token

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
                only_exceptions:      false
                only_master_requests:  false
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
                # DEPRECATED! Session starts on demand
                auto_start:           false
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
                save_path:            %kernel.cache_dir%/sessions

                # DEPRECATED! Please use: cookie_lifetime
                lifetime:             ~

                # DEPRECATED! Please use: cookie_path
                path:                 ~

                # DEPRECATED! Please use: cookie_domain
                domain:               ~

                # DEPRECATED! Please use: cookie_secure
                secure:               ~

                # DEPRECATED! Please use: cookie_httponly
                httponly:             ~

            # templating configuration
            templating:
                assets_version:       ~
                assets_version_format:  %%s?%%s
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
                        version_format:       %%s?%%s
                        base_urls:
                            http:                 []
                            ssl:                  []

            # translator configuration
            translator:
                enabled:              false
                fallback:             en

            # validation configuration
            validation:
                enabled:              false
                cache:                ~
                enable_annotations:   false
                translation_domain:   validators

            # annotation configuration
            annotations:
                cache:                file
                file_cache_dir:       %kernel.cache_dir%/annotations
                debug:                %kernel.debug%


.. versionadded:: 2.1
    The ```framework.session.auto_start`` setting has been removed in Symfony2.1,
    it will start on demand now.

.. _`protocol-relative`: http://tools.ietf.org/html/rfc3986#section-4.2
