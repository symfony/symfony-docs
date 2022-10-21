.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Configuring Sessions and Save Handlers
======================================

This article deals with how to configure session management and fine tune it
to your specific needs. This documentation covers save handlers, which
store and retrieve session data, and configuring session behavior.

Save Handlers
~~~~~~~~~~~~~

The PHP session workflow has 6 possible operations that may occur. The normal
session follows ``open``, ``read``, ``write`` and ``close``, with the possibility
of ``destroy`` and ``gc`` (garbage collection which will expire any old sessions:
``gc`` is called randomly according to PHP's configuration and if called, it is
invoked after the ``open`` operation). You can read more about this at
`php.net/session.customhandler`_

Native PHP Save Handlers
------------------------

So-called native handlers, are save handlers which are either compiled into
PHP or provided by PHP extensions, such as PHP-SQLite, PHP-Memcached and so on.

All native save handlers are internal to PHP and as such, have no public facing API.
They must be configured by ``php.ini`` directives, usually ``session.save_path`` and
potentially other driver specific directives. Specific details can be found in
the docblock of the ``setOptions()`` method of each class. For instance, the one
provided by the Memcached extension can be found on :phpmethod:`php.net <Memcached::setOptions>`.

While native save handlers can be activated by directly using
``ini_set('session.save_handler', $name);``, Symfony provides a convenient way to
activate these in the same way as it does for custom handlers.

Symfony provides drivers for the following native save handler as an example:

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeFileSessionHandler`

Example usage::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

    $sessionStorage = new NativeSessionStorage([], new NativeFileSessionHandler());
    $session = new Session($sessionStorage);

.. note::

    With the exception of the ``files`` handler which is built into PHP and
    always available, the availability of the other handlers depends on those
    PHP extensions being active at runtime.

.. note::

    Native save handlers provide a quick solution to session storage, however,
    in complex systems where you need more control, custom save handlers may
    provide more freedom and flexibility. Symfony provides several implementations
    which you may further customize as required.

Custom Save Handlers
--------------------

Custom handlers are those which completely replace PHP's built-in session save
handlers by providing six callback functions which PHP calls internally at
various points in the session workflow.

The Symfony HttpFoundation component provides some by default and these can
serve as examples if you wish to write your own.

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`
* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcachedSessionHandler`
* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MigratingSessionHandler`
* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\RedisSessionHandler`
* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`
* :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NullSessionHandler`

Example usage::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

    $pdo = new \PDO(...);
    $sessionStorage = new NativeSessionStorage([], new PdoSessionHandler($pdo));
    $session = new Session($sessionStorage);

Migrating Between Save Handlers
-------------------------------

If your application changes the way sessions are stored, use the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MigratingSessionHandler`
to migrate between old and new save handlers without losing session data.

This is the recommended migration workflow:

#. Switch to the migrating handler, with your new handler as the write-only one.
   The old handler behaves as usual and sessions get written to the new one::

       $sessionStorage = new MigratingSessionHandler($oldSessionStorage, $newSessionStorage);

#. After your session gc period, verify that the data in the new handler is correct.
#. Update the migrating handler to use the old handler as the write-only one, so
   the sessions will now be read from the new handler. This step allows easier rollbacks::

       $sessionStorage = new MigratingSessionHandler($newSessionStorage, $oldSessionStorage);

#. After verifying that the sessions in your application are working, switch
   from the migrating handler to the new handler.

Configuring PHP Sessions
~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
can configure most of the ``php.ini`` configuration directives which are documented
at `php.net/session.configuration`_.

To configure these settings, pass the keys (omitting the initial ``session.`` part
of the key) as a key-value array to the ``$options`` constructor argument.
Or set them via the
:method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage::setOptions`
method.

For the sake of clarity, some key options are explained in this documentation.

Session Cookie Lifetime
~~~~~~~~~~~~~~~~~~~~~~~

For security, session tokens are generally recommended to be sent as session cookies.
You can configure the lifetime of session cookies by specifying the lifetime
(in seconds) using the ``cookie_lifetime`` key in the constructor's ``$options``
argument in :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`.

Setting a ``cookie_lifetime`` to ``0`` will cause the cookie to live only as
long as the browser remains open. Generally, ``cookie_lifetime`` would be set to
a relatively large number of days, weeks or months. It is not uncommon to set
cookies for a year or more depending on the application.

Since session cookies are just a client-side token, they are less important in
controlling the fine details of your security settings which ultimately can only
be securely controlled from the server side.

.. note::

    The ``cookie_lifetime`` setting is the number of seconds the cookie should live
    for, it is not a Unix timestamp. The resulting session cookie will be stamped
    with an expiry time of ``time()`` + ``cookie_lifetime`` where the time is taken
    from the server.

Configuring Garbage Collection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a session opens, PHP will call the ``gc`` handler randomly according to the
probability set by ``session.gc_probability`` / ``session.gc_divisor`` in ``php.ini``.
For example if these were set to ``5/100``, it would mean a probability of 5%.

If the garbage collection handler is invoked, PHP will pass the value of
``session.gc_maxlifetime``, meaning that any stored session that was saved more
than ``gc_maxlifetime`` seconds ago should be deleted. This allows to expire records
based on idle time.

However, some operating systems (e.g. Debian) do their own session handling and set
the ``session.gc_probability`` directive to ``0`` to stop PHP doing garbage
collection. That's why Symfony now overwrites this value to ``1``.

If you wish to use the original value set in your ``php.ini``, add the following
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                gc_probability: null

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:session gc-probability="null"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container) {
            $container->extension('framework', [
                'session' => [
                    'gc_probability' => null,
                ],
            ]);
        };

You can configure these settings by passing ``gc_probability``, ``gc_divisor``
and ``gc_maxlifetime`` in an array to the constructor of
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
or to the :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage::setOptions`
method.

Session Lifetime
~~~~~~~~~~~~~~~~

When a new session is created, meaning Symfony issues a new session cookie
to the client, the cookie will be stamped with an expiry time. This is
calculated by adding the PHP runtime configuration value in
``session.cookie_lifetime`` with the current server time.

.. note::

    PHP will only issue a cookie once. The client is expected to store that cookie
    for the entire lifetime. A new cookie will only be issued when the session is
    destroyed, the browser cookie is deleted, or the session ID is regenerated
    using the ``migrate()`` or ``invalidate()`` methods of the ``Session`` class.

    The initial cookie lifetime can be set by configuring ``NativeSessionStorage``
    using the ``setOptions(['cookie_lifetime' => 1234])`` method.

.. note::

    A cookie lifetime of ``0`` means the cookie expires when the browser is closed.

Session Idle Time/Keep Alive
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are often circumstances where you may want to protect, or minimize
unauthorized use of a session when a user steps away from their terminal while
logged in by destroying the session after a certain period of idle time. For
example, it is common for banking applications to log the user out after just
5 to 10 minutes of inactivity. Setting the cookie lifetime here is not
appropriate because that can be manipulated by the client, so we must do the expiry
on the server side. The easiest way is to implement this via garbage collection
which runs reasonably frequently. The ``cookie_lifetime`` would be set to a
relatively high value, and the garbage collection ``gc_maxlifetime`` would be set
to destroy sessions at whatever the desired idle period is.

The other option is specifically check if a session has expired after the
session is started. The session can be destroyed as required. This method of
processing can allow the expiry of sessions to be integrated into the user
experience, for example, by displaying a message.

Symfony records some basic metadata about each session to give you complete
freedom in this area.

Session Cache Limiting
~~~~~~~~~~~~~~~~~~~~~~

To avoid users seeing stale data, it's common for session-enabled resources to be
sent with headers that disable caching. For this purpose PHP Sessions has the
``sessions.cache_limiter`` option, which determines which headers, if any, will be
sent with the response when the session in started.

Upon construction,
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
sets this global option to ``""`` (send no headers) in case the developer wishes to
use a :class:`Symfony\\Component\\HttpFoundation\\Response` object to manage
response headers.

.. caution::

    If you rely on PHP Sessions to manage HTTP caching, you *must* manually set the
    ``cache_limiter`` option in
    :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
    to a non-empty value.

    For example, you may set it to PHP's default value during construction:

    Example usage::

        use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

        $options['cache_limiter'] = session_cache_limiter();
        $sessionStorage = new NativeSessionStorage($options);

Session Metadata
~~~~~~~~~~~~~~~~

Sessions are decorated with some basic metadata to enable fine control over the
security settings. The session object has a getter for the metadata,
:method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getMetadataBag` which
exposes an instance of :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MetadataBag`::

    $session->getMetadataBag()->getCreated();
    $session->getMetadataBag()->getLastUsed();

Both methods return a Unix timestamp (relative to the server).

This metadata can be used to explicitly expire a session on access, e.g.::

    $session->start();
    if (time() - $session->getMetadataBag()->getLastUsed() > $maxIdleTime) {
        $session->invalidate();
        throw new SessionExpired(); // redirect to expired session page
    }

It is also possible to tell what the ``cookie_lifetime`` was set to for a
particular cookie by reading the ``getLifetime()`` method::

    $session->getMetadataBag()->getLifetime();

The expiry time of the cookie can be determined by adding the created
timestamp and the lifetime.

.. _`php.net/session.customhandler`: https://www.php.net/session.customhandler
.. _`php.net/session.configuration`: https://www.php.net/session.configuration
