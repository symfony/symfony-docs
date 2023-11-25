Best Practices for Reusable Bundles
===================================

This article is all about how to structure your **reusable bundles** to be
configurable and extendable. Reusable bundles are those meant to be shared
privately across many company projects or publicly so any Symfony project can
install them.

.. _bundles-naming-conventions:

Bundle Name
-----------

A bundle is also a PHP namespace. The namespace must follow the `PSR-4`_
interoperability standard for PHP namespaces and class names: it starts with a
vendor segment, followed by zero or more category segments, and it ends with the
namespace short name, which must end with ``Bundle``.

A namespace becomes a bundle as soon as you add "a bundle class" to it (which is
a class that extends :class:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle`).
The bundle class name must follow these rules:

* Use only alphanumeric characters and underscores;
* Use a StudlyCaps name (i.e. camelCase with an uppercase first letter);
* Use a descriptive and short name (no more than two words);
* Prefix the name with the concatenation of the vendor (and optionally the
  category namespaces);
* Suffix the name with ``Bundle``.

Here are some valid bundle namespaces and class names:

==========================  ==================
Namespace                   Bundle Class Name
==========================  ==================
``Acme\Bundle\BlogBundle``  AcmeBlogBundle
``Acme\BlogBundle``         AcmeBlogBundle
==========================  ==================

By convention, the ``getName()`` method of the bundle class should return the
class name.

.. note::

    If you share your bundle publicly, you must use the bundle class name as
    the name of the repository (AcmeBlogBundle and not BlogBundle for instance).

.. note::

    Symfony core Bundles do not prefix the Bundle class with ``Symfony``
    and always add a ``Bundle`` sub-namespace; for example:
    :class:`Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle`.

Each bundle has an alias, which is the lower-cased short version of the bundle
name using underscores (``acme_blog`` for AcmeBlogBundle). This alias
is used to enforce uniqueness within a project and for defining bundle's
configuration options (see below for some usage examples).

Directory Structure
-------------------

The following is the recommended directory structure of an AcmeBlogBundle:

.. code-block:: text

    <your-bundle>/
    ├── assets/
    ├── config/
    ├── docs/
    │   └─ index.md
    ├── public/
    ├── src/
    │   ├── Controller/
    │   ├── DependencyInjection/
    │   └── AcmeBlogBundle.php
    ├── templates/
    ├── tests/
    ├── translations/
    ├── LICENSE
    └── README.md

This directory structure requires to configure the bundle path to its root
directory as follows::

    class AcmeBlogBundle extends Bundle
    {
        public function getPath(): string
        {
            return \dirname(__DIR__);
        }
    }

**The following files are mandatory**, because they ensure a structure convention
that automated tools can rely on:

* ``src/AcmeBlogBundle.php``: This is the class that transforms a plain directory
  into a Symfony bundle (change this to your bundle's name);
* ``README.md``: This file contains the basic description of the bundle and it
  usually shows some basic examples and links to its full documentation (it
  can use any of the markup formats supported by GitHub, such as ``README.rst``);
* ``LICENSE``: The full contents of the license used by the code. Most third-party
  bundles are published under the MIT license, but you can `choose any license`_;
* ``docs/index.md``: The root file for the Bundle documentation.

The depth of subdirectories should be kept to a minimum for the most used
classes and files. Two levels is the maximum.

The bundle directory is read-only. If you need to write temporary files, store
them under the ``cache/`` or ``log/`` directory of the host application. Tools
can generate files in the bundle directory structure, but only if the generated
files are going to be part of the repository.

The following classes and files have specific emplacements (some are mandatory
and others are just conventions followed by most developers):

===================================================  ========================================
Type                                                 Directory
===================================================  ========================================
Commands                                             ``src/Command/``
Controllers                                          ``src/Controller/``
Service Container Extensions                         ``src/DependencyInjection/``
Doctrine ORM entities                                ``src/Entity/``
Doctrine ODM documents                               ``src/Document/``
Event Listeners                                      ``src/EventListener/``
Configuration (routes, services, etc.)               ``config/``
Web Assets (compiled CSS and JS, images)             ``public/``
Web Asset sources (``.scss``, ``.ts``, Stimulus)     ``assets/``
Translation files                                    ``translations/``
Validation (when not using attributes)               ``config/validation/``
Serialization (when not using attributes)            ``config/serialization/``
Templates                                            ``templates/``
Unit and Functional Tests                            ``tests/``
===================================================  ========================================

Classes
-------

The bundle directory structure is used as the namespace hierarchy. For
instance, a ``ContentController`` controller which is stored in
``src/Controller/ContentController.php`` would have the fully
qualified class name of ``Acme\BlogBundle\Controller\ContentController``.

All classes and files must follow the :doc:`Symfony coding standards </contributing/code/standards>`.

Some classes should be seen as facades and should be as short as possible, like
Commands, Helpers, Listeners and Controllers.

Classes that connect to the event dispatcher should be suffixed with
``Listener``.

Exception classes should be stored in an ``Exception`` sub-namespace.

Vendors
-------

A bundle must not embed third-party PHP libraries. It should rely on the
standard Symfony autoloading instead.

A bundle should also not embed third-party libraries written in JavaScript,
CSS or any other language.

Doctrine Entities/Documents
---------------------------

If the bundle includes Doctrine ORM entities and/or ODM documents, it's
recommended to define their mapping using XML files stored in
``config/doctrine/``. This allows to override that mapping using the
:doc:`standard Symfony mechanism to override bundle parts </bundles/override>`.
This is not possible when using attributes to define the mapping.

Tests
-----

A bundle should come with a test suite written with PHPUnit and stored under
the ``tests/`` directory. Tests should follow the following principles:

* The test suite must be executable with a simple ``phpunit`` command run from
  a sample application;
* The functional tests should only be used to test the response output and
  some profiling information if you have some;
* The tests should cover at least 95% of the code base.

.. note::

    A test suite must not contain ``AllTests.php`` scripts, but must rely on the
    existence of a ``phpunit.xml.dist`` file.

Continuous Integration
----------------------

Testing bundle code continuously, including all its commits and pull requests,
is a good practice called Continuous Integration. There are several services
providing this feature for free for open source projects, like `GitHub Actions`_
and `Travis CI`_.

A bundle should at least test:

* The lower bound of their dependencies (by running ``composer update --prefer-lowest``);
* The supported PHP versions;
* All supported major Symfony versions (e.g. both ``4.x`` and ``5.x`` if
  support is claimed for both).

Thus, a bundle supporting PHP 7.3, 7.4 and 8.0, and Symfony 4.4 and 5.x should
have at least this test matrix:

===========  ===============  ===================
PHP version  Symfony version  Composer flags
===========  ===============  ===================
7.3          ``4.*``          ``--prefer-lowest``
7.4          ``5.*``
8.0          ``5.*``
===========  ===============  ===================

.. tip::

    The tests should be run with the ``SYMFONY_DEPRECATIONS_HELPER``
    env variable set to ``max[direct]=0``. This ensures no code in the
    bundle uses deprecated features directly.

    The lowest dependency tests can be run with this variable set to
    ``disabled=1``.

Require a Specific Symfony Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the special ``SYMFONY_REQUIRE`` environment variable together
with Symfony Flex to install a specific Symfony version:

.. code-block:: bash

    # this requires Symfony 5.x for all Symfony packages
    export SYMFONY_REQUIRE=5.*
    # alternatively you can run this command to update composer.json config
    # composer config extra.symfony.require "5.*"

    # install Symfony Flex in the CI environment
    composer global config --no-plugins allow-plugins.symfony/flex true
    composer global require --no-progress --no-scripts --no-plugins symfony/flex

    # install the dependencies (using --prefer-dist and --no-progress is
    # recommended to have a better output and faster download time)
    composer update --prefer-dist --no-progress

.. caution::

    If you want to cache your Composer dependencies, **do not** cache the
    ``vendor/`` directory as this has side-effects. Instead cache
    ``$HOME/.composer/cache/files``.

Installation
------------

Bundles should set ``"type": "symfony-bundle"`` in their ``composer.json`` file.
With this, :ref:`Symfony Flex <symfony-flex>` will be able to automatically
enable your bundle when it's installed.

If your bundle requires any setup (e.g. configuration, new files, changes to
``.gitignore``), then you should create a `Symfony Flex recipe`_.

Documentation
-------------

All classes and functions must come with full PHPDoc.

Extensive documentation should also be provided in the ``docs/``
directory.
The index file (for example ``docs/index.rst`` or
``docs/index.md``) is the only mandatory file and must be the entry
point for the documentation. The
:doc:`reStructuredText (rST) </contributing/documentation/format>` is the format
used to render the documentation on the Symfony website.

Installation Instructions
~~~~~~~~~~~~~~~~~~~~~~~~~

In order to ease the installation of third-party bundles, consider using the
following standardized instructions in your ``README.md`` file.

.. configuration-block::

    .. code-block:: markdown

        Installation
        ============

        Make sure Composer is installed globally, as explained in the
        [installation chapter](https://getcomposer.org/doc/00-intro.md)
        of the Composer documentation.

        Applications that use Symfony Flex
        ----------------------------------

        Open a command console, enter your project directory and execute:

        ```console
        $ composer require <package-name>
        ```

        Applications that don't use Symfony Flex
        ----------------------------------------

        ### Step 1: Download the Bundle

        Open a command console, enter your project directory and execute the
        following command to download the latest stable version of this bundle:

        ```console
        $ composer require <package-name>
        ```

        ### Step 2: Enable the Bundle

        Then, enable the bundle by adding it to the list of registered bundles
        in the `config/bundles.php` file of your project:

        ```php
        // config/bundles.php

        return [
            // ...
            <vendor>\<bundle-name>\<bundle-long-name>::class => ['all' => true],
        ];
        ```

    .. code-block:: rst

        Installation
        ============

        Make sure Composer is installed globally, as explained in the
        `installation chapter`_ of the Composer documentation.

        ----------------------------------

        Open a command console, enter your project directory and execute:

        .. code-block:: bash

            $ composer require <package-name>

        Applications that don't use Symfony Flex
        ----------------------------------------

        Step 1: Download the Bundle
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~

        Open a command console, enter your project directory and execute the
        following command to download the latest stable version of this bundle:

        .. code-block:: terminal

            $ composer require <package-name>

        Step 2: Enable the Bundle
        ~~~~~~~~~~~~~~~~~~~~~~~~~

        Then, enable the bundle by adding it to the list of registered bundles
        in the ``config/bundles.php`` file of your project::

            // config/bundles.php
            return [
                // ...
                <vendor>\<bundle-name>\<bundle-long-name>::class => ['all' => true],
            ];

        .. _`installation chapter`: https://getcomposer.org/doc/00-intro.md

The example above assumes that you are installing the latest stable version of
the bundle, where you don't have to provide the package version number
(e.g. ``composer require friendsofsymfony/user-bundle``). If the installation
instructions refer to some past bundle version or to some unstable version,
include the version constraint (e.g. ``composer require friendsofsymfony/user-bundle "~2.0@dev"``).

Optionally, you can add more installation steps (*Step 3*, *Step 4*, etc.) to
explain other required installation tasks, such as registering routes or
dumping assets.

Routing
-------

If the bundle provides routes, they must be prefixed with the bundle alias.
For example, if your bundle is called AcmeBlogBundle, all its routes must be
prefixed with ``acme_blog_``.

Templates
---------

If a bundle provides templates, they must use Twig. A bundle must not provide
a main layout, except if it provides a full working application.

Translation Files
-----------------

If a bundle provides message translations, they must be defined in the XLIFF
format; the domain should be named after the bundle name (``acme_blog``).

A bundle must not override existing messages from another bundle.

Configuration
-------------

To provide more flexibility, a bundle can provide configurable settings by
using the Symfony built-in mechanisms.

For simple configuration settings, rely on the default ``parameters`` entry of
the Symfony configuration. Symfony parameters are simple key/value pairs; a
value being any valid PHP value. Each parameter name should start with the
bundle alias, though this is just a best-practice suggestion. The rest of the
parameter name will use a period (``.``) to separate different parts (e.g.
``acme_blog.author.email``).

The end user can provide values in any configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            acme_blog.author.email: 'fabien@example.com'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <parameters>
                <parameter key="acme_blog.author.email">fabien@example.com</parameter>
            </parameters>

        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container): void {
            $container->parameters()
                ->set('acme_blog.author.email', 'fabien@example.com')
            ;
        };

Retrieve the configuration parameters in your code from the container::

    $container->getParameter('acme_blog.author.email');

While this mechanism requires the least effort, you should consider using the
more advanced :doc:`semantic bundle configuration </bundles/configuration>` to
make your configuration more robust.

Versioning
----------

Bundles must be versioned following the `Semantic Versioning Standard`_.

Services
--------

If the bundle defines services, they must be prefixed with the bundle alias
instead of using fully qualified class names like you do in your project
services. For example, AcmeBlogBundle services must be prefixed with ``acme_blog``.
The reason is that bundles shouldn't rely on features such as service autowiring
or autoconfiguration to not impose an overhead when compiling application services.

In addition, services not meant to be used by the application directly, should
be :ref:`defined as private <container-private-services>`. For public services,
:ref:`aliases should be created <service-autowiring-alias>` from the interface/class
to the service id. For example, in MonologBundle, an alias is created from
``Psr\Log\LoggerInterface`` to ``logger`` so that the ``LoggerInterface`` type-hint
can be used for autowiring.

Services should not use autowiring or autoconfiguration. Instead, all services should
be defined explicitly.

.. seealso::

    You can learn much more about service loading in bundles reading this article:
    :doc:`How to Load Service Configuration inside a Bundle </bundles/extension>`.

Composer Metadata
-----------------

The ``composer.json`` file should include at least the following metadata:

``name``
    Consists of the vendor and the short bundle name. If you are releasing the
    bundle on your own instead of on behalf of a company, use your personal name
    (e.g. ``johnsmith/blog-bundle``). Exclude the vendor name from the bundle
    short name and separate each word with a hyphen. For example: AcmeBlogBundle
    is transformed into ``blog-bundle`` and AcmeSocialConnectBundle is
    transformed into ``social-connect-bundle``.

``description``
    A brief explanation of the purpose of the bundle.

``type``
    Use the ``symfony-bundle`` value.

``license``
    a string (or array of strings) with a `valid license identifier`_, such as ``MIT``.

``autoload``
    This information is used by Symfony to load the classes of the bundle. It's
    recommended to use the `PSR-4`_ autoload standard: use the namespace as key,
    and the location of the bundle's main class (relative to ``composer.json``)
    as value. As the main class is located in the ``src/`` directory of the bundle:

    .. code-block:: json

        {
            "autoload": {
                "psr-4": {
                    "Acme\\BlogBundle\\": "src/"
                }
            },
            "autoload-dev": {
                "psr-4": {
                    "Acme\\BlogBundle\\Tests\\": "tests/"
                }
            }
        }

In order to make it easier for developers to find your bundle, register it on
`Packagist`_, the official repository for Composer packages.

Resources
---------

If the bundle references any resources (config files, translation files, etc.),
you can use physical paths (e.g. ``__DIR__/config/services.xml``).

In the past, we recommended to only use logical paths (e.g.
``@AcmeBlogBundle/config/services.xml``) and resolve them with the
:ref:`resource locator <http-kernel-resource-locator>` provided by the Symfony
kernel, but this is no longer a recommended practice.

Learn more
----------

* :doc:`/bundles/extension`
* :doc:`/bundles/configuration`

.. _`PSR-4`: https://www.php-fig.org/psr/psr-4/
.. _`Symfony Flex recipe`: https://github.com/symfony/recipes
.. _`Semantic Versioning Standard`: https://semver.org/
.. _`Packagist`: https://packagist.org/
.. _`choose any license`: https://choosealicense.com/
.. _`valid license identifier`: https://spdx.org/licenses/
.. _`GitHub Actions`: https://docs.github.com/en/free-pro-team@latest/actions
.. _`Travis CI`: https://docs.travis-ci.com/
