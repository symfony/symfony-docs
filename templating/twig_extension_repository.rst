.. index::
   single: Using the Twig Extensions Repository

How to Use the Twig Extensions Repository
=========================================

The `Twig official extension repository`_ contains (as of writing) some
helpful Twig extensions that are not part of the Twig core. They add
useful functions for internationalization, working with arrays and
dates. To learn more about these extensions, have a look at their
`documentation`_.

This repository is meant as an extension to Twig in general. So, it
is does *not* provide a direct means to register itself with the
Symfony Framework (it is not a Bundle).

It is, however, very easy to get the extensions set-up in Symfony.
This article will show you how to register the ``Intl`` extension from
that repository so you can use it in your Twig templates.

.. tip::

    Setting up one of the other extensions works just the same way,
    except you need to choose another service id and have to use
    the right class name.

First, add the Twig Extensions repository as a dependency in your
project. Assuming you are using Composer, run

.. code-block:: terminal

    $ composer require twig/extensions

Then, define the extension class as a service and tag it with the
``twig.extension`` tag.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            twig_extensions.intl:
                class: Twig_Extensions_Extension_Intl
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="twig_extensions.intl"
                    class="Twig_Extensions_Extension_Intl">
                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use \Twig_Extensions_Extension_Intl;

        $container
            ->register('twig_extensions.intl', Twig_Extensions_Extension_Intl::class)
            ->addTag('twig.extension');

And that's it! For example, you should now be able to use the
``localizeddate`` filter to format a date according to the request's
current locale:

.. code-block:: twig

    {{ post.published_at|localizeddate('medium', 'none', locale) }}

Learning further
----------------

In the :doc:`reference section </reference/twig_reference>`, you can
find all the extra Twig functions, filters, tags and tests that are
already added by the Symfony Framework.

We also have documentation on :doc:`how to write your own Twig extension </templating/twig_extension>`.

.. _`Twig official extension repository`: https://github.com/twigphp/Twig-extensions
.. _`documentation`: http://twig-extensions.readthedocs.io/
