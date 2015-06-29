.. index::
   single: Security; Best Practices

How to Secure a Symfony Application
===================================

This article summarizes the steps you should follow to secure your Symfony
applications.

Configuration
-------------

Configure the ``secret`` Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The value of this option is used to increase the entropy in several security
related operations. Make sure to change its default value (usually defined in
the ``app/config/parameters.yml`` file). It's even recommended to change its
value from time to time.

Read the :ref:`framework.secret documentation <configuration-framework-secret>`

Configure a Strong Password Encryption
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

`Bcrypt`_ is the most recommended password hash algorithm for modern web
applications. That's why Symfony only recommends to :ref:`use the bcrypt encoder <reference-security-bcrypt>`
even for in-memory users defined in the ``app/config/security.yml`` file.

Make your Application HTTPS-only
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application defines some protected areas under the ``access_control``
configuration option in ``app/config/security.yml``, use the ``requires_channel``
option as explained in :doc:`this article </cookbook/security/force_https>`.

If you prefer to protect some specific routes, define the ``schemes`` route option
for all those routes as explained in :doc:`this article </cookbook/routing/scheme>`.

Enable or Disabled the CSRF Protection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Symfony forms include a protection against `CSRF attacks`_. In case
you need to :ref:`disable this protection for some form <forms-csrf>`, use the
``csrf_protection`` option. You can even :ref:`disable CSRF protection <reference-csrf_protection-enabled>`
application-wide.

Furthermore, the form used to log in users doesn't include any CSRF protection.
Read :doc:`this article </cookbook/security/csrf_in_login_form>` to learn how to
enable CSRF protection for login forms.

Deployment
----------

Remove Non-Production Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony's development front controller (``web/app_dev.php``) includes a built-in
protection to avoid leaking sensitive information in production servers. However,
when developing custom front controllers is common to forget to add this protection.
That's why your deployment script or tool should delete any front controller
different from ``web/app.php``.

Monitoring
----------

Log Security Events in a Separate File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For some applications it may be useful to log any security-related event into a
separate log file. Read :ref:`this article <logging-channel-handler>` to learn
how to create that ``security.log`` file.

Check Dependencies Vulnerabilities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.5
    The ``security:check`` command was introduced in Symfony 2.5. This command is
    included in ``SensioDistributionBundle``, which has to be registered in your
    application in order to use this command.

When using lots of dependencies in your Symfony projects, some of them may
contain security vulnerabilities. That's why Symfony includes a command called
``security:check`` that checks your ``composer.lock`` file to find any known
security vulnerability in your installed dependencies:

.. code-block:: bash

    $ php app/console security:check

A good security practice is to execute this command regularly to be able to
update or replace compromised dependencies as soon as possible. Internally,
this command uses the public `security advisories database`_ published by the
FriendsOfPHP organization.

.. tip::

    The ``security:check`` command terminates with a non-zero exit code if
    any of your dependencies is affected by a known security vulnerability.
    Therefore, you can easily integrate it in your build process.

Additional Resources
--------------------

Community Bundles
~~~~~~~~~~~~~~~~~

`NelmioSecurityBundle`_
    It provides additional security features for Symfony applications, such as
    signed and encrypted cookies, clickjacking protection, flexible HTTPS/SSL
    handling, etc.

Documentation
~~~~~~~~~~~~~

* :doc:`SecurityBundle Configuration Reference </reference/configuration/security>`.
* :doc:`Framework Configuration Reference </reference/configuration/framework>`
  (it describes the purpose of security-related options such as ``secret`` and
  ``csrf_protection``).
* :doc:`Symfony Security Tutorials </cookbook/security/index>`.

.. _`Bcrypt`: https://en.wikipedia.org/wiki/Bcrypt
.. _`CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
.. _`NelmioSecurityBundle`: https://github.com/nelmio/NelmioSecurityBundle
