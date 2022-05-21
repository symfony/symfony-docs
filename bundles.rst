.. index::
   single: Bundles

.. _page-creation-bundles:

The Bundle System
=================

.. caution::

    In Symfony versions prior to 4.0, it was recommended to organize your own
    application code using bundles. This is no longer recommended and bundles
    should only be used to share code and features between multiple applications.

A bundle is similar to a plugin in other software, but even better. The core
features of Symfony framework are implemented with bundles (FrameworkBundle,
SecurityBundle, DebugBundle, etc.) They are also used to add new features in
your application via `third-party bundles`_.

Bundles used in your applications must be enabled per
:ref:`environment <configuration-environments>` in the ``config/bundles.php``
file::

    // config/bundles.php
    return [
        // 'all' means that the bundle is enabled for any Symfony environment
        Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
        Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
        Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
        Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
        Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
        Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
        // this bundle is enabled only in 'dev' and 'test', so you can't use it in 'prod'
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    ];

.. tip::

    In a default Symfony application that uses :ref:`Symfony Flex <symfony-flex>`,
    bundles are enabled/disabled automatically for you when installing/removing
    them, so you don't need to look at or edit this ``bundles.php`` file.

Creating a Bundle
-----------------

This section creates and enables a new bundle to show there are only a few steps required.
The new bundle is called AcmeTestBundle, where the ``Acme`` portion is an example
name that should be replaced by some "vendor" name that represents you or your
organization (e.g. ABCTestBundle for some company named ``ABC``).

Start by creating a ``Acme/TestBundle/src/`` directory and adding a new file
called ``AcmeTestBundle.php``::

    // Acme/TestBundle/src/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class AcmeTestBundle extends AbstractBundle
    {
    }

.. versionadded:: 6.1

    The ``AbstractBundle`` was introduced in Symfony 6.1.

.. warning::

    If your bundle must be compatible with previous Symfony versions you have to
    extend from the :class:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle` instead.

.. tip::

    The name AcmeTestBundle follows the standard
    :ref:`Bundle naming conventions <bundles-naming-conventions>`. You could
    also choose to shorten the name of the bundle to simply TestBundle by naming
    this class TestBundle (and naming the file ``TestBundle.php``).

This empty class is the only piece you need to create the new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle. Now that you've created the bundle, enable it::

    // config/bundles.php
    return [
        // ...
        Acme\TestBundle\AcmeTestBundle::class => ['all' => true],
    ];

And while it doesn't do anything yet, AcmeTestBundle is now ready to be used.

Bundle Directory Structure
--------------------------

The directory structure of a bundle is meant to help to keep code consistent
between all Symfony bundles. It follows a set of conventions, but is flexible
to be adjusted if needed:

``src/``
    Contains mainly PHP classes related to the bundle logic (e.g. ``Controller/RandomController.php``).

``config/``
    Houses configuration, including routing configuration (e.g. ``routing.yaml``).

``templates/``
    Holds templates organized by controller name (e.g. ``random/index.html.twig``).

``translations/``
    Holds translations organized by domain and locale (e.g. ``AcmeTestBundle.en.xlf``).

``public/``
    Contains web assets (images, stylesheets, etc) and is copied or symbolically
    linked into the project ``public/`` directory via the ``assets:install`` console
    command.

``tests/``
    Holds all tests for the bundle.

It's recommended to use the `PSR-4`_ autoload standard: use the namespace as key,
and the location of the bundle's main class (relative to ``composer.json``)
as value. As the main class is located in the ``src/`` directory of the bundle:

.. code-block:: json

    {
        "autoload": {
            "psr-4": {
                "Acme\\TestBundle\\": "src/"
            }
        },
        "autoload-dev": {
            "psr-4": {
                "Acme\\TestBundle\\Tests\\": "tests/"
            }
        }
    }

A bundle can be as small or large as the feature it implements. It contains
only the files you need and nothing else.

As you move through the guides, you'll learn how to persist objects to a
database, create and validate forms, create translations for your application,
write tests and much more. Each of these has their own place and role within
the bundle.

Learn more
----------

* :doc:`/bundles/override`
* :doc:`/bundles/best_practices`
* :doc:`/bundles/configuration`
* :doc:`/bundles/extension`
* :doc:`/bundles/prepend_extension`

.. _`third-party bundles`: https://github.com/search?q=topic%3Asymfony-bundle&type=Repositories
.. _`PSR-4`: https://www.php-fig.org/psr/psr-4/
