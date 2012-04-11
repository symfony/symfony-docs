.. index::
   single: HTTP
   single: HttpFoundation
   single: Sessions

HttpFoundation Sessions
-----------------------

The Symfony2 HttpFoundation Component has a very powerful and flexible session
subsystem which is designed to provide session management through a simple
object-oriented interface using a variety of session storage drivers.

.. versionadded:: 2.1
    The :class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface` interface,
    as well as a number of other changes, are new as of Symfony 2.1.

Sessions are used via the simple :class:`Symfony\\Component\\HttpFoundation\\Session\\Session`
implementation of :class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface` interface.

Quick example::

    use Symfony\Component\HttpFoundation\Session\Session;

    $session = new Session();
    $session->start();

    // set and get session attributes
    $session->set('name', 'Drak');
    $session->get('name');

    // set flash messages
    $session->getFlashBag()->add('notice', 'Profile updated');

    // retrieve messages
    foreach ($session->getFlashBag()->get('notice', array()) as $message) {
        echo "<div class='flash-notice'>$message</div>";
    }

Session API
~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpFoundation\\Session\\Session` class implements
:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface`.

The :class:`Symfony\\Component\\HttpFoundation\\Session\\Session` has a simple API
as follows divided into a couple of groups.

Session workflow

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::start`:
  Starts the session - do not use ``session_start()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::migrate`:
  Regenerates the session id - do not use ``session_regenerate_id()``.
  This method can optionally change the lifetime of the new cookie that will
  be emitted by calling this method.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::invalidate`:
  Clears the session data and regenerates the session id do not use ``session_destroy()``.
  This is basically a shortcut for ``clear()`` and ``migrate()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getId`: Gets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::setId`: Sets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getName`: Gets the
  session name.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::setName`: Sets the
  session name.

Session attributes

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::set`:
  Sets an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::get`:
  Gets an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::all`:
  Gets all attributes as an array of key => value;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::has`:
  Returns true if the attribute exists;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::keys`:
  Returns an array of stored attribute keys;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::replace`:
  Sets multiple attributes at once: takes a keyed array and sets each key => value pair.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::remove`:
  Deletes an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::clear`:
  Clear all attributes;

The attributes are stored internally in an "Bag", a PHP object that acts like
an array. A few methods exist for "Bag" management:

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::registerBag`:
  Registers a :class:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface`

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getBag`:
  Gets a :class:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface` by
  bag name.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getFlashBag`:
  Gets the :class:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface`.
  This is just a shortcut for convenience.

Session meta-data

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getMetadataBag`:
  Gets the :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\MetadataBag`
  which contains information about the session.

Save Handlers
~~~~~~~~~~~~~

The PHP session workflow has 6 possible operations that may occur.  The normal
session follows `open`, `read`, `write` and `close`, with the possibility of
`destroy` and `gc` (garbage collection which will expire any old sessions: `gc`
is called randomly according to PHP's configuration and if called, it is invoked
after the `open` operation).  You can read more about this at
`php.net/session.customhandler`_


Native PHP Save Handlers
~~~~~~~~~~~~~~~~~~~~~~~~

So-called 'native' handlers, are session handlers which are either compiled into
PHP or provided by PHP extensions, such as PHP-Sqlite, PHP-Memcached and so on.
The handlers are compiled and can be activated directly in PHP using
`ini_set('session.save_handler', $name);` and are usually configured with
`ini_set('session.save_path', $path);` and sometimes, a variety of other PHP
`ini` directives.

Symfony2 provides drivers for native handlers which are easy to configure, these are:

  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeFileSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeSqliteSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeMemcacheSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeMemcachedSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeRedisSessionHandler`;

Example of use::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeMemcachedSessionHandler;

    $storage = new NativeSessionStorage(array(), new NativeMemcachedSessionHandler());
    $session = new Session($storage);

Custom Save Handlers
~~~~~~~~~~~~~~~~~~~~

Custom handlers are those which completely replace PHP's built in session save
handlers by providing six callback functions which PHP calls internally at
various points in the session workflow.

Symfony2 HttpFoundation provides some by default and these can easily serve as
examples if you wish to write your own.

  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcacheSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcachedSessionHandler`;
  * :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NullSessionHandler`;

Example::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\SessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

    $storage = new NativeSessionStorage(array(), new PdoSessionHandler());
    $session = new Session($storage);

Session Bags
------------

PHP's session management requires the use of the `$_SESSION` super-global,
however, this interferes somewhat with code testability and encapsulation in a
OOP paradigm. To help overcome this, Symfony2 uses 'session bags' linked to the
session to encapsulate a specific dataset of 'attributes' or 'flash messages'.

This approach also mitigates namespace pollution within the `$_SESSION`
super-global because each bag stores all its data under a unique namespace.
This allows Symfony2 to peacefully co-exist with other applications or libraries
that might use the `$_SESSION` super-global and all data remains completely
compatible with Symfony2's session management.

Symfony2 provides 2 kinds of bags, with two separate implementations.
Everything is written against interfaces so you may extend or create your own
bag types if necessary.

:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface` has
the following API which is intended mainly for internal purposes:

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface::getStorageKey`:
  Returns the key which the bag will ultimately store its array under in `$_SESSION`.
  Generally this value can be left at its default and is for internal use.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface::initialize`:
  This is called internally by Symfony2 session storage classes to link bag data
  to the session.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface::getName`:
  Returns the name of the session bag.

Attributes
~~~~~~~~~~

The purpose of the bags implementing the :class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface`
is to handle session attribute storage. This might include things like user ID,
and remember me login settings or other user based state information.

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag`
  This is the standard default implementation.

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\NamespacedAttributeBag`
  This implementation allows for attributes to be stored in a structured namespace.

Any plain `key => value` storage system is limited in the extent to which
complex data can be stored since each key must be unique. You can achieve
namespacing by introducing a naming convention to the keys so different parts of
your application could operate without clashing. For example, `module1.foo` and
`module2.foo`. However, sometimes this is not very practical when the attributes
data is an array, for example a set of tokens. In this case, managing the array
becomes a burden because you have to retrieve the array then process it and
store it again::

    $tokens = array('tokens' => array('a' => 'a6c1e0b6',
                                      'b' => 'f4a7b1f3'));

So any processing of this might quickly get ugly, even simply adding a token to
the array::

    $tokens = $session->get('tokens');
    $tokens['c'] = $value;
    $session->set('tokens', $tokens);

With structured namespacing, the the key can be translated to the array
structure like this using a namespace character (defaults to `/`)::

    $session->set('tokens/c', $value);

This way you can easily access a key within the stored array directly and easily.

:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface`
has a simple API

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::set`:
  Sets an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::get`:
  Gets an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::all`:
  Gets all attributes as an array of key => value;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::has`:
  Returns true if the attribute exists;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::keys`:
  Returns an array of stored attribute keys;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::replace`:
  Sets multiple attributes at once: takes a keyed array and sets each key => value pair.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::remove`:
  Deletes an attribute by key;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBagInterface::clear`:
  Clear the bag;

Flash messages
~~~~~~~~~~~~~~

The purpose of the :class:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface`
is to provide a way of settings and retrieving messages on a per session basis.
The usual workflow for flash messages would be set in an request, and displayed
after a page redirect. For example, a user submits a form which hits an update
controller, and after processing the controller redirects the page to either the
updated page or an error page. Flash messages set in the previous page request
would be displayed immediately on the subsequent page load for that session.
This is however just one application for flash messages.

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\AutoExpireFlashBag`
   This implementation messages set in one page-load will
   be available for display only on the next page load. These messages will auto
   expire regardless of if they are retrieved or not.

* :class:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBag`
   In this implementation, messages will remain in the session until
   they are explicitly retrieved or cleared. This makes it possible to use ESI
   caching.

:class:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface`
has a simple API

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::add`:
  Adds a flash message to the stack of specified type;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::set`:
  Sets flashes by type;  This method conveniently takes both singles messages as
  a ``string`` or multiple messages in an ``array``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::get`:
  Gets flashes by type and clears those flashes from the bag;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::setAll`:
  Sets all flashes, accepts a keyed array of arrays ``type => array(messages)``;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::all`:
  Gets all flashes (as a keyed array of arrays) and clears the flashes from the bag;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::peek`:
  Gets flashes by type (read only);

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::peekAll`:
  Gets all flashes (read only) as keyed array of arrays;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::has`:
  Returns true if the type exists, false if not;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::keys`:
  Returns an array of the stored flash types;

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::clear`:
  Clears the bag;

For simple applications it is usually sufficient to have one flash message per
type, for example a confirmation notice after a form is submitted. However,
flash messages are stored in a keyed array by flash ``$type`` which means your
application can issue multiple messages for a given type. This allows the API
to be used for more complex messaging in your application.

Examples of setting multiple flashes::

    use Symfony\Component\HttpFoundation\Session\Session;

    $session = new Session();
    $session->start();

    // add flash messages
    $session->getFlashBag()->add('warning', 'Your config file is writable, it should be set read-only');
    $session->getFlashBag()->add('error', 'Failed to update name');
    $session->getFlashBag()->add('error', 'Another error');

Displaying the flash messages might look like this:

Simple, display one type of message::

    // display warnings
    foreach ($session->getFlashBag()->get('warning', array()) as $message) {
        echo "<div class='flash-warning'>$message</div>";
    }

    // display errors
    foreach ($session->getFlashBag()->get('error', array()) as $message) {
        echo "<div class='flash-error'>$message</div>";
    }

Compact method to process display all flashes at once::

    foreach ($session->getFlashBag()->all() as $type => $messages) {
        foreach ($messages as $message) {
            echo "<div class='flash-$type'>$message</div>\n";
        }
    }

Testability
-----------

Symfony2 is designed from the ground up with code-testability in mind. In order
to make your code which utilizes session easily testable we provide two separate
mock storage mechanisms for both unit testing and functional testing.

Testing code using real sessions is tricky because PHP's workflow state is global
and it is not possible to have multiple concurrent sessions in the same PHP
process.

The mock storage engines simulate the PHP session workflow without actually
starting one allowing you to test your code without complications. You may also
run multiple instances in the same PHP process.

The mock storage drivers do not read or write the system globals
`session_id()` or `session_name()`. Methods are provided to simulate this if
required:

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionStorageInterface::getId`: Gets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionStorageInterface::setId`: Sets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionStorageInterface::getName`: Gets the
  session name.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionStorageInterface::setName`: Sets the
  session name.

Unit Testing
~~~~~~~~~~~~

For unit testing where it is not necessary to persist the session, you should
simply swap out the default storage engine with
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockArraySessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
    use Symfony\Component\HttpFoundation\Session\Session;

    $session = new Session(new MockArraySessionStorage());

Functional Testing
~~~~~~~~~~~~~~~~~~

For functional testing where you may need to persist session data across
separate PHP processes, simply change the storage engine to
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockFileSessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

    $session = new Session(new MockFileSessionStorage());

PHP 5.4 compatibility
~~~~~~~~~~~~~~~~~~~~~

Since PHP 5.4.0, :phpclass:`SessionHandler` and :phpclass:`SessionHandlerInterface`
are available. Symfony 2.1 provides forward compatibility for the :phpclass:`SessionHandlerInterface`
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

The proxy mechanism allow you to get more deeply involved in session save handler
classes. A proxy for example could be used to encrypt any session transaction
without knowledge of the specific save handler.

Configuring PHP Sessions
~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
can configure most of the PHP ini configuration directives which are documented
at `php.net/session.configuration`_.

To configure these setting, pass the keys (omitting the initial ``session.`` part
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
the PHP ini directive ``session.gc_maxlifetime`. The meaning in this context is
that any stored session that was saved more than ``maxlifetime`` ago should be
deleted. This allows one to expire records based on idle time.

You can configure these settings by passing ``gc_probability``, ``gc_divisor``
and ``gc_maxlifetime`` in an array to the constructor of
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
or to the :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage::setOptions()`
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

    A cookie lifetime of ``0`` means the cookie expire when the browser is closed.

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
    if (time() - $session->getMetadataBag()->getLastUpdate() > $maxIdleTime) {
        $session->invalidate();
        throw new SessionExpired(); // redirect to expired session page
    }

It is also possible to tell what the ``cookie_lifetime`` was set to for a
particular cookie by reading the ``getLifetime()`` method::

    $session->getMetadataBag()->getLifetime();

The expiry time of the cookie can be determined by adding the created
timestamp and the lifetime.

.. _`php.net/session.customhandler`: http://php.net/session.customhandler
.. _`php.net/session.configuration`: http://php.net/session.configuration

Other sections
--------------

* :doc:`/components/http_foundation/http_foundation`
