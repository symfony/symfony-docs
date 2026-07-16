.. _page-creation-bundles:

The Bundle System
=================

.. warning::

    In Symfony versions prior to 4.0, it was recommended to organize your own
    application code using bundles. This is :ref:`no longer recommended <best-practice-no-application-bundles>` and bundles
    should only be used to share code and features between multiple applications.

.. screencast::

    Do you prefer video tutorials? Check out the `Symfony Bundle Development screencast series`_.

A bundle is similar to a plugin in other software, but even better. The core
features of Symfony framework are implemented with bundles (FrameworkBundle,
SecurityBundle, DebugBundle, etc.) Bundles are also used to add new features in
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

This section creates and enables a new bundle to show that only a few steps are required.
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

.. warning::

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

.. _bundles-required-bundles:

Requiring Other Bundles
~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``#[RequiredBundle]`` attribute was introduced in Symfony 8.1.

If your bundle needs one or more other bundles to work (e.g. to reuse their
services or compiler passes), add the
:class:`Symfony\\Component\\DependencyInjection\\Kernel\\RequiredBundle`
attribute to your bundle class to declare each dependency::

    // src/AcmeBlogBundle.php
    namespace Acme\BlogBundle;

    use Acme\CoreBundle\AcmeCoreBundle;
    use Acme\MarkdownBundle\AcmeMarkdownBundle;
    use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    #[RequiredBundle(AcmeCoreBundle::class)]
    #[RequiredBundle(AcmeMarkdownBundle::class, ignoreOnInvalid: true)]
    class AcmeBlogBundle extends AbstractBundle
    {
    }

When the kernel initializes the bundles, the required bundles are registered
automatically, right before the bundle that requires them. This way, users of
your bundle only need to enable your bundle in their ``config/bundles.php``
file. Required bundles are resolved recursively (a required bundle can itself
require other bundles) and each bundle is only registered once.

The attribute accepts two arguments:

* ``class``: the bundle class to require;
* ``ignoreOnInvalid``: if set to ``true``, the dependency is silently skipped
  when its class does not exist. Use this for optional dependencies that are
  only loaded when the related package is installed.

Bundles registered this way are enabled in the same environments as the
bundle that requires them. If the application also registers a required
bundle explicitly in ``config/bundles.php``, the explicit configuration (and
its environments) takes precedence.

Symfony itself uses this feature: ``FrameworkBundle`` declares
``#[RequiredBundle(ServicesBundle::class)]`` and
``#[RequiredBundle(ConsoleBundle::class, ignoreOnInvalid: true)]``, so its
console integration is only loaded when the Console component is installed.

.. _bundles-legacy-directory-structure:
.. _bundles-directory-structure:

Bundle Directory Structure
--------------------------

The directory structure of a bundle is meant to help keep code consistent
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

.. tip::

    It's recommended to use the `PSR-4`_ autoload standard on your bundle's
    ``composer.json`` file. Use the namespace as key, and the location of the
    bundle's main class (relative to ``composer.json``) as value. As the main
    class is located in the ``src/`` directory of the bundle:

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

Developing a Reusable Bundle
----------------------------

Bundles are meant to be reusable pieces of code that live independently from
any particular Symfony application. However, a bundle cannot run on its own: it
must be registered inside an application to execute its code.

This can be a bit challenging during development. When working on a bundle in
its own repository, there's no Symfony application around it, so you need a way
to test your changes inside a real application environment.

There are two common approaches to do this, depending on whether your bundle has
already been published or is still under development.

Using a Local Path Repository
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your bundle hasn't been published yet (for example, it's not available on
Packagist), you can point Composer to your local bundle directory from any
Symfony application you use for testing.

Edit the ``composer.json`` file of your application and add this:

.. code-block:: json

    {
        "repositories": [
            {
                "type": "path",
                "url": "/path/to/your/AcmeBlogBundle"
            }
        ],
        "require": {
            "acme/blog-bundle": "*"
        }
    }

Then, in your application, install the bundle as usual:

.. code-block:: terminal

    $ composer require acme/blog-bundle

Composer will create a symbolic link (symlink) to your local bundle directory,
so any change you make in the ``AcmeBlogBundle/`` directory is immediately
visible in the application. You can now enable the bundle in ``config/bundles.php``::

    return [
        // ...
        Acme\BlogBundle\AcmeBlogBundle::class => ['all' => true],
    ];

This setup is ideal during early development because it allows quick iteration
without publishing or rebuilding archives.

Linking an Already Published Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your bundle is already public (for example, it's published on Packagist),
you can still develop it locally while testing it inside a Symfony application.

In your application, replace the installed bundle with a symlink to your local
development copy. For example, if your bundle is installed under
``vendor/acme/blog-bundle/`` and your local copy is in ``~/Projects/AcmeBlogBundle/``:

.. code-block:: terminal

    $ rm -rf vendor/acme/blog-bundle/
    $ ln -s ~/Projects/AcmeBlogBundle/ vendor/acme/blog-bundle

Symfony will now use your local bundle directly. You can edit its code, run
tests, and see the changes immediately. When you're done, restore the vendor
folder or reinstall the package with Composer to go back to the published version.

Learn more
----------

* :doc:`/bundles/override`
* :doc:`/bundles/best_practices`
* :doc:`/bundles/configuration`
* :doc:`/bundles/extension`
* :doc:`/bundles/prepend_extension`

.. _`third-party bundles`: https://github.com/search?q=topic%3Asymfony-bundle&type=Repositories
.. _`PSR-4`: https://www.php-fig.org/psr/psr-4/
.. _`Symfony Bundle Development screencast series`: https://symfonycasts.com/screencast/bundle-development
