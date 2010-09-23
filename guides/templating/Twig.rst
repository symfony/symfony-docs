.. index::
   single: Twig
   single: View; Twig

Twig & Symfony2
===============

`Twig`_ is a flexible, fast, and secure template language for PHP. Symfony2
has native support for Twig through ``TwigBundle``.

.. index::
   single: Twig; Installation
   single: Twig; Configuration

Installation & Configuration
----------------------------

Enable the ``TwigBundle`` in your kernel::

    // hello/config/HelloKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Symfony\Framework\TwigBundle\Bundle(),
        );

        // ...
    }

Then, configure it:

.. configuration-block::

    .. code-block:: yaml

        # config/config.yml
        twig.config: ~

        # config/config_dev.yml
        twig.config:
            auto_reload: true

    .. code-block:: xml

        <!--
        xmlns:twig="http://www.symfony-project.org/schema/dic/twig"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/twig http://www.symfony-project.org/schema/dic/twig/twig-1.0.xsd
        -->

        <!-- config/config.xml -->
        <twig:config />

        <!-- config/config_dev.xml -->
        <twig:config auto_reload="true" />

    .. code-block:: php

        // config/config.php
        $container->loadFromExtension('twig', 'config');

        // config/config_dev.php
        $container->loadFromExtension('twig', 'config', array('auto_reload' => true));

.. tip::
   The configuration options are the same as the ones you pass to the
   ``Twig_Environment`` `constructor`_.

Usage
-----

To render a Twig template instead of a PHP one, add the ``:twig`` suffix at the
end of the template name. The controller below renders the ``index.twig``
template::

    // src/Application/HelloBundle/Controller/HelloController.php

    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index:twig', array('name' => $name));
    }

The ``:twig`` suffix is only needed when there is no context, like in a
controller. But when you extend or include a template from a Twig template,
Symfony2 automatically switches the default engine to Twig:

.. code-block:: jinja

    {# src/Application/HelloBundle/Resources/views/Hello/index.twig #}

    {# no need to add :twig as this is the default #}
    {% extends "HelloBundle::layout" %}

    Hello {{ $name }}!

.. note::
   The Twig templates must use the ``twig`` extension.

And here is a typical layout:

.. code-block:: jinja

   {# src/Application/HelloBundle/Resources/views/layout.twig #}
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
            {% block body %}{% endblock %}
        </body>
    </html>

Include other Templates
-----------------------

The best way to share a snippet of code between several distinct templates is
to define a template that can then be included into another one.

Create a ``hello.twig`` template:

.. code-block:: jinja

    {# src/Application/HelloBundle/Resources/views/Hello/hello.twig #}
    Hello {{ $name }}

And change the ``index.twig`` template to include it:

.. code-block:: jinja

    {# src/Application/HelloBundle/Resources/views/Hello/index.php #}
    {% extends "HelloBundle::layout" %}

    {% include "HelloBundle:Hello:hello" %}

.. tip:
   To embed a PHP template in a Twig one, add the ``:php`` suffix to the template
   name:

    .. code-block:: jinja

        {# index.twig #}

        {% render 'HelloBundle:Hello:sidebar:php' %}

Embed other Actions
-------------------

And what if you want to embed the result of another action in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

If you create a ``fancy`` action, and want to include it into the ``index``
template, simply use the following code:

.. code-block:: jinja

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.php -->
    {% render "HelloBundle:Hello:fancy" with ['name': name, 'color': 'green'] %}

Here, the ``HelloBundle:Hello:fancy`` string refers to the ``fancy`` action of
the ``Hello`` controller, and the argument is used as simulated request path
values::

    // src/Application/HelloBundle/Controller/HelloController.php

    class HelloController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('HelloBundle:Hello:fancy:twig', array('name' => $name, 'object' => $object));
        }

        // ...
    }

.. index::
   single: Twig; Helpers

Template Helpers
----------------

The default Symfony2 helpers are available within a Twig template via
specialized tags:

.. code-block:: jinja

    {# add a javascript #}
    {% javascript 'bundles/blog/js/blog.js' %}

    {# add a stylesheet #}
    {% stylesheet 'bundles/blog/css/blog.css' with ['media': 'screen'] %}

    {# output the javascripts and stylesheets in the layout #}
    {% javascripts %}
    {% stylesheets %}

    {# generate a URL for an asset #}
    {% asset 'css/blog.css' %}
    {% asset 'images/logo.png' %}

    {# generate a route #}
    {% route 'blog_post' with ['id': post.id] %}

    {# render a template #}
    {% include 'BlogBundle:Post:list' %}

    {# embed another controller response #}
    {% render 'BlogBundle:Post:list' with ['limit': 2], ['alt': 'BlogBundle:Post:error'] %}

.. _twig_extension_tag:

Enabling Custom Twig Extensions
-------------------------------

To enable a Twig extension, add it as a regular service in one of your
configuration, and tag it with ``twig.extension``:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.your_extension_name:
                class: Fully\Qualified\Extension\Class\Name
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.your_extension_name" class="Fully\Qualified\Extension\Class\Name">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.your_extension_name', 'Fully\Qualified\Extension\Class\Name')
            ->addTag('twig.extension')

and add the namespace for the extension in the src/autoload.php file::

    $loader->registerNamespaces(array(
        . . .
        'Fully\\Qualified\\Extension' => $vendorDir.'/fully/qualified/extesion',
        . . .
    ));
    
If you are using a PEAR formatted classname instead of namespace for you extension use this configuration:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.your_extension_name:
                class: Your_Extensions_Extension_Name
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.your_extension_name" class="Your_Extensions_Extension_Name">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.your_extension_name', 'Your_Extensions_Extension_Name')
            ->addTag('twig.extension')
            
and add the file prefix to src/autoload.php::

    $loader->registerPrefixes(array(
        . . .
        'Your_Extensions_' =>  $vendorDir.'/your-extensions/lib',
        . . .
    ));

.. tip::
  In autoload.php, the prefixes must be listed with the paths in assending order
    
        ;
    
.. _Twig:        http://www.twig-project.org/
.. _constructor: http://www.twig-project.org/book/03-Twig-for-Developers
