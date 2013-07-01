.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Session Management
==================

The Symfony2 HttpFoundation Component has a very powerful and flexible session
subsystem which is designed to provide session management through a simple
object-oriented interface using a variety of session storage drivers.

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

.. note::

    Symfony sessions are designed to replace several native PHP functions.
    Applications should avoid using ``session_start()``, ``session_regenerate_id()``,
    ``session_id()``, ``session_name()``, and ``session_destroy()`` and instead
    use the APIs in the following section.

.. note::

    While it is recommended to explicitly start a session, a sessions will actually
    start on demand, that is, if any session request is made to read/write session
    data.

.. caution::

    Symfony sessions are incompatible with PHP ini directive ``session.auto_start = 1``
    This directive should be turned off in ``php.ini``, in the webserver directives or
    in ``.htaccess``.

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
  Regenerates the session ID - do not use ``session_regenerate_id()``.
  This method can optionally change the lifetime of the new cookie that will
  be emitted by calling this method.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::invalidate`:
  Clears all session data and regenerates session ID. Do not use ``session_destroy()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getId`: Gets the
  session ID. Do not use ``session_id()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::setId`: Sets the
  session ID. Do not use ``session_id()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::getName`: Gets the
  session name. Do not use ``session_name()``.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Session::setName`: Sets the
  session name. Do not use ``session_name()``.

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
  Gets the :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MetadataBag`
  which contains information about the session.

Session Data Management
~~~~~~~~~~~~~~~~~~~~~~~

PHP's session management requires the use of the ``$_SESSION`` super-global,
however, this interferes somewhat with code testability and encapsulation in a
OOP paradigm. To help overcome this, Symfony2 uses 'session bags' linked to the
session to encapsulate a specific dataset of 'attributes' or 'flash messages'.

This approach also mitigates namespace pollution within the ``$_SESSION``
super-global because each bag stores all its data under a unique namespace.
This allows Symfony2 to peacefully co-exist with other applications or libraries
that might use the ``$_SESSION`` super-global and all data remains completely
compatible with Symfony2's session management.

Symfony2 provides 2 kinds of storage bags, with two separate implementations.
Everything is written against interfaces so you may extend or create your own
bag types if necessary.

:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface` has
the following API which is intended mainly for internal purposes:

* :method:`Symfony\\Component\\HttpFoundation\\Session\\SessionBagInterface::getStorageKey`:
  Returns the key which the bag will ultimately store its array under in ``$_SESSION``.
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

With structured namespacing, the key can be translated to the array
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
is to provide a way of setting and retrieving messages on a per session basis.
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
    $session->getFlashBag()->add(
        'warning',
        'Your config file is writable, it should be set read-only'
    );
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
