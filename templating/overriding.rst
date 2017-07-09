.. index::
    single: Template; Overriding templates

How to Override Templates from Third-Party Bundles
==================================================

The Symfony community prides itself on creating and maintaining high quality
bundles (see `KnpBundles.com`_) for a large number of different features.
Once you use a third-party bundle, you'll likely need to override and customize
one or more of its templates.

Suppose you've installed an imaginary open-source AcmeBlogBundle in your
project. And while you're really happy with everything, you want to override
the template for a blog list page. Inside the bundle, the template you want to
override lives at ``Resources/views/Blog/index.html.twig``.

To override the bundle template, just copy the ``index.html.twig`` template
from the bundle to ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
(the ``app/Resources/AcmeBlogBundle`` directory won't exist, so you'll need
to create it). You're now free to customize the template.

.. caution::

    If you add a template in a new location, you *may* need to clear your
    cache (``php bin/console cache:clear``), even if you are in debug mode.

This logic also applies to *any* template that lives in a bundle: just follow the
convention: ``app/Resources/{BUNDLE_NAME}/views/{PATH/TO/TEMPLATE.html.twig}``.

.. note::

    You can also override templates from within a bundle by using bundle
    inheritance. For more information, see :doc:`/bundles/inheritance`.

.. _templating-overriding-core-templates:

.. index::
    single: Template; Overriding exception templates

Overriding Core Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

Since the Symfony Framework itself is just a bundle, core templates can be
overridden in the same way. For example, the core TwigBundle contains a number
of different "exception" and "error" templates that can be overridden by
copying each from the ``Resources/views/Exception`` directory of the TwigBundle
to, you guessed it, the ``app/Resources/TwigBundle/views/Exception`` directory.

.. _`KnpBundles.com`: http://knpbundles.com
