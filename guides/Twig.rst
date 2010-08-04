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

    public function registerBundles()
    {
      $bundles = array(
        // ...
        new Symfony\Framework\TwigBundle\Bundle(),
      );

      // ...
    }

Then, configure it:

.. code-block:: yaml

    # config/config.yml
    twig.config: ~

    # config/config_dev.yml
    twig.config:
        auto_reload: true

.. tip::
   The configuration options are the same as the ones you pass to the
   ``Twig_Environment`` `constructor`_.

Usage
-----

To render a Twig template instead of a PHP one, add the ``:twig`` suffix at the
end of the template name. The controller below renders the ``index.twig``
template::

    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index:twig', array('name' => $name));
    }

The ``:twig`` suffix is only needed when there is no context, like in a
controller. But when you extend or include a template from a Twig template,
Symfony2 automatically switches the default engine to Twig:

.. code-block:: jinja

    {# index.twig #}

    {# no need to add :twig as this is the default #}
    {% extends 'HelloBundle::layout' %}

    {% block content %}
        Hello {{ name }}

        {# use the special render tag to render a template #}
        {% render 'HelloBundle:Hello:sidebar' %}
    {% endblock %}

To embed a PHP template in a Twig one, add the ``:php`` suffix to the template
name:

.. code-block:: jinja

    {# index.twig #}

    {% render 'HelloBundle:Hello:sidebar:php' %}

And the opposite is also true::

    {# index.php #}

    <?php $view->render('HelloBundle:Hello:sidebar:twig') ?>

.. index::
   single: Twig; Helpers

Helpers
-------

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
    {% render 'BlogBundle:Post:list' with ['path': ['limit': 2], 'alt': 'BlogBundle:Post:error'] %}

.. _Twig:        http://www.twig-project.org/
.. _constructor: http://www.twig-project.org/book/03-Twig-for-Developers

Enabling Custom Extensions
--------------------------

To enable a Twig extension, add it as a regular service in one of your
configuration, and add a ``twig.extension`` annotation:

.. code-block:: yaml

    services:
        twig.extension.your_extension_name:
            class:      Fully\Qualified\Extension\Class\Name
            annotation: { name: twig.extension }
