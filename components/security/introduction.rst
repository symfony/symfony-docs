.. index::
   single: Security

The Security Component
======================


    The Security component provides a complete security system for your
    application. It's flexible enough to solve the most basic security needs
    (form login or HTTP basic/digest), but also to serve as a complex security
    system (X.509 certificate or custom authentication systems). Furthermore,
    the component provides ways to authorize authenticated users based on their
    roles or using an advanced ACL system.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/security`` on Packagist_);
* Use the official Git repository (https://github.com/symfony/security).

.. include:: /components/require_autoload.rst.inc

.. _components-security-terminology:

Security Terminology
--------------------

Security is a quite complex task and has some terminology that you won't often
find in other type of programming libraries. That's why it's good to make sure
you know some of the terms used in the Security component before starting to
dive into it.

**Authentication**
    This is the first of the two phases in the Security component. During this
    phase, the security tries to answer one question: **Who are you?** The
    answer to this question might be retrieved from a session or from a token,
    to name some examples.

**Authorization**
    This is the second phase and it tries to answer another question: **Are you
    allowed to access this?** The security system knows who you are and what
    you are allowed to do, now it has to decide if you fit the requirements for
    the requested action. E.g. do you have admin rights, when trying to access
    the admin dashboard.

**Token**
    The token is the central object in the Security system. It contains all
    information that the security system has to know. For instance, if you
    login, it contains the submitted username and password. After you've logged
    in, it contains your rights.

**Roles**
    This is another naming for rights. It tells your "role" in the application
    (e.g. user, admin or moderator, etc.).

Usage
-----

Now you know some terms, you already have a very global overview of the flow in
the Security component (be aware that this code is *not* working in the current
state)::

    // The authentication manager answers *Who are you?*
    $authenticationManager = ...;

    // Keeps track of the token during the lifetime of the token
    $tokenStorage = ...;
    
    // The authenticator transforms an unauthenticated token into an
    // authenticated one
    $authenticatedToken = $authenticationManager->authenticate(new Token(...));

    $tokenStorage->setToken($authenticatedToken);

    // The authorization checker is the access point of the Authorization phase
    $authorizationChecker = ...;

    // This code checks if admin is your role in the application
    if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
        throw new AccessDeniedException();
    }

    // ... show something secret, all Security checks are done!

The Security component itself consists of a couple of sub-components, with Core
and Http being the two big ones. Besides this, it contains a sub-components for
CSRF protection and ACL (Access Control Lists) systems.

The Security Core component contains the actual Security system, decoupled from
possible access points (e.g. web request). This means it's usable in every
application (e.g. a CLI application or a web application).

The Security Http component is a wrapper around the Core component, providing
integration into "HTTP-land" using the
:doc:`HttpFoundation component </components/http_foundation/introduction>`.

In the next chapters, you'll learn more about these 2 sub-components.

Chapters
--------

* :doc:`/components/security/core/authentication`
* :doc:`/components/security/core/authorization`
* :doc:`/components/security/http`
* :doc:`/components/security/secure_tools`

.. _Packagist: https://packagist.org/packages/symfony/security
