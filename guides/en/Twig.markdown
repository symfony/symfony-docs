Twig & Symfony2
===============

[Twig][1] is a flexible, fast, and secure template language for PHP. Symfony2
has native support for Twig through `TwigBundle`.

Installation & Configuration
----------------------------

Enable the `TwigBundle` in your kernel:

    [php]
    public function registerBundles()
    {
      $bundles = array(
        // ...
        new Symfony\Framework\TwigBundle\Bundle(),
      );

      // ...
    }

Then, configure it:

    [yml]
    # config/config.yml
    twig.config: ~

    # config/config_dev.yml
    twig.config:
      auto_reload: true

>**TIP**
>The configuration options are the same as the ones you pass to the
>`Twig_Environment` [constructor][2].

Usage
-----

To render a Twig template instead of a PHP one, add the `:twig` suffix at the
end of the template name. The controller below renders the `index.twig`
template:

    [php]
    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index:twig', array('name' => $name));
    }

The `:twig` suffix is only needed when there is no context, like in a
controller. But when you extend or include a template from a Twig template,
Symfony2 automatically switches the default engine to Twig:

    [twig]
    {# index.twig #}

    {# no need to add :twig as this is the default #}
    {% extends 'HelloBundle::layout' %}

    {% block content %}
        Hello {{ name }}

        {# use the special render tag to render a template #}
        {% render 'HelloBundle:Hello:sidebar' %}
    {% endblock %}

To embed a PHP template in a Twig one, add the `:php` suffix to the template
name:

    [twig]
    {# index.twig #}

    {% render 'HelloBundle:Hello:sidebar:php' %}

And the opposite is also true:

    [php]
    {# index.php #}

    <?php $view->render('HelloBundle:Hello:sidebar:twig') ?>

Helpers
-------

The default Symfony2 helpers are available within a Twig template via
specialized tags:

    [twig]
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
    {% render 'BlogBundle:Post:list' with ['path': ['limit': 2], 'alt': 'BlogBundle:Post:error'] %}

[1]: http://www.twig-project.org/
[2]: http://www.twig-project.org/book/03-Twig-for-Developers
