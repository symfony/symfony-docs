.. index::
    single: Template; Overriding templates

How to Override Templates from Third-Party Bundles
==================================================

The Symfony community prides itself on creating and maintaining high quality
bundles (see `KnpBundles.com`_) for a large number of different features.
Once you use a third-party bundle, you'll likely need to override and customize
one or more of its templates.

Suppose you've installed the imaginary open-source AcmeBlogBundle in your
project. And while you're really happy with everything, you want to override
the blog "list" page to customize the markup specifically for your application.
By digging into the ``Blog`` controller of the AcmeBlogBundle, you find the
following::

    public function indexAction()
    {
        // some logic to retrieve the blogs
        $blogs = ...;

        $this->render(
            '@AcmeBlog/Blog/index.html.twig',
            array('blogs' => $blogs)
        );
    }

When ``@AcmeBlog/Blog/index.html.twig`` is rendered, Symfony actually looks in
two different locations for the template:

#. ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``

To override the bundle template, just copy the ``index.html.twig`` template
from the bundle to ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
(the ``app/Resources/AcmeBlogBundle`` directory won't exist, so you'll need
to create it). You're now free to customize the template.

.. caution::

    If you add a template in a new location, you *may* need to clear your
    cache (``php app/console cache:clear``), even if you are in debug mode.

This logic also applies to base bundle templates. Suppose also that each
template in AcmeBlogBundle inherits from a base template called
``@AcmeBlog/layout.html.twig``. Just as before, Symfony will look in
the following two places for the template:

#. ``app/Resources/AcmeBlogBundle/views/layout.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/layout.html.twig``

Once again, to override the template, just copy it from the bundle to
``app/Resources/AcmeBlogBundle/views/layout.html.twig``. You're now free to
customize this copy as you see fit.

If you take a step back, you'll see that Symfony always starts by looking in
the ``app/Resources/{BUNDLE_NAME}/views/`` directory for a template. If the
template doesn't exist there, it continues by checking inside the
``Resources/views`` directory of the bundle itself. This means that all bundle
templates can be overridden by placing them in the correct ``app/Resources``
subdirectory.

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
