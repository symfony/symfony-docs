.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Integrating with Legacy Sessions
================================

Sometimes it may be necessary to integrate Symfony into a legacy application
where you do not initially have the level of control you require.

As stated elsewhere, Symfony Sessions are designed to replace the use of
PHP's native ``session_*()`` functions and use of the ``$_SESSION``
superglobal. Additionally, it is mandatory for Symfony to start the session.

However when there really are circumstances where this is not possible, you
can use a special storage bridge
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\PhpBridgeSessionStorage`
which is designed to allow Symfony to work with a session started outside of
the Symfony Session framework. You are warned that things can interrupt this
use-case unless you are careful: for example the legacy application erases
``$_SESSION``.

A typical use of this might look like this::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

    // legacy application configures session
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', '/tmp');
    session_start();

    // Get Symfony to interface with this existing session
    $session = new Session(new PhpBridgeSessionStorage());

    // symfony will now interface with the existing PHP session
    $session->start();

This will allow you to start using the Symfony Session API and allow migration
of your application to Symfony sessions.

.. note::

    Symfony sessions store data like attributes in special 'Bags' which use a
    key in the ``$_SESSION`` superglobal. This means that a Symfony session
    cannot access arbitrary keys in ``$_SESSION`` that may be set by the legacy
    application, although all the ``$_SESSION`` contents will be saved when
    the session is saved.

