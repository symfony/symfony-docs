.. _config-framework-session:

session
~~~~~~~

cache_limiter
.............

**type**: ``string`` **default**: ``0``

If set to ``0``, Symfony won't set any particular header related to the cache
and it will rely on ``php.ini``'s `session.cache_limiter`_ directive.

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

Be aware that if you configure it, you'll have to set other session-related options
as parameters as well.

cookie_domain
.............

**type**: ``string``

This determines the domain to set in the session cookie.

If not set, ``php.ini``'s `session.cookie_domain`_ directive will be relied on.

cookie_httponly
...............

**type**: ``boolean`` **default**: ``true``

This determines whether cookies should only be accessible through the HTTP
protocol. This means that the cookie won't be accessible by scripting
languages, such as JavaScript. This setting can effectively help to reduce
identity theft through :ref:`XSS attacks <xss-attacks>`.

cookie_lifetime
...............

**type**: ``integer``

This determines the lifetime of the session - in seconds.
Setting this value to ``0`` means the cookie is valid for
the length of the browser session.

If not set, ``php.ini``'s `session.cookie_lifetime`_ directive will be relied on.

cookie_path
...........

**type**: ``string``

This determines the path to set in the session cookie.

If not set, ``php.ini``'s `session.cookie_path`_ directive will be relied on.

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

* ``null``, use ``php.ini``'s `session.cookie_samesite`_ directive.
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

**type**: ``boolean`` or ``'auto'``

This determines whether cookies should only be sent over secure connections. In
addition to ``true`` and ``false``, there's a special ``'auto'`` value that
means ``true`` for HTTPS requests and ``false`` for HTTP requests.

If not set, ``php.ini``'s `session.cookie_secure`_ directive will be relied on.

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

        return static function (FrameworkConfig $framework): void {
            $framework->session()
                ->enabled(true);
        };

gc_divisor
..........

**type**: ``integer``

See `gc_probability`_.

If not set, ``php.ini``'s `session.gc_divisor`_ directive will be relied on.

gc_maxlifetime
..............

**type**: ``integer``

This determines the number of seconds after which data will be seen as "garbage"
and potentially cleaned up. Garbage collection may occur during session
start and depends on `gc_divisor`_ and `gc_probability`_.

If not set, ``php.ini``'s `session.gc_maxlifetime`_ directive will be relied on.

gc_probability
..............

**type**: ``integer``

This defines the probability that the garbage collector (GC) process is
started on every session initialization. The probability is calculated by
using ``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1%
chance that the GC process will start on each request.

If not set, Symfony will use the value of the `session.gc_probability`_ directive
in the ``php.ini`` configuration file.

.. versionadded:: 7.2

    Relying on ``php.ini``'s directive as default for ``gc_probability`` was
    introduced in Symfony 7.2.

.. _config-framework-session-handler-id:

handler_id
..........

**type**: ``string`` | ``null`` **default**: ``null``

If ``framework.session.save_path`` is not set, the default value of this option
is ``null``, which means to use the session handler configured in php.ini. If the
``framework.session.save_path`` option is set, then Symfony stores sessions using
the native file session handler.

It is possible to :ref:`store sessions in a database <session-database>`,
and also to configure the session handler with a DSN:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # a few possible examples
                handler_id: 'redis://localhost'
                handler_id: '%env(REDIS_URL)%'
                handler_id: '%env(DATABASE_URL)%'
                handler_id: 'file://%kernel.project_dir%/var/sessions'

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
                <!-- a few possible examples -->
                <framework:session enabled="true"
                    handler-id="redis://localhost"
                    handler-id="%env(REDIS_URL)%"
                    handler-id="%env(DATABASE_URL)%"
                    handler-id="file://%kernel.project_dir%/var/sessions"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;
        use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

        return static function (FrameworkConfig $framework): void {
            // ...

            $framework->session()
                // a few possible examples
                ->handlerId('redis://localhost')
                ->handlerId(env('REDIS_URL'))
                ->handlerId(env('DATABASE_URL'))
                ->handlerId('file://%kernel.project_dir%/var/sessions');
        };

.. note::

    Supported DSN protocols are the following:

    * ``file``
    * ``redis``
    * ``rediss`` (Redis over TLS)
    * ``memcached`` (requires :doc:`symfony/cache </components/cache>`)
    * ``pdo_oci`` (requires :doc:`doctrine/dbal </doctrine/dbal>`)
    * ``mssql``
    * ``mysql``
    * ``mysql2``
    * ``pgsql``
    * ``postgres``
    * ``postgresql``
    * ``sqlsrv``
    * ``sqlite``
    * ``sqlite3``

.. _reference-session-metadata-update-threshold:

metadata_update_threshold
.........................

**type**: ``integer`` **default**: ``0``

This is how many seconds to wait between updating/writing the session metadata.
This can be useful if, for some reason, you want to limit the frequency at which
the session persists, instead of doing that on every request.

.. _name:

name
....

**type**: ``string``

This specifies the name of the session cookie.

If not set, ``php.ini``'s `session.name`_ directive will be relied on.

save_path
.........

**type**: ``string`` | ``null`` **default**: ``%kernel.cache_dir%/sessions``

This option defines the directory where session files are stored when using
the native file session handler (i.e. when ``handler_id`` is ``null`` and no
DSN is configured). It overrides PHP's `session.save_path`_ directive.

By default, Symfony stores session files in ``%kernel.cache_dir%/sessions/``.
If you set this option to ``null``, PHP's default path from ``php.ini`` will
be used instead:

.. note::

    When ``handler_id`` is set to a DSN (e.g. ``redis://localhost`` or
    ``file://%kernel.project_dir%/var/sessions``), this option is ignored
    because the storage location is already part of the DSN.

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

        return static function (FrameworkConfig $framework): void {
            $framework->session()
                ->savePath(null);
        };

sid_bits_per_character
......................

**type**: ``integer``

This determines the number of bits in the encoded session ID character. The possible
values are ``4`` (0-9, a-f), ``5`` (0-9, a-v), and ``6`` (0-9, a-z, A-Z, "-", ",").
The more bits results in stronger session ID. ``5`` is recommended value for
most environments.

If not set, ``php.ini``'s `session.sid_bits_per_character`_ directive will be relied on.

.. deprecated:: 7.2

    The ``sid_bits_per_character`` option was deprecated in Symfony 7.2. No alternative
    is provided as PHP 8.4 has deprecated the related option.

sid_length
..........

**type**: ``integer``

This determines the length of session ID string, which can be an integer between
``22`` and ``256`` (both inclusive), ``32`` being the recommended value. Longer
session IDs are harder to guess.

If not set, ``php.ini``'s `session.sid_length`_ directive will be relied on.

.. deprecated:: 7.2

    The ``sid_length`` option was deprecated in Symfony 7.2. No alternative is
    provided as PHP 8.4 has deprecated the related option.

.. _storage_id:

storage_factory_id
..................

**type**: ``string`` **default**: ``session.storage.factory.native``

The service ID used for creating the ``SessionStorageInterface`` that stores
the session. This service is available in the Symfony application via the
``session.storage.factory`` service alias. The class has to implement
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageFactoryInterface`.
To see a list of all available storages, run:

.. code-block:: terminal

    $ php bin/console debug:container session.storage.factory.

use_cookies
...........

**type**: ``boolean``

This specifies if the session ID is stored on the client side using cookies or
not.

If not set, ``php.ini``'s `session.use_cookies`_ directive will be relied on.

.. _`CSRF security attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`session.name`: https://www.php.net/manual/en/session.configuration.php#ini.session.name
.. _`session.cookie_lifetime`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
.. _`session.cookie_path`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-path
.. _`session.cache_limiter`: https://www.php.net/manual/en/session.configuration.php#ini.session.cache-limiter
.. _`session.cookie_domain`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-domain
.. _`session.cookie_samesite`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-samesite
.. _`session.cookie_secure`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-secure
.. _`session.gc_divisor`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-divisor
.. _`session.gc_probability`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability
.. _`session.gc_maxlifetime`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
.. _`session.sid_length`: https://www.php.net/manual/en/session.configuration.php#ini.session.sid-length
.. _`session.sid_bits_per_character`: https://www.php.net/manual/en/session.configuration.php#ini.session.sid-bits-per-character
.. _`session.save_path`: https://www.php.net/manual/en/session.configuration.php#ini.session.save-path
.. _`session.use_cookies`: https://www.php.net/manual/en/session.configuration.php#ini.session.use-cookies
