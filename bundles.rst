.. _page-creation-bundles:

The Bundle System
=================

.. caution::

    In Symfony versions prior to 4.0, it was recommended to organize your own
    application code using bundles. This is :ref:`no longer recommended <best-practice-no-application-bundles>` and bundles
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
        // ...

        // this bundle is enabled only in 'dev'
        Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
        // ...

        // this bundle is enabled only in 'dev' and 'test', so you can't use it in 'prod'
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
        // ...
    ];

.. tip::

    In a default Symfony application that uses :ref:`Symfony Flex <symfony-flex>`,
    bundles are enabled/disabled automatically for you when installing/removing
    them, so you don't need to look at or edit this ``bundles.php`` file.

Creating a Bundle
-----------------

This section creates and enables a new bundle to show there are only a few steps required.
The new bundle is called AcmeBlogBundle, where the ``Acme`` portion is an example
name that should be replaced by some "vendor" name that represents you or your
organization (e.g. AbcBlogBundle for some company named ``Abc``).

Start by creating a new class called ``AcmeBlogBundle``::

    // src/AcmeBlogBundle.php
    namespace Acme\BlogBundle;

    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class AcmeBlogBundle extends AbstractBundle
    {
    }

.. caution::

    If your bundle must be compatible with previous Symfony versions you have to
    extend from the :class:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle` instead.

.. tip::

    The name AcmeBlogBundle follows the standard
    :ref:`Bundle naming conventions <bundles-naming-conventions>`. You could
    also choose to shorten the name of the bundle to simply BlogBundle by naming
    this class BlogBundle (and naming the file ``BlogBundle.php``).

This empty class is the only piece you need to create the new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle. Now that you've created the bundle, enable it::

    // config/bundles.php
    return [
        // ...
        Acme\BlogBundle\AcmeBlogBundle::class => ['all' => true],
    ];

And while it doesn't do anything yet, AcmeBlogBundle is now ready to be used.

.. _bundles-directory-structure:

Bundle Directory Structure
--------------------------

The directory structure of a bundle is meant to help to keep code consistent
between all Symfony bundles. It follows a set of conventions, but is flexible
to be adjusted if needed:

``assets/``
    Contains the web asset sources like JavaScript and TypeScript files, CSS and
    Sass files, but also images and other assets related to the bundle that are
    not in ``public/`` (e.g. Stimulus controllers).

``config/``
    Houses configuration, including routing configuration (e.g. ``routes.php``).

``public/``
    Contains web assets (images, compiled CSS and JavaScript files, etc.) and is
    copied or symbolically linked into the project ``public/`` directory via the
    ``assets:install`` console command.

``src/``
    Contains all PHP classes related to the bundle logic (e.g. ``Controller/CategoryController.php``).

``templates/``
    Holds templates organized by controller name (e.g. ``category/show.html.twig``).

``tests/``
    Holds all tests for the bundle.

``translations/``
    Holds translations organized by domain and locale (e.g. ``AcmeBlogBundle.en.xlf``).

.. _bundles-legacy-directory-structure:

.. caution::

    The recommended bundle structure was changed in Symfony 5, read the
    `Symfony 4.4 bundle documentation`_ for information about the old
    structure.

    When using the new ``AbstractBundle`` class, the bundle defaults to the
    new structure. Override the ``Bundle::getPath()`` method to change to
    the old structure::

        class AcmeBlogBundle extends AbstractBundle
        {
            public function getPath(): string
            {
                return __DIR__;
            }
        }

.. tip::

    It's recommended to use the `PSR-4`_ autoload standard: use the namespace as key,
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

Learn more
----------

* :doc:`/bundles/override`
* :doc:`/bundles/best_practices`
* :doc:`/bundles/configuration`
* :doc:`/bundles/extension`
* :doc:`/bundles/prepend_extension`

.. _`third-party bundles`: https://github.com/search?q=topic%3Asymfony-bundle&type=Repositories
.. _`Symfony 4.4 bundle documentation`: https://symfony.com/doc/4.4/bundles.html#bundle-directory-structure
.. _`PSR-4`: https://www.php-fig.org/psr/psr-4/
