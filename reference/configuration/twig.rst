Twig Configuration Reference (TwigBundle)
=========================================

The TwigBundle integrates the Twig library in Symfony applications to
:ref:`render templates <templates-rendering>`. All these options are configured
under the ``twig`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference twig

    # displays the actual config values used by your application
    $ php bin/console debug:config twig

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/twig``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/twig/twig-1.0.xsd``

Configuration
-------------

auto_reload
~~~~~~~~~~~

**type**: ``boolean`` **default**: ``%kernel.debug%``

If ``true``, whenever a template is rendered, Symfony checks first if its source
code has changed since it was compiled. If it has changed, the template is
compiled again automatically.

.. _config-twig-autoescape:

autoescape
~~~~~~~~~~

**type**: ``boolean`` or ``string`` **default**: ``'name'``

If set to ``false``, automatic escaping is disabled (you can still escape each content
individually in the templates).

.. caution::

    Setting this option to ``false`` is dangerous and it will make your
    application vulnerable to `XSS attacks`_ because most third-party bundles
    assume that auto-escaping is enabled and they don't escape contents
    themselves.

If set to a string, the template contents are escaped using the strategy with
that name. Allowed values are ``html``, ``js``, ``css``, ``url``, ``html_attr``
and ``name``. The default value is ``name``. This strategy escapes contents
according to the template name extension (e.g. it uses ``html`` for ``*.html.twig``
templates and ``js`` for ``*.js.twig`` templates).

.. tip::

    See `autoescape_service`_ and `autoescape_service_method`_ to define your
    own escaping strategy.

autoescape_service
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The escaping strategy applied by default to the template is determined during
compilation time based on the filename of the template. This means for example
that the contents of a ``*.html.twig`` template are escaped for HTML and the
contents of ``*.js.twig`` are escaped for JavaScript.

This option allows to define the Symfony service which will be used to determine
the default escaping applied to the template.

autoescape_service_method
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

If ``autoescape_service`` option is defined, then this option defines the method
called to determine the default escaping applied to the template.

base_template_class
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'Twig\Template'``

Twig templates are compiled into PHP classes before using them to render
contents. This option defines the base class from which all the template classes
extend. Using a custom base template is discouraged because it will make your
application harder to maintain.

cache
~~~~~

**type**: ``string`` | ``false`` **default**: ``'%kernel.cache_dir%/twig'``

Before using the Twig templates to render some contents, they are compiled into
regular PHP code. Compilation is a costly process, so the result is cached in
the directory defined by this configuration option.

Set this option to ``false`` to disable Twig template compilation. However, this
is not recommended; not even in the ``dev`` environment, because the
``auto_reload`` option ensures that cached templates which have changed get
compiled again.

charset
~~~~~~~

**type**: ``string`` **default**: ``'%kernel.charset%'``

The charset used by the template files. By default it's the same as the value of
the :ref:`kernel.charset container parameter <configuration-kernel-charset>`,
which is ``UTF-8`` by default in Symfony applications.

date
~~~~

These options define the default values used by the ``date`` filter to format
date and time values. They are useful to avoid passing the same arguments on
every ``date`` filter call.

format
......

**type**: ``string`` **default**: ``F j, Y H:i``

The format used by the ``date`` filter to display values when no specific format
is passed as an argument.

interval_format
...............

**type**: ``string`` **default**: ``%d days``

The format used by the ``date`` filter to display ``DateInterval`` instances
when no specific format is passed as argument.

timezone
........

**type**: ``string`` **default**: (the value returned by ``date_default_timezone_get()``)

The timezone used when formatting date values with the ``date`` filter and no
specific timezone is passed as an argument.

debug
~~~~~

**type**: ``boolean`` **default**: ``%kernel.debug%``

If ``true``, the compiled templates include a ``__toString()`` method that can
be used to display their nodes.

This option also controls the behavior of :ref:`the Twig dump utilities <twig-dump-utilities>`.
If this option is ``false``, the ``dump()`` function doesn't output any contents.

.. _config-twig-default-path:

default_path
~~~~~~~~~~~~

**type**: ``string`` **default**: ``'%kernel.project_dir%/templates'``

The path to the directory where Symfony will look for the application Twig
templates by default. If you store the templates in more than one directory, use
the :ref:`paths <config-twig-paths>`  option too.

.. _config-twig-form-themes:

form_themes
~~~~~~~~~~~

**type**: ``array`` of ``string`` **default**: ``['form_div_layout.html.twig']``

Defines one or more :doc:`form themes </form/form_themes>` which are applied to
all the forms of the application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['bootstrap_5_layout.html.twig', 'form/my_theme.html.twig']
            # ...

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_5_layout.html.twig</twig:form-theme>
                <twig:form-theme>form/my_theme.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            $twig->formThemes([
                'bootstrap_5_layout.html.twig',
                'form/my_theme.html.twig',
            ]);

            // ...
        };

The order in which themes are defined is important because each theme overrides
all the previous one. When rendering a form field whose block is not defined in
the form theme, Symfony falls back to the previous themes until the first one.

These global themes are applied to all forms, even those which use the
:ref:`form_theme Twig tag <reference-twig-tag-form-theme>`, but you can
:ref:`disable global themes for specific forms <disabling-global-themes-for-single-forms>`.

globals
~~~~~~~

**type**: ``array`` **default**: ``[]``

It defines the global variables injected automatically into all Twig templates.
Learn more about :ref:`Twig global variables <templating-global-variables>`.

number_format
~~~~~~~~~~~~~

These options define the default values used by the ``number_format`` filter to
format numeric values. They are useful to avoid passing the same arguments on
every ``number_format`` filter call.

decimals
........

**type**: ``integer`` **default**: ``0``

The number of decimals used to format numeric values when no specific number is
passed as argument to the ``number_format`` filter.

decimal_point
.............

**type**: ``string`` **default**: ``.``

The character used to separate the decimals from the integer part of numeric
values when no specific character is passed as argument to the ``number_format``
filter.

thousands_separator
...................

**type**: ``string`` **default**: ``,``

The character used to separate every group of thousands in numeric values when
no specific character is passed as argument to the ``number_format`` filter.

optimizations
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``-1``

Twig includes an extension called ``optimizer`` which is enabled by default in
Symfony applications. This extension analyzes the templates to optimize them
when being compiled. For example, if your template doesn't use the special
``loop`` variable inside a ``for`` tag, this extension removes the initialization
of that unused variable.

By default, this option is ``-1``, which means that all optimizations are turned
on. Set it to ``0`` to disable all the optimizations. You can even enable or
disable these optimizations selectively, as explained in the Twig documentation
about `the optimizer extension`_.

.. _config-twig-paths:

paths
~~~~~

**type**: ``array`` **default**: ``null``

Defines the directories where application templates are stored in addition to
the directory defined in the :ref:`default_path option <config-twig-default-path>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths:
                'email/default/templates': ~
                'backend/templates': 'admin'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:path>email/default/templates</twig:path>
                <twig:path namespace="admin">backend/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            // ...

            $twig->path('email/default/templates', null);
            $twig->path('backend/templates', 'admin');
        };

Read more about :ref:`template directories and namespaces <templates-namespaces>`.

.. _config-twig-strict-variables:

strict_variables
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``%kernel.debug%``

If set to ``true``, Symfony shows an exception whenever a Twig variable,
attribute or method doesn't exist. If set to ``false`` these errors are ignored
and the non-existing values are replaced by ``null``.

.. _`the optimizer extension`: https://twig.symfony.com/doc/3.x/api.html#optimizer-extension
.. _`XSS attacks`: https://en.wikipedia.org/wiki/Cross-site_scripting
