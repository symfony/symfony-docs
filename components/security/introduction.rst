.. index::
   single: Security

The Security Component
======================

    The Security component provides a complete security system for your web
    application. It ships with facilities for authenticating using HTTP basic
    or digest authentication, interactive form login or X.509 certificate
    login, but also allows you to implement your own authentication strategies.
    Furthermore, the component provides ways to authorize authenticated users
    based on their roles, and it contains an advanced ACL system.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/security`` on Packagist_);
* Use the official Git repository (https://github.com/symfony/security).

.. include:: /components/require_autoload.rst.inc

The Security component is divided into four smaller sub-components which can be
used separately:

``symfony/security-core``
    It provides all the common security features, from authentication to
    authorization and from encoding passwords to loading users.

``symfony/security-http``
    It integrates the core sub-component with the HTTP protocol to handle HTTP
    requests and responses.

``symfony/security-csrf``
    It provides protection against `CSRF attacks`_.

``symfony/security-acl``
    It provides a fine grained permissions mechanism based on Access Control Lists.

Sections
--------

* :doc:`/components/security/firewall`
* :doc:`/components/security/authentication`
* :doc:`/components/security/authorization`
* :doc:`/components/security/secure_tools`

.. _Packagist: https://packagist.org/packages/symfony/security
.. _`CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
