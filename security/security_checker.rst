.. index::
    single: Security; Vulnerability Checker

How to Check for Known Security Vulnerabilities in Your Dependencies
====================================================================

When using lots of dependencies in your Symfony projects, some of them may
contain security vulnerabilities. That's why Symfony includes a command called
``security:check`` that checks your ``composer.lock`` file to find any known
security vulnerability in your installed dependencies:

.. code-block:: bash

    $ php bin/console security:check

A good security practice is to execute this command regularly to be able to
update or replace compromised dependencies as soon as possible. Internally,
this command uses the public `security advisories database`_ published by the
FriendsOfPHP organization.

.. tip::

    The ``security:check`` command terminates with a non-zero exit code if
    any of your dependencies is affected by a known security vulnerability.
    Therefore, you can easily integrate it in your build process.

.. note::

    To enable the ``security:check`` command, make sure the
    `SensioDistributionBundle`_ is installed.

    .. code-block:: bash

        $ composer require 'sensio/distribution-bundle'

.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
.. _`SensioDistributionBundle`: https://packagist.org/packages/sensio/distribution-bundle
