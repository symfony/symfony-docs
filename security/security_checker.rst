.. index::
    single: Security; Vulnerability Checker

How to Check for Known Security Vulnerabilities in Your Dependencies
====================================================================

When using lots of dependencies in your Symfony projects, some of them may
contain security vulnerabilities. That's why the :doc:`Symfony local server </setup/symfony_server>`
includes a command called ``check:security`` that checks your ``composer.lock``
file to find known security vulnerabilities in your installed dependencies:

.. code-block:: terminal

    $ symfony check:security

A good security practice is to execute this command regularly to be able to
update or replace compromised dependencies as soon as possible. The security
check is done locally by fetching the `security advisories database`_ published
by the FriendsOfPHP organization, so your ``composer.lock`` file is not sent on
the network.

.. tip::

    The ``check:security`` command terminates with a non-zero exit code if
    any of your dependencies is affected by a known security vulnerability.
    This way you can add it to your project build process and your continuous
    integration workflows to make them fail when there are vulnerabilities.

.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
