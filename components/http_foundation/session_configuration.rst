.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Configuring Sessions and Save Handlers
======================================

This section deals with how to configure session management and fine tune it
to your specific needs. This documentation covers save handlers, which
store and retrieve session data, and configuring session behaviour.

Save Handlers
~~~~~~~~~~~~~

The PHP session workflow has 6 possible operations that may occur.  The normal
session follows `open`, `read`, `write` and `close`, with the possibility of
`destroy` and `gc` (garbage collection which will expire any old sessions: `gc`
is called randomly according to PHP's configuration and if called, it is invoked
after the `open` operation).  You can read more about this at
`php.net/session.customhandler`_


Native PHP Save Handlers
------------------------

So-called 'native' handlers, are save handlers which are either compiled into
PHP or provided by PHP extensions, such as PHP-Sqlite, PHP-Memcached and so on.

All native save handlers are internal to PHP and as such, have no public facing API.
They must be configured by PHP ini directives, usually ``session.save_path`` and
potentially other driver specific directives. Specific details can be found in
docblock of the ``setOptions()`` method of each class.

While native save handlers can be activated by directly using
``ini_set('session.save_handler', $name);``, Symfony2 provides a convenient way to
activate these in the same way as custom handlers.

Symfony2 provides drivers for the following native save handler as an example:

  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeFileSessionHandler`

Example usage::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

    $storage = new NativeSessionStorage(array(), new NativeFileSessionHandler());
    $session = new Session($storage);

.. note::

    With the exception of the ``files`` handler which is built into PHP and always available,
    the availability of the other handlers depends on those PHP extensions being active at runtime.

.. note::

    Native save handlers provide a quick solution to session storage, however, in complex systems
    where you need more control, custom save handlers may provide more freedom and flexibility.
    Symfony2 provides several implementations which you may further customise as required.


Custom Save Handlers
--------------------

Custom handlers are those which completely replace PHP's built in session save
handlers by providing six callback functions which PHP calls internally at
various points in the session workflow.

Symfony2 HttpFoundation provides some by default and these can easily serve as
examples if you wish to write your own.

  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcacheSessionHandler`
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcachedSessionHandler`
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NullSessionHandler`

Example usage::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

    $storage = new NativeSessionStorage(array(), new PdoSessionHandler());
    $session = new Session($storage);


Configuring PHP Sessions
~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
can configure most of the PHP ini configuration directives which are documented
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
    with an expiry time of ``time()``+``cookie_lifetime`` where the time is taken
    from the server.

Configuring Garbage Collection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a session opens, PHP will call the ``gc`` handler randomly according to the
probability set by ``session.gc_probability`` / ``session.gc_divisor``. For
example if these were set to ``5/100`` respectively, it would mean a probability
of 5%. Similarly, ``3/4`` would mean a 3 in 4 chance of being called, i.e. 75%.

If the garbage collection handler is invoked, PHP will pass the value stored in
the PHP ini directive ``session.gc_maxlifetime``. The meaning in this context is
that any stored session that was saved more than ``maxlifetime`` ago should be
deleted. This allows one to expire records based on idle time.

You can configure these settings by passing ``gc_probability``, ``gc_divisor``
and ``gc_maxlifetime`` in an array to the constructor of
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
or to the :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage::setOptions`
method.

Session Lifetime
~~~~~~~~~~~~~~~~

When a new session is created, meaning Symfony2 issues a new session cookie
to the client, the cookie will be stamped with an expiry time. This is
calculated by adding the PHP runtime configuration value in
``session.cookie_lifetime`` with the current server time.

.. note::

    PHP will only issue a cookie once. The client is expected to store that cookie
    for the entire lifetime. A new cookie will only be issued when the session is
    destroyed, the browser cookie is deleted, or the session ID is regenerated
    using the ``migrate()`` or ``invalidate()`` methods of the ``Session`` class.

    The initial cookie lifetime can be set by configuring ``NativeSessionStorage``
    using the ``setOptions(array('cookie_lifetime' => 1234))`` method.

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
which runs reasonably frequently. The cookie ``lifetime`` would be set to a
relatively high value, and the garbage collection ``maxlifetime`` would be set
to destroy sessions at whatever the desired idle period is.

The other option is to specifically checking if a session has expired after the
session is started. The session can be destroyed as required. This method of
processing can allow the expiry of sessions to be integrated into the user
experience, for example, by displaying a message.

Symfony2 records some basic meta-data about each session to give you complete
freedom in this area.

Session meta-data
~~~~~~~~~~~~~~~~~

Sessions are decorated with some basic meta-data to enable fine control over the
security settings. The session object has a getter for the meta-data,
:method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getMetadataBag` which
exposes an instance of :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MetadataBag`::

    $session->getMetadataBag()->getCreated();
    $session->getMetadataBag()->getLastUsed();

Both methods return a Unix timestamp (relative to the server).

This meta-data can be used to explicitly expire a session on access, e.g.::

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

PHP 5.4 compatibility
~~~~~~~~~~~~~~~~~~~~~

Since PHP 5.4.0, :phpclass:`SessionHandler` and :phpclass:`SessionHandlerInterface`
are available. Symfony provides forward compatibility for the :phpclass:`SessionHandlerInterface`
so it can be used under PHP 5.3. This greatly improves inter-operability with other
libraries.

:phpclass:`SessionHandler` is a special PHP internal class which exposes native save
handlers to PHP user-space.

In order to provide a solution for those using PHP 5.4, Symfony2 has a special
class called :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeSessionHandler`
which under PHP 5.4, extends from `\SessionHandler` and under PHP 5.3 is just a
empty base class. This provides some interesting opportunities to leverage
PHP 5.4 functionality if it is available.

Save Handler Proxy
~~~~~~~~~~~~~~~~~~

There are two kinds of save handler class proxies which inherit from
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\AbstractProxy`:
they are :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeProxy`
and :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\SessionHandlerProxy`.

:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
automatically injects storage handlers into a save handler proxy unless already
wrapped by one.

:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeProxy`
is used automatically under PHP 5.3 when internal PHP save handlers are specified
using the `Native*SessionHandler` classes, while
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\SessionHandlerProxy`
will be used to wrap any custom save handlers, that implement :phpclass:`SessionHandlerInterface`.

Under PHP 5.4 and above, all session handlers implement :phpclass:`SessionHandlerInterface`
including `Native*SessionHandler` classes which inherit from :phpclass:`SessionHandler`.

The proxy mechanism allows you to get more deeply involved in session save handler
classes. A proxy for example could be used to encrypt any session transaction
without knowledge of the specific save handler.

.. _`php.net/session.customhandler`: http://php.net/session.customhandler
.. _`php.net/session.configuration`: http://php.net/session.configuration
