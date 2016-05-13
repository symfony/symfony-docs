.. index::
    pair: Twig; Configuration reference

TwigBundle Configuration ("twig")
=================================

.. configuration-block::

    .. code-block:: yaml

        twig:
            exception_controller:  twig.controller.exception:showAction
            form:
                resources:

                    # Default:
                    - form_div_layout.html.twig

                    # Example:
                    - MyBundle::form.html.twig
            globals:

                # Examples:
                foo:                 '@bar'
                pi:                  3.14

                # Example options, but the easiest use is as seen above
                some_variable_name:
                    # a service id that should be the value
                    id:                   ~
                    # set to service or leave blank
                    type:                 ~
                    value:                ~
            autoescape:                ~

            # The following were added in Symfony 2.3.
            # See http://twig.sensiolabs.org/doc/recipes.html#using-the-template-name-to-set-the-default-escaping-strategy
            autoescape_service:        ~ # Example: '@my_service'
            autoescape_service_method: ~ # use in combination with autoescape_service option
            base_template_class:       ~ # Example: Twig_Template
            cache:                     '%kernel.cache_dir%/twig'
            charset:                   '%kernel.charset%'
            debug:                     '%kernel.debug%'
            strict_variables:          ~
            auto_reload:               ~
            optimizations:             ~
            paths:
                '%kernel.root_dir%/../vendor/acme/foo-bar/templates': foo_bar

    .. code-block:: xml

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config auto-reload="%kernel.debug%"
                autoescape="true"
                base-template-class="Twig_Template"
                cache="%kernel.cache_dir%/twig"
                charset="%kernel.charset%"
                debug="%kernel.debug%"
                strict-variables="false"
                optimizations="true"
            >
                <twig:form>
                    <twig:resource>MyBundle::form.html.twig</twig:resource>
                </twig:form>
                <twig:global key="foo" id="bar" type="service" />
                <twig:global key="pi">3.14</twig:global>
                <twig:exception-controller>AcmeFooBundle:Exception:showException</twig:exception-controller>
                <twig:path namespace="foo_bar">%kernel.root_dir%/../vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('twig', array(
            'form' => array(
                'resources' => array(
                    'MyBundle::form.html.twig',
                )
             ),
             'globals' => array(
                 'foo' => '@bar',
                 'pi'  => 3.14,
             ),
             'auto_reload'          => '%kernel.debug%',
             'autoescape'           => true,
             'base_template_class'  => 'Twig_Template',
             'cache'                => '%kernel.cache_dir%/twig',
             'charset'              => '%kernel.charset%',
             'debug'                => '%kernel.debug%',
             'strict_variables'     => false,
             'exception_controller' => 'AcmeFooBundle:Exception:showException',
             'optimizations'        => true,
             'paths' => array(
                 '%kernel.root_dir%/../vendor/acme/foo-bar/templates' => 'foo_bar',
             ),
        ));

Configuration
-------------

auto_reload
~~~~~~~~~~~

**type**: ``boolean`` **default**: ``'%kernel.debug%'``

If ``true``, whenever a template is rendered, Symfony checks first if its source
code has changed since it was compiled. If it has changed, the template is
compiled again automatically.

autoescape
~~~~~~~~~~

**type**: ``boolean`` or ``string`` **default**: ``'filename'``

If set to ``true``, all template contents are escaped for HTML. If set to
``false``, automatic escaping is disabled (you can still escape each content
individually in the templates).

.. caution::

    Setting this option to ``false`` is dangerous and it will make your
    application vulnerable to XSS exploits because most third-party bundles
    assume that auto-escaping is enabled and they don't escape contents
    themselves.

If set to a string, the template contents are escaped using the strategy with
that name. Allowed values are ``html``, ``js``, ``css``, ``url``, ``html_attr``
and ``filename``. The default value is ``filename``. This strategy escapes
contents according to the filename extension (e.g. it uses ``html`` for
``*.html.twig`` templates and ``js`` for ``*.js.html`` templates).

.. tip::

    See `autoescape_service`_ and `autoescape_service_method`_ to define your
    own escaping strategy.

autoescape_service
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

As of Twig 1.17, the escaping strategy applied by default to the template is
determined during compilation time based on the filename of the template. This
means for example that the contents of a ``*.html.twig`` template are escaped
for HTML and the contents of ``*.js.twig`` are escaped for JavaScript.

This option allows to define the Symfony service which will be used to determine
the default escaping applied to the template.

autoescape_service_method
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

If ``autoescape_service`` option is defined, then this option defines the method
called to determine the default escaping applied to the template.

base_template_class
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'Twig_Template'``

Twig templates are compiled into PHP classes before using them to render
contents. This option defines the base class from which all the template classes
extend. Using a custom base template is discouraged because it will make your
application harder to maintain.

cache
~~~~~

**type**: ``string`` **default**: ``'%kernel.cache_dir%/twig'``

Before using the Twig templates to render some contents, they are compiled into
regular PHP code. Compilation is a costly process, so the result is cached in
the directory defined by this configuration option.

Set this option to ``null`` to disable Twig template compilation. However, this
is not recommended; not even in the ``dev`` environment, because the
``auto_reload`` option ensures that cached templates which have changed get
compiled again.

charset
~~~~~~~

**type**: ``string`` **default**: ``'%kernel.charset%'``

The charset used by the template files. In the Symfony Standard edition this
defaults to the ``UTF-8`` charset.

debug
~~~~~

**type**: ``boolean`` **default**: ``'%kernel.debug%'``

If ``true``, the compiled templates include a ``__toString()`` method that can
be used to display their nodes.

.. _config-twig-exception-controller:

exception_controller
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``twig.controller.exception:showAction``

This is the controller that is activated after an exception is thrown anywhere
in your application. The default controller
(:class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`)
is what's responsible for rendering specific templates under different error
conditions (see :doc:`/cookbook/controller/error_pages`). Modifying this
option is advanced. If you need to customize an error page you should use
the previous link. If you need to perform some behavior on an exception,
you should add a listener to the ``kernel.exception`` event (see :ref:`dic-tags-kernel-event-listener`).

optimizations
~~~~~~~~~~~~~

**type**: ``int`` **default**: ``-1``

Twig includes an extension called ``optimizer`` which is enabled by default in
Symfony applications. This extension analyzes the templates to optimize them
when being compiled. For example, if your template doesn't use the special
``loop`` variable inside a ``for`` tag, this extension removes the initialization
of that unused variable.

By default, this option is ``-1``, which means that all optimizations are turned
on. Set it to ``0`` to disable all the optimizations. You can even enable or
disable these optimizations selectively, as explained in the Twig documentation
about `the optimizer extension`_.

paths
~~~~~

**type**: ``array`` **default**: ``null``

This option defines the directories where Symfony will look for Twig templates
in addition to the default locations (``app/Resources/views/`` and the bundles'
``Resources/views/`` directories). This is useful to integrate the templates
included in some library or package used by your application.

The values of the ``paths`` option are defined as ``key: value`` pairs where the
``value`` part can be ``null``. For example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            paths:
                '%kernel.root_dir%/../vendor/acme/foo-bar/templates': ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:path>%kernel.root_dir%/../vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            // ...
            'paths' => array(
               '%kernel.root_dir%/../vendor/acme/foo-bar/templates' => null,
            ),
        ));

The directories defined in the ``paths`` option have more priority than the
default directories defined by Symfony. In the above example, if the template
exists in the ``acme/foo-bar/templates/`` directory inside your application's
``vendor/``, it will be used by Symfony.

If you provide a value for any path, Symfony will consider it the Twig namespace
for that directory:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            paths:
                '%kernel.root_dir%/../vendor/acme/foo-bar/templates': 'foo_bar'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:path namespace="foo_bar">%kernel.root_dir%/../vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        # app/config/config.php
        $container->loadFromExtension('twig', array(
            // ...
            'paths' => array(
               '%kernel.root_dir%/../vendor/acme/foo-bar/templates' => 'foo_bar',
            ),
        ));

This option is useful to not mess with the default template directories defined
by Symfony. Besides, it simplifies how you refer to those templates:

.. code-block:: text

    @foo_bar/template_name.html.twig

strict_variables
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``'%kernel.debug%'``

If set to ``true``, Symfony shows an exception whenever a Twig variable,
attribute or method doesn't exist. If set to ``false`` these errors are ignored
and the non-existing values are replaced by ``null``.

.. _`the optimizer extension`: http://twig.sensiolabs.org/doc/api.html#optimizer-extension
