.. index::
    single: Security; Vulnerability Checker

How to Check for Known Security Vulnerabilities in Your Dependencies
====================================================================

When using lots of dependencies in your Symfony projects, some of them may
contain security vulnerabilities. That's why the Symfony client includes a
command called ``security:check`` that checks your ``composer.lock`` file to
find known security vulnerabilities in your installed dependencies:

.. code-block:: terminal

    $ symfony security:check

.. tip::

   The Symfony client is distributed as a free installable binary without any
   dependency and support for Linux, macOS and Windows. Go to `symfony.com/download`_
   and follow the instructions for your operating system.

A good security practice is to execute this command regularly to be able to
update or replace compromised dependencies as soon as possible. Internally,
this command uses the public `security advisories database`_ published by the
FriendsOfPHP organization.

.. tip::

    The ``security:check`` command terminates with a non-zero exit code if
    any of your dependencies is affected by a known security vulnerability.
    This way you can add it to your project build process and your continuous
    integration workflows to make them fail when there are vulnerabilities.

.. tip::

    The security check is done locally: the `security advisories database`_ is
    cloned and your ``composer.lock`` file is not sent on the network.

.. _`symfony.com/download`: https://symfony.com/download
.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
