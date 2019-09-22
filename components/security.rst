.. index::
   single: Security

The Security Component
======================

    The Security component provides a complete security system for your web
    application. It ships with facilities for authenticating using HTTP basic
    authentication, interactive form login or X.509 certificate login, but also
    allows you to implement your own authentication strategies. Furthermore, the
    component provides ways to authorize authenticated users based on their
    roles.

Installation
------------

The Security component is divided into several smaller sub-components which can
be used separately:

``symfony/security-core``
    It provides all the common security features, from authentication to
    authorization and from encoding passwords to loading users.

``symfony/security-http``
    It integrates the core sub-component with the HTTP protocol to handle HTTP
    requests and responses.

``symfony/security-csrf``
    It provides protection against `CSRF attacks`_.

``symfony/security-guard``
    It brings many layers of authentication together, allowing the creation
    of complex authentication systems.

You can install each of them separately in your project:

.. code-block:: terminal

    $ composer require symfony/security-core
    $ composer require symfony/security-http
    $ composer require symfony/security-csrf
    $ composer require symfony/security-guard

.. include:: /components/require_autoload.rst.inc

.. seealso::

    This article explains how to use the Security features as an independent
    component in any PHP application. Read the :doc:`/security` article to learn
    about how to use it in Symfony applications.

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /components/security/*
    /security
    /security/*
    /reference/configuration/security
    /reference/constraints/UserPassword

.. _`CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
