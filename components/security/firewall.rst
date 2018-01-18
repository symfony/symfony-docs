.. index::
   single: Security, Firewall

The Firewall and Authorization
==============================

Central to the Security component is authorization. This is handled by an instance
of :class:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface`.
When all steps in the process of authenticating the user have been taken successfully,
you can ask the authorization checker if the authenticated user has access to a
certain action or resource of the application::

    use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    // instance of Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
    $tokenStorage = ...;

    // instance of Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
    $authenticationManager = ...;

    // instance of Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface
    $accessDecisionManager = ...;

    $authorizationChecker = new AuthorizationChecker(
        $tokenStorage,
        $authenticationManager,
        $accessDecisionManager
    );

    // ... authenticate the user

    if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
        throw new AccessDeniedException();
    }

.. note::

    Read the dedicated sections to learn more about :doc:`/components/security/authentication`
    and :doc:`/components/security/authorization`.

.. _firewall:

A Firewall for HTTP Requests
----------------------------

Authenticating a user is done by the firewall. An application may have
multiple secured areas, so the firewall is configured using a map of these
secured areas. For each of these areas, the map contains a request matcher
and a collection of listeners. The request matcher gives the firewall the
ability to find out if the current request points to a secured area.
The listeners are then asked if the current request can be used to authenticate
the user::

    use Symfony\Component\Security\Http\FirewallMap;
    use Symfony\Component\HttpFoundation\RequestMatcher;
    use Symfony\Component\Security\Http\Firewall\ExceptionListener;

    $map = new FirewallMap();

    $requestMatcher = new RequestMatcher('^/secured-area/');

    // instances of Symfony\Component\Security\Http\Firewall\ListenerInterface
    $listeners = array(...);

    $exceptionListener = new ExceptionListener(...);

    $map->add($requestMatcher, $listeners, $exceptionListener);

The firewall map will be given to the firewall as its first argument, together
with the event dispatcher that is used by the :class:`Symfony\\Component\\HttpKernel\\HttpKernel`::

    use Symfony\Component\Security\Http\Firewall;
    use Symfony\Component\HttpKernel\KernelEvents;

    // the EventDispatcher used by the HttpKernel
    $dispatcher = ...;

    $firewall = new Firewall($map, $dispatcher);

    $dispatcher->addListener(
        KernelEvents::REQUEST,
        array($firewall, 'onKernelRequest')
    );

The firewall is registered to listen to the ``kernel.request`` event that
will be dispatched by the HttpKernel at the beginning of each request
it processes. This way, the firewall may prevent the user from going any
further than allowed.

.. _firewall_listeners:

Firewall Listeners
~~~~~~~~~~~~~~~~~~

When the firewall gets notified of the ``kernel.request`` event, it asks
the firewall map if the request matches one of the secured areas. The first
secured area that matches the request will return a set of corresponding
firewall listeners (which each implement :class:`Symfony\\Component\\Security\\Http\\Firewall\\ListenerInterface`).
These listeners will all be asked to handle the current request. This basically
means: find out if the current request contains any information by which
the user might be authenticated (for instance the Basic HTTP authentication
listener checks if the request has a header called ``PHP_AUTH_USER``).

Exception Listener
~~~~~~~~~~~~~~~~~~

If any of the listeners throws an :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`,
the exception listener that was provided when adding secured areas to the
firewall map will jump in.

The exception listener determines what happens next, based on the arguments
it received when it was created. It may start the authentication procedure,
perhaps ask the user to supply their credentials again (when they have only been
authenticated based on a "remember-me" cookie), or transform the exception
into an :class:`Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException`,
which will eventually result in an "HTTP/1.1 403: Access Denied" response.

Entry Points
~~~~~~~~~~~~

When the user is not authenticated at all (i.e. when the token storage
has no token yet), the firewall's entry point will be called to "start"
the authentication process. An entry point should implement
:class:`Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface`,
which has only one method: :method:`Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface::start`.
This method receives the current :class:`Symfony\\Component\\HttpFoundation\\Request`
object and the exception by which the exception listener was triggered.
The method should return a :class:`Symfony\\Component\\HttpFoundation\\Response`
object. This could be, for instance, the page containing the login form or,
in the case of Basic HTTP authentication, a response with a ``WWW-Authenticate``
header, which will prompt the user to supply their username and password.

Flow: Firewall, Authentication, Authorization
---------------------------------------------

Hopefully you can now see a little bit about how the "flow" of the security
context works:

#. The Firewall is registered as a listener on the ``kernel.request`` event;
#. At the beginning of the request, the Firewall checks the firewall map
   to see if any firewall should be active for this URL;
#. If a firewall is found in the map for this URL, its listeners are notified;
#. Each listener checks to see if the current request contains any authentication
   information - a listener may (a) authenticate a user, (b) throw an
   ``AuthenticationException``, or (c) do nothing (because there is no
   authentication information on the request);
#. Once a user is authenticated, you'll use :doc:`/components/security/authorization`
   to deny access to certain resources.

Read the next sections to find out more about :doc:`/components/security/authentication`
and :doc:`/components/security/authorization`.
