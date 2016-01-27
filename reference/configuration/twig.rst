.. index::
    pair: Twig; Configuration reference

TwigBundle Configuration ("twig")
=================================

.. configuration-block::

    .. code-block:: yaml

        twig:
            exception_controller:  twig.controller.exception:showAction

            form_themes:

                # Default:
                - form_div_layout.html.twig

                # Bootstrap:
                - bootstrap_3_layout.html.twig
                - bootstrap_3_horizontal_layout.html.twig

                # Foundation
                - foundation_5_layout.html.twig

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

            <twig:config
                auto-reload="%kernel.debug%"
                autoescape="true"
                base-template-class="Twig_Template"
                cache="%kernel.cache_dir%/twig"
                charset="%kernel.charset%"
                debug="%kernel.debug%"
                strict-variables="false"
                optimizations="true"
            >
                <twig:form-theme>form_div_layout.html.twig</twig:form-theme> <!-- Default -->
                <twig:form-theme>MyBundle::form.html.twig</twig:form-theme>

                <twig:global key="foo" id="bar" type="service" />
                <twig:global key="pi">3.14</twig:global>

                <twig:exception-controller>AcmeFooBundle:Exception:showException</twig:exception-controller>
                <twig:path namespace="foo_bar">%kernel.root_dir%/../vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('twig', array(
            'form_themes' => array(
                'form_div_layout.html.twig', // Default
                'MyBundle::form.html.twig',
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

.. caution::

    The ``twig.form`` (``<twig:form />`` tag for xml) configuration key
    has been deprecated and will be removed in 3.0. Instead, use the ``twig.form_themes``
    option.

Configuration
-------------

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
