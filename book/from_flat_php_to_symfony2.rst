From flat PHP to Symfony2
=========================

.. tip::

   If you're familiar with the MVC philosophy, you can choose to skip this
   chapter. Still, Symfony2's approach to organized code and application
   flow is so fresh and simple that we hope everyone will continuing reading.

The goal of any web application is simple: to process each HTTP request and
return the HTTP response for the requested resource. In reality, it takes real
work to keep an increasingly complex application organized and maintainable.
The purpose of a framework is to handle common tasks and encourage best practices
that help make this happen. Nowhere is this more obvious than when converting
a flat PHP application into Symfony2.

Simple Blog Application in flat PHP
-----------------------------------

To begin, let's create a one-page application that displays blog entries
that have been persisted to the database. Writing the application in flat
PHP is quick and easy:

.. code-block:: html+php

    <?php

    // index.php

    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    $result = mysql_query('SELECT id, title FROM post', $link);

    ?>

    <html>
        <head>
            <title>List of Posts</title>
        </head>
        <body>
            <h1>List of Posts</h1>
            <ul>
                <?php while ($row = mysql_fetch_assoc($result)): ?>
                <li>
                    <a href="/show.php?id=<?php echo $row['id'] ?>">
                        <?php echo $row['title'] ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </body>
    </html>

    <?php
    mysql_close($link);

That's quick to write, fast to execute, but impossible to maintain. There
are several problems that we'll aim to solve:

* **No error-checking** What if the connection to the database fails?

* **Poor organization** As the application grows in complexity, this single file
  would become increasingly unmaintainable. How should we begin to organize
  our code into pieces?

* **Difficult to reuse code** Since everything is in one file, there would
  be no way to reuse any part of the application for other "pages" of the
  application.

.. note::
    Another problem not mentioned here is the fact that our database is
    tied to MySQL. Though we won't cover it here, Symfony2 fully integrates
    with `Doctrine`_, a library dedicated to database abstraction and mapping.

Let's get to work on solving these problems and more.

Isolating the Presentation
~~~~~~~~~~~~~~~~~~~~~~~~~~

The code can immediately gain from separating the application "logic" from
the code that prepares the HTML representation of the requested resource:

.. code-block:: html+php

    <?php

    // index.php

    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    $result = mysql_query('SELECT id, title FROM post', $link);

    $posts = array();
    while ($row = mysql_fetch_assoc($result)) {
        $posts[] = $row;
    }

    mysql_close($link);

    // include the HTML presentation code
    require 'templates/list.php';

The HTML code is now stored in a separate file (``templates/list.php``), which
is primarily an HTML file that uses a template-like PHP syntax:

.. code-block:: html+php

    <html>
        <head>
            <title>List of Posts</title>
        </head>
        <body>
            <h1>List of Posts</h1>
            <ul>
                <?php foreach ($posts as $post): ?>
                <li>
                    <a href="/read?id=<?php echo $post['id'] ?>">
                        <?php echo $post['title'] ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </body>
    </html>

By convention, the file that contains all of the application logic - ``index.php`` -
is known as the "controller". The term controller is a word you'll hear
a lot regardless of the language or framework you choose for your web application.
It refers very simply to the area of *your* code that receives input from
the request and initiates the response.

In this case, our controller prepares data from the database and then includes
a template to present that data. With the controller isolated, you can now
imagine how it could easily be used to render the same blogs in other formats
(RSS, JSON, etc) simply by rendering a different template file (e.g. list.rss.php).

Isolating the Application (Domain) Logic
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So far our application contains only one page, but let's think ahead a
little bit about how the logic and code of our application might be reused.
For example, what if a different page needs to use the same database connection,
or even the same array of blog posts? Let's refactor the code so that the
core behavior and data-access function of our application are isolated in
a new file called ``model.php``:

.. code-block:: html+php

    <?php

    // model.php

    function open_database_connection()
    {
        $link = mysql_connect('localhost', 'myuser', 'mypassword');
        mysql_select_db('blog_db', $link);

        return $link;
    }

    function close_database_connection($link)
    {
        mysql_close($link);
    }

    function get_all_posts()
    {
        $link = open_database_connection();

        $result = mysql_query('SELECT id, title FROM post', $link);
        $posts = array();
        while ($row = mysql_fetch_assoc($result)) {
            $posts[] = $row;
        }

        close_database_connection($link);

        return $posts;
    }

.. note::

   We're using the filename ``model.php`` because we're isolating the
   actual behavior and logic of our application into a layer traditionally
   known as the "model". In a well-organized application, the majority
   of your application-specific PHP code would be considered to be the
   model. And unlike in this example, only a portion (or none) of the model
   is actually concerned with accessing a database.

The controller (``index.php``) is now very simple:

.. code-block:: html+php

    <?php

    require_once 'model.php';

    $posts = get_all_posts();

    require 'templates/list.php';

The sole task now of the controller is to get data from the core of our
application (the model) and call a template to render that data.

Isolating the Layout
~~~~~~~~~~~~~~~~~~~~

At this point, our application has been refactored into three distinct
pieces offering several advantages:

* The application logic (``model.php``) can be reused on other pages.
* The same controller could easily render the blog posts in other formats
  (RSS, JSON, etc) by using a different template (e.g. ``list.rss.php``).

The only portion of the code that can't be reused is the page layout. Let's
fix that by creating a new ``layout.php`` file:

.. code-block:: html+php

    <!-- templates/layout.php -->
    <html>
        <head>
            <title><?php echo $title ?></title>
        </head>
        <body>
            <?php echo $content ?>
        </body>
    </html>

The template (``templates/list.php``) can now be simplified to "extend"
the layout:

.. code-block:: html+php

    <?php $title = 'List of Posts' ?>

    <?php ob_start() ?>
        <h1>List of Posts</h1>
        <ul>
            <?php foreach ($posts as $post): ?>
            <li>
                <a href="/read?id=<?php echo $post['id'] ?>">
                    <?php echo $post['title'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php $content = ob_get_clean() ?>

    <?php include 'layout.php' ?>

We've now introduced a methodology that that allows for the reuse of the
layout. Unfortunately, you'll also notice that we've had to use a few ugly
PHP functions (``ob_start()``, ``ob_end_clean()``) in the template in order
to make it happen. As we'll see later, Symfony2 uses a ``Templating`` component
that allows this to be accomplished with clean template code.

Adding a Blog "show" Page
-------------------------

The blog "list" page has now been refactored so that the code is better-organized
and reusable. To prove it, let's add a blog "show" page, which displays an
individual blog post identified by an ``id`` query parameter.

To begin, we'll need a new function in the ``model.php`` file that retrieves
an individual blog result based on a given id::

    // model.php
    function get_post_by_id($id)
    {
        $link = open_database_connection();

        $id = mysql_real_escape_string($id);
        $query = 'SELECT date, title, body FROM post WHERE id = '.$id;
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);

        close_database_connection($link);

        return $row;
    }

Next, create a new file called ``show.php`` - our controller for this new
page:

.. code-block:: html+php

    <?php

    require_once 'model.php';

    $post = get_post_by_id($_GET['id']);

    require 'templates/show.php';

Finally, create the new template file - ``templates/show.php`` - to render
the individual blog:

.. code-block:: html+php

    <?php $title = $post['title'] ?>

    <?php ob_start() ?>
        <h1><?php echo $post['title'] ?></h1>

        <div class="date"><?php echo $post['date'] ?></div>
        <div class="body">
            <?php echo $post['body'] ?>
        </div>
    <?php $content = ob_get_clean() ?>

    <?php include 'layout.php' ?>

Creating the second page is now very easy and no code is duplicated. Still,
this page introduces even more lingering problems that a framework can solve
for you. For example, a missing or invalid "id" query parameter will cause
the page to crash. It would be better if this caused a 404 page to be rendered,
but this can't yet be easily accomplished.

Another major problem is that each individual controller file must include
the ``model.php`` file. What if each controller file suddenly needed to include
an additional file or perform some other global task (e.g. enforce security)?
As it stands now, that code would need to be added to every controller file.

A "Front Controller" to the Rescue
----------------------------------

The solution is to use a front controller: a single PHP file through which
*all* requests are processed. With a front controller, the URIs for the
application change slightly, but start to become more flexible::

    Without a front controller
    /index.php          => Blog list page (index.php executed)
    /show.php           => Blog show page (show.php executed)

    With index.php as the front controller
    /index.php          => Blog list page (index.php executed)
    /index.php/show     => Blog show page (index.php executed)

.. tip::
    The ``index.php`` portion of the URI can be removed if using Apache
    rewrite rules (or equivalent). In that case, the resulting URI of the
    blog show page would simply be ``/show``.

When using a front controller, a single PHP file (``index.php`` in this case)
renders *every* request. For the blog show page, ``/index.php/show`` will
actually execute the ``index.php`` file, which is now responsible for routing
requests internally based on the full URI. As you'll see, a front controller
is a very powerful tool.

Creating the Front Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We're about to take a **big** step with our application. With one file handling
all requests, we can centralize things such as security handling, configuration
loading, and routing. In our application, ``index.php`` must now be smart
enough to render the blog list page *or* the blog show page based on the
requested URI:

.. code-block:: html+php

    <?php

    // index.php

    // load and initialize any global libraries
    require_once 'model.php';
    require_once 'controllers.php';

    // route the request internally
    $uri = $_REQUEST['REQUEST_URI'];
    if ($uri == '/index.php') {
        list_action();
    } elseif ($uri == '/index.php/show' && isset($_GET['id'])) {
        show_action($_GET['id']);
    } else {
        header('Status: 404 Not Found');
        echo '<html><body><h1>Page Not Found</h1></body></html>';
    }

For organization, we've made both of our controllers (formerly ``index.php``
and ``show.php``) PHP functions and moved them into a separate file,
``controllers.php``::

    function list_action()
    {
        $posts = get_all_posts();
        require 'templates/list.php';
    }

    function show_action($id)
    {
        $post = get_post_by_id($id);
        require 'templates/show.php';
    }

As a front controller, ``index.php`` has taken on an entirely new role, one
that includes loading the core libraries and routing the application so that
one of the two controllers (the ``list_action()`` and ``show_action()``
functions) is called. In reality, the front controller is beginning to look and
act a lot like Symfony2's mechanism for handling and routing requests.

.. tip::

   Another advantage of a front controller is flexible URLs. Notice that
   the URL to the blog show page could be changed from ``/show`` to ``/read``
   by changing code in only one location. Before, an entire file needed to
   be renamed. In Symfony2, URLs are even more flexible.

By now, we've evolved our application from a single PHP file into a structure
that is organized and allows for code reuse. You should be happier, but far
from satisfied. For example, our "routing" system is easily fooled, and wouldn't
recognize that the list page (``/index.php``) should be accessible simply via ``/``
(if Apache rewrite rules were added). Instead of developing the application
we intended to build, we risk spending a significant amount of development
time-solving problems (e.g. routing, calling controllers, security, logging,
etc) that are routine to all web applications.

Add a Touch of Symfony2
~~~~~~~~~~~~~~~~~~~~~~~

Now before you actually start using Symfony2, you need to make sure PHP knows 
where to find the Symfony2 classes. For this, you need to set up the autoloader.
Symfony2 provides a generic autoloader that can be used for many of the next-
generation frameworks, including Zend Framework 2 and PEAR 2. To set this up, 
create an ``app/bootstrap.php`` and set up the autoloader in that file:

.. code-block:: html+php

    <?php
    
    // app/bootstrap.php
    
    require_once 'vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
    
    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'                        => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    ));
    
    $loader->register();

.. note::

    The above example assumes that the Symfony2 codebase was put into the
    ``vendor/symfony`` directory. If you put the Symfony2 codebase in a 
    different location (which is not a problem for Symfony2), adjust the
    require path and the registerNamespaces() path accordingly.

This will include the UniversalClassLoader, register the Symfony namespace with
it and then register the autoloader with the standard PHP autoloader stack.
Now, you're all set to start using Symfony2 classes.

Now take another look at our application. Though simple, we've created an 
application that looks and acts almost exactly like a full Symfony2 
application. Sure, Symfony2 gives you lots of helpful tools, but the process
of handling a request and returning a response is almost identical:

* A front controller handles all requests.
* The core classes and configuration are loaded.
* A routing system decides which controller to execute based on information
  from the request.
* The controller is called, which returns a response.

The good news is that no matter what you do with Symfony2, this basic formula
will apply. And instead of setting it all up yourself, Symfony2 takes care
of it.

Before diving all the way in, let's use just a little bit of Symfony2 to make
our application more flexible and dependable. Core to Symfony's philosophy is
the idea the application's job is to process each HTTP request and return the
appropriate HTTP response. To this end, Symfony2 provides both a
:class:`Symfony\\Component\\HttpFoundation\\Request` and a
:class:`Symfony\\Component\\HttpFoundation\\Response` class. These classes are
object-oriented representations of the raw HTTP request being processed and
the HTTP response being returned. We can use them to improve our simple
application:

.. code-block:: html+php

    <?php

    // index.php
    require_once 'app/bootstrap.php';
    require_once 'model.php';
    require_once 'controllers.php';
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    $request = Request::createFromGlobals();

    $uri = $request->getPathInfo();
    if ($uri == '/') {
        $response = list_action();
    } elseif ($uri == '/show' && $request->query->has('id')) {
        $response = show_action($request->query->get('id'));
    } else {
        $html = '<html><body><h1>Page Not Found</h1></body></html>';
        $response = new Response($html, 404);
    }

    // echo the headers and send the response
    $response->send();

The controllers are now responsible for returning a ``Response`` object::

    // controllers.php
    use Symfony\Component\HttpFoundation\Response;

    function list_action()
    {
        $posts = get_all_posts();
        $html = render_template('templates/list.php');

        return new Response($html);
    }

    function show_action($id)
    {
        $post = get_post_by_id($id);
        $html = render_template('templates/show.php');

        return new Response($html);
    }

    // helper function to render templates
    function render_template($path)
    {
        ob_start();
        require $path;
        $html = ob_end_clean();

        return $html;
    }

By bringing in a small part of Symfony2, our application is more flexible and
dependable. The ``Request`` object gives us a dependable way to access
information about the HTTP request. Specifically, the ``getPathInfo()`` method
returns a cleaned request URI (always returning ``/show`` and never
``/index.php/show``). The ``Response`` object gives us more flexibility when
constructing the HTTP response, allowing HTTP headers and content to be added
via an object-oriented interface.

The Sample Application in Symfony2
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We've come a long way, but we've still got a lot of code for such a simple
application. We've also invented a simple routing system and are dependent
on using ``ob_start()`` and ``ob_end_clean()`` to render templates. If we
were to continue to build a framework from scratch, we could use Symfony's
standalone ``Routing`` and ``Templating`` components to fix some of these
issues.

Instead, we'll let Symfony2 take care of these issues for us. Here's the
same sample application, now built in Symfony2:

.. code-block:: html+php

    <?php

    // src/Sensio/BlogBundle/Controller/BlogController.php

    namespace Sensio\BlogBundle\Controller;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class BlogController extends Controller
    {
        public function listAction()
        {
            $blogs = $this->container->get('doctrine.orm.entity_manager')
                ->createQuery('SELECT b FROM Blog:Blog b')
                ->execute();

            return $this->render('BlogBundle:Blog:list.html.php', array('blogs' => $blogs));
        }

        public function showAction($id)
        {
            $blog = $this->container->get('doctrine.orm.entity_manager')
                ->createQuery('SELECT b FROM Blog:Blog b WHERE id = :id')
                ->setParameter('id', $id)
                ->getSingleResult();

            return $this->render('BlogBundle:Blog:show.html.php', array('blog' => $blog));
        }
    }

Our two controllers are still lightweight. Each uses the Doctrine ORM library
to retrieve objects from the database and the ``Templating`` component to
render a template and return a ``Response`` object. The list template is
now quite a bit simpler:

.. code-block:: html+php

    <!-- src/Sensio/BlogBundle/Resources/views/Blog/list.html.php --> 
    <?php $view->extend('::layout.html.php') ?>

    <?php $view['slots']->set('title', 'List of Posts') ?>

    <h1>List of Posts</h1>
    <ul>
        <?php foreach ($posts as $post): ?>
        <li>
            <a href="<?php echo $view['router']->generate('blog_show', array('id' => $post->getId())) ?>">
                <?php echo $post->getTitle() ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

The layout is nearly identical:

.. code-block:: html+php

    <!-- app/views/layout.html.php -->
    <html>
        <head>
            <title><?php echo $view['slots']->output('title', 'Default title') ?></title>
        </head>
        <body>
            <?php echo $view['slots']->output('_content') ?>
        </body>
    </html>

.. note::

    We'll leave the show template as an exercise as it should be trivial to
    create based on the list template.

When Symfony2's engine (called the ``Kernel``) boots up, it needs a map so
that it knows which controllers to execute based on the request information.
A routing configuration map provides this information in a readable format::

    # app/config/routing.yml
    blog_list:
        pattern:  /blog
        defaults: { _controller: BlogBundle:Blog:list }

    blog_show:
        pattern:  /blog/show/{id}
        defaults: { _controller: BlogBundle:Blog:show }

Now that Symfony2 is handling all the mundane tasks, our front controller
is dead simple. And since it contains so little, you never have to touch
it once it's created (and if you use a Symfony2 distribution, you won't
even need to create it):

.. code-block:: html+php

    <?php

    // web/app.php
    require_once __DIR__.'/../app/bootstrap.php';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->handle(Request::createFromGlobals())->send();

The front controller's only job is to initialize Symfony2's engine (the kernel)
and pass it a ``Request`` object to handle. Symfony2's core then uses the
routing map information to determine which controller to call. Just as in
our sample application, your controller method is responsible for returning
the final ``Response`` object. There's really not much else to it.

In the upcoming chapters, we'll learn more about how each piece works and how
the project is organized by default. For now, just realize what we've gained
by migrating the original flat PHP application to Symfony2:

* Your application code is clearly and consistently organized (though Symfony
  doesn't force you into this) in a way that promotes reusability and allows
  for new developers to be productive in your project more quickly.

* 100% of the code you write is for *your* application. You no longer need
  to develop or maintain low-level framework tasks such as :ref:`autoloading<autoloading-introduction-sidebar>`,
  :doc:`routing</book/routing>`, or rendering :doc:`controllers</book/controller>`.

* Symfony2 gives you access to open source tools such as Doctrine and the
  Templating, Security, Form, Validation and Translation components (among others).

* The URLs of your application are fully-flexible thanks to the ``Routing``
  component.

* Symfony2's HTTP-centric architecture gives you access to powerful tools
  such as HTTP caching powered by Symfony2's internal HTTP cache or more
  powerful tools such as `Varnish`_.

* Unit and functional testing via `PHPUnit`_ is available by default. Symfony2
  provides several standalone components that make functional testing very
  easy and powerful.

Better templates
----------------

If you choose to use it, Symfony2 comes standard with a templating engine
called `Twig`_ that makes templates faster to write and easier to read.
It means that our sample application could contain even less code! Take,
for example, the previous list template written in Twig:

.. code-block:: html+jinja

    {# src/Sensio/BlogBundle/Resources/views/Blog/list.html.twig #}

    {% extends "::layout.html.twig" %}
    {% block title %}List of Posts{% endblock %}

    {% block body %}
        <h1>List of Posts</h1>
        <ul>
            {% for post in posts %}
            <li>
                <a href="{{ path('blog_show', { 'id': post.id }) }}">
                    {{ post.title }}
                </a>
            </li>
            {% endfor %}
        </ul>
    {% endblock %}

The corresponding ``layout.html.twig`` template is also easier to write:

.. code-block:: html+jinja

    {# app/views/layout.html.twig #}

    <html>
        <head>
            <title>{% block title %}Default title{% endblock %}</title>
        </head>
        <body>
            {% block body %}{% endblock %}
        </body>
    </html>

Twig is well-supported in Symfony2. And while PHP templates will always
be supported in Symfony2, we'll continue to discuss the advantages of Twig.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/templating/PHP`
* :doc:`/cookbook/controller/service`

.. _`Doctrine`: http://www.doctrine-project.org
.. _`Twig`: http://www.twig-project.org
.. _`Varnish`: http://www.varnish-cache.org
.. _`PHPUnit`: http://www.phpunit.de
