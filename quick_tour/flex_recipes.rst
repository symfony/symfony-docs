Flex: Compose your Application
==============================

After reading the first part of this tutorial, you have decided that Symfony was
worth another 10 minutes. Great choice! In this second part, you'll learn about
Symfony Flex: the amazing tool that makes adding new features as simple as running
one command. It's also the reason why Symfony is ideal for a small micro-service
or a huge application. Curious? Perfect!

Symfony: Start Micro!
---------------------

Unless you're building a pure API (more on that soon!), you'll probably want to
render HTML. To do that, you'll use `Twig`_. Twig is a flexible, fast, and secure
template engine for PHP. It makes your templates more readable and concise; it also
makes them more friendly for web designers.

Is Twig already installed in our application? Actually, not yet! And that's great!
When you start a new Symfony project, it's *small*:  only the most critical dependencies
are included in your ``composer.json`` file:

.. code-block:: text

    "require": {
        "...",
        "symfony/console": "^6.1",
        "symfony/flex": "^2.0",
        "symfony/framework-bundle": "^6.1",
        "symfony/yaml": "^6.1"
    }

This makes Symfony different from any other PHP framework! Instead of starting with
a *bulky* app with *every* possible feature you might ever need, a Symfony app is
small, simple and *fast*. And you're in total control of what you add.

Flex Recipes and Aliases
------------------------

So how can we install and configure Twig? By running one single command:

.. code-block:: terminal

    $ composer require twig

Two *very* interesting things happen behind the scenes thanks to Symfony Flex: a
Composer plugin that is already installed in our project.

First, ``twig`` is not the name of a Composer package: it's a Flex *alias* that
points to ``symfony/twig-bundle``. Flex resolves that alias for Composer.

And second, Flex installs a *recipe* for ``symfony/twig-bundle``. What's a recipe?
It's a way for a library to automatically configure itself by adding and modifying
files. Thanks to recipes, adding features is seamless and automated: install a package
and you're done!

You can find a full list of recipes and aliases inside `RECIPES.md on the recipes repository`_.

What did this recipe do? In addition to automatically enabling the feature in
``config/bundles.php``, it added 3 things:

``config/packages/twig.yaml``
    A configuration file that sets up Twig with sensible defaults.

``config/packages/test/twig.yaml``
    A configuration file that changes some Twig options when running tests.

``templates/``
    This is the directory where template files will live. The recipe also added
    a ``base.html.twig`` layout file.

Twig: Rendering a Template
--------------------------

Thanks to Flex, after one command, you can start using Twig immediately:

.. code-block:: diff

      <?php
      // src/Controller/DefaultController.php
      namespace App\Controller;

      use Symfony\Component\Routing\Attribute\Route;
      use Symfony\Component\HttpFoundation\Response;
    + use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    - class DefaultController
    + class DefaultController extends AbstractController
      {
           #[Route('/hello/{name}', methods: ['GET'])]
           public function index(string $name): Response
           {
    -        return new Response("Hello $name!");
    +        return $this->render('default/index.html.twig', [
    +            'name' => $name,
    +        ]);
           }
      }

By extending ``AbstractController``, you now have access to a number of shortcut
methods and tools, like ``render()``. Create the new template:

.. code-block:: html+twig

    {# templates/default/index.html.twig #}
    <h1>Hello {{ name }}</h1>

That's it! The ``{{ name }}`` syntax will print the ``name`` variable that's passed
in from the controller. If you're new to Twig, welcome! You'll learn more about
its syntax and power later.

But, right now, the page *only* contains the ``h1`` tag. To give it an HTML layout,
extend ``base.html.twig``:

.. code-block:: html+twig

    {# templates/default/index.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Hello {{ name }}</h1>
    {% endblock %}

This is called template inheritance: our page now inherits the HTML structure from
``base.html.twig``.

Profiler: Debugging Paradise
----------------------------

One of the *coolest* features of Symfony isn't even installed yet! Let's fix that:

.. code-block:: terminal

    $ composer require profiler

Yes! This is another alias! And Flex *also* installs another recipe, which automates
the configuration of Symfony's Profiler. What's the result? Refresh!

See that black bar on the bottom? That's the web debug toolbar, and it's your new
best friend. By hovering over each icon, you can get information about what controller
was executed, performance information, cache hits & misses and a lot more. Click
any icon to go into the *profiler* where you have even *more* detailed debugging
and performance data!

Oh, and as you install more libraries, you'll get more tools (like a web debug toolbar
icon that shows database queries).

You can now directly use the profiler because it configured *itself* thanks to
the recipe. What else can we install?

Rich API Support
----------------

Are you building an API? You can already return JSON from any controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\Routing\Attribute\Route;

    class DefaultController extends AbstractController
    {
        // ...

        #[Route('/api/hello/{name}', methods: ['GET'])]
        public function apiHello(string $name): JsonResponse
        {
            return $this->json([
                'name' => $name,
                'symfony' => 'rocks',
            ]);
        }
    }

But for a *truly* rich API, try installing `API Platform`_:

.. code-block:: terminal

    $ composer require api

This is an alias to ``api-platform/api-pack`` :ref:`Symfony pack <symfony-packs>`,
which has dependencies on several other packages, like Symfony's Validator and
Security components, as well as the Doctrine ORM. In fact, Flex installed *5* recipes!

But like usual, we can immediately start using the new library. Want to create a
rich API for a ``product`` table? Create a ``Product`` entity and give it the
``#[ApiResource]`` attribute::

    // src/Entity/Product.php
    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiResource;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    #[ApiResource]
    class Product
    {
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'AUTO')]
        #[ORM\Column(type: 'integer')]
        private int $id;

        #[ORM\Column(type: 'string')]
        private string $name;

        #[ORM\Column(type: 'integer')]
        private int $price;

        // ...
    }

Done! You now have endpoints to list, add, update and delete products! Don't believe
me? List your routes by running:

.. code-block:: terminal

    $ php bin/console debug:router

    ------------------------------ -------- -------------------------------------
     Name                           Method   Path
    ------------------------------ -------- -------------------------------------
     api_products_get_collection    GET      /api/products.{_format}
     api_products_post_collection   POST     /api/products.{_format}
     api_products_get_item          GET      /api/products/{id}.{_format}
     api_products_put_item          PUT      /api/products/{id}.{_format}
     api_products_delete_item       DELETE   /api/products/{id}.{_format}
     ...
    ------------------------------ -------- -------------------------------------

.. _ easily-remove-recipes:

Removing Recipes
----------------

Not convinced yet? No problem: remove the library:

.. code-block:: terminal

    $ composer remove api

Flex will *uninstall* the recipes: removing files and undoing changes to put your
app back in its original state. Experiment without worry.

More Features, Architecture and Speed
-------------------------------------

I hope you're as excited about Flex as I am! But we still have *one* more chapter,
and it's the most important yet. I want to show you how Symfony empowers you to quickly
build features *without* sacrificing code quality or performance. It's all about
the service container, and it's Symfony's super power. Read on: about :doc:`/quick_tour/the_architecture`.

.. _`RECIPES.md on the recipes repository`: https://github.com/symfony/recipes/blob/flex/main/RECIPES.md
.. _`API Platform`: https://api-platform.com/
.. _`Twig`: https://twig.symfony.com/
