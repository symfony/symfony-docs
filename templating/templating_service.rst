.. index::
    single: Templating; The templating service

How to Configure and Use the ``templating`` Service
===================================================

The heart of the template system in Symfony is the templating ``Engine``.
This special object is responsible for rendering templates and returning
their content. When you render a template in a controller, for example,
you're actually using the templating engine service. For example::

    return $this->render('article/index.html.twig');

is equivalent to::

    use Symfony\Component\HttpFoundation\Response;

    $engine = $this->container->get('templating');
    $content = $engine->render('article/index.html.twig');

    return $response = new Response($content);

.. _template-configuration:

The templating engine (or "service") is preconfigured to work automatically
inside Symfony. It can, of course, be configured further in the application
configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating: { engines: ['twig'] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:templating>
                    <framework:engine>twig</framework:engine>
                </framework:templating>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...

            'templating' => array(
                'engines' => array('twig'),
            ),
        ));

Several configuration options are available and are covered in the
:doc:`Configuration Appendix </reference/configuration/framework>`.

.. note::

   The ``twig`` engine is mandatory to use the webprofiler (as well as many
   third-party bundles).
