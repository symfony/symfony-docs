.. index::
   single: Twig
   single: View; Twig

Twig e Symfony2
===============

`Twig`_ é uma linguagem de template PHP flexível, rápida e segura. O Symfony2 
tem suporte nativo ao Twig através do ``TwigBundle``.

.. index::
   single: Twig; Installation
   single: Twig; Configuration

Instalação e Configuração
-------------------------

Ative o ``TwigBundle`` em seu kernel::

    public function registerBundles()
    {
      $bundles = array(
        // ...
        new Symfony\Framework\TwigBundle\Bundle(),
      );

      // ...
    }

Em seguida, configure-o:

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
   As opções de configuração são as mesmas que você passa ao 
   `construtor`_ ``Twig_Environment``.

Uso
---

Para renderizar um template Twig em vez de um PHP, adicione o sufixo ``:twig`` no
final do nome do template. O controlador abaixo renderiza o template ``index.twig``::

    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index:twig', array('name' => $name));
    }

O sufixo ``:twig`` somente é necessário quando não há contexto, como em um controlador. 
Mas quando você estender ou incluir um template dentro um template Twig, o Symfony2 
automaticamente fará a troca da engine padrão para Twig:

.. code-block:: jinja

    {# index.twig #}

    {# no need to add :twig as this is the default #}
    {% extends 'HelloBundle::layout' %}

    {% block content %}
        Hello {{ name }}

        {# use the special render tag to render a template #}
        {% render 'HelloBundle:Hello:sidebar' %}
    {% endblock %}

Para incorporar um template PHP em um Twig, adicione o sufixo ``:php``
ao nome do template:

.. code-block:: jinja

    {# index.twig #}

    {% render 'HelloBundle:Hello:sidebar:php' %}

E o contrário também é verdadeiro::

    // index.php

    <?php $view->render('HelloBundle:Hello:sidebar:twig') ?>

.. index::
   single: Twig; Helpers

Helpers
-------

Os helpers padrão do Symfony2 estão disponíveis dentro de um template
Twig através de tags especializadas:

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

.. _twig_extensions:

Habilitando Extensões Twig Customizadas
---------------------------------------

Para habilitar uma extensão Twig, adicione-a como um serviço regular em uma das suas configurações, e adicione 
uma anotação ``twig.extension``:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.your_extension_name:
                class: Fully\Qualified\Extension\Class\Name
                tag:   { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.your_extension_name" class="Fully\Qualified\Extension\Class\Name">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.your_extension_name', 'Fully\Qualified\Extension\Class\Name')
            ->addTag('twig.extension')
        ;

.. _Twig:        http://www.twig-project.org/
.. _constructor: http://www.twig-project.org/book/03-Twig-for-Developers
