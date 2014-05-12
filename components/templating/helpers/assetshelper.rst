.. index::
    single: Templating Helpers; Assets Helper

Assets Helper
=============

The assets helper's main purpose is to make your application more portable by
generating asset paths:

.. code-block:: html+php

   <link href="<?php echo $view['assets']->getUrl('css/style.css') ?>" rel="stylesheet">

   <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>">

The assets helper can then be configured to render paths to a CDN or modify
the paths in case your assets live in a sub-directory of your host (e.g. ``http://example.com/app``).

Configure Paths
---------------

By default, the assets helper will prefix all paths with a slash. You can
configure this by passing a base assets path as the first argument of the
constructor::

    use Symfony\Component\Templating\Helper\AssetsHelper;

    // ...
    $templateEngine->set(new AssetsHelper('/foo/bar'));

Now, if you use the helper, everything will be prefixed with ``/foo/bar``:

.. code-block:: html+php

   <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>">
   <!-- renders as:
   <img src="/foo/bar/images/logo.png">
   -->

Absolute Urls
-------------

You can also specify a URL to use in the second parameter of the constructor::

    // ...
    $templateEngine->set(new AssetsHelper(null, 'http://cdn.example.com/'));

Now URLs are rendered like ``http://cdn.example.com/images/logo.png``.

.. versionadded:: 2.5
    Absolute URLs for assets were introduced in Symfony 2.5.

You can also use the third argument of the helper to force an absolute URL:

.. code-block:: html+php

   <img src="<?php echo $view['assets']->getUrl('images/logo.png', null, true) ?>">
   <!-- renders as:
   <img src="http://yourwebsite.com/foo/bar/images/logo.png">
   -->

.. note::

    If you already set a URL in the constructor, using the third argument of
    ``getUrl`` will not affect the generated URL.

Versioning
----------

To avoid using the cached resource after updating the old resource, you can
use versions which you bump every time you release a new project. The version
can be specified in the third argument::

    // ...
    $templateEngine->set(new AssetsHelper(null, null, '328rad75'));

Now, every URL is suffixed with ``?328rad75``. If you want to have a different
format, you can specify the new format in fourth argument. It's a string that
is used in :phpfunction:`sprintf`. The first argument is the path and the
second is the version. For instance, ``%s?v=%s`` will be rendered as
``/images/logo.png?v=328rad75``.

.. versionadded:: 2.5
    On-demand versioned URLs for assets were introduced in Symfony 2.5.

You can also generate a versioned URL on an asset-by-asset basis using the
fourth argument of the helper:

.. code-block:: html+php

   <img src="<?php echo $view['assets']->getUrl('images/logo.png', null, false, '3.0') ?>">
   <!-- renders as:
   <img src="/images/logo.png?v=3.0">
   -->

Multiple Packages
-----------------

Asset path generation is handled internally by packages. The component provides
2 packages by default:

* :class:`Symfony\\Component\\Templating\\Asset\\PathPackage`
* :class:`Symfony\\Component\\Templating\\Asset\\UrlPackage`

You can also use multiple packages::

    // ...
    $templateEngine->set(new AssetsHelper());

    $templateEngine->get('assets')->addPackage('images', new PathPackage('/images/'));
    $templateEngine->get('assets')->addPackage('scripts', new PathPackage('/scripts/'));

This will setup the assets helper with 3 packages: the default package which
defaults to ``/`` (set by the constructor), the images package which prefixes
it with ``/images/`` and the scripts package which prefixes it with
``/scripts/``.

If you want to set another default package, you can use
:method:`Symfony\\Component\\Templating\\Helper\\AssetsHelper::setDefaultPackage`.

You can specify which package you want to use in the second argument of
:method:`Symfony\\Component\\Templating\\Helper\\AssetsHelper::getUrl`:

.. code-block:: html+php

    <img src="<?php echo $view['assets']->getUrl('foo.png', 'images') ?>">
    <!-- renders as:
    <img src="/images/foo.png">
    -->

Custom Packages
---------------

You can create your own package by extending
:class:`Symfony\\Component\\Templating\\Package\\Package`.
