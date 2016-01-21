The Controller
==============

Still here after the first two parts? You are already becoming a Symfony
fan! Without further ado, discover what controllers can do for you.

Returning Raw Responses
-----------------------

Symfony defines itself as a Request-Response framework. When the user makes
a request to your application, Symfony creates a ``Request`` object to
encapsulate all the information related to that request. Similarly, the
result of executing any action of any controller is the creation of a
``Response`` object which Symfony uses to generate the HTML content returned
to the user.

So far, all the actions shown in this tutorial used the ``$this->render()``
shortcut to return a rendered response as result. In case you need it, you
can also create a raw ``Response`` object to return any text content::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            return new Response('Welcome to Symfony!');
        }
    }

Route Parameters
----------------

Most of the time, the URLs of applications include variable parts on them.
If you are creating for example a blog application, the URL to display the
articles should include their title or some other unique identifier to let
the application know the exact article to display.

In Symfony applications, the variable parts of the routes are enclosed in
curly braces (e.g. ``/blog/read/{article_title}/``). Each variable part
is assigned a unique name that can be used later in the controller to retrieve
each value.

Let's create a new action with route variables to show this feature in action.
Open the ``src/AppBundle/Controller/DefaultController.php`` file and add
a new method called ``helloAction`` with the following content::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class DefaultController extends Controller
    {
        // ...

        /**
         * @Route("/hello/{name}", name="hello")
         */
        public function helloAction($name)
        {
            return $this->render('default/hello.html.twig', array(
                'name' => $name
            ));
        }
    }

Open your browser and access the ``http://localhost:8000/hello/fabien``
URL to see the result of executing this new action. Instead of the action
result, you'll see an error page. As you probably guessed, the cause of
this error is that we're trying to render a template
(``default/hello.html.twig``) that doesn't exist yet.

Create the new ``app/Resources/views/default/hello.html.twig`` template
with the following content:

.. code-block:: html+twig

    {# app/Resources/views/default/hello.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Hi {{ name }}! Welcome to Symfony!</h1>
    {% endblock %}

Browse again the ``http://localhost:8000/hello/fabien`` URL and you'll see
this new template rendered with the information passed by the controller.
If you change the last part of the URL (e.g.
``http://localhost:8000/hello/thomas``) and reload your browser, the page
will display a different message. And if you remove the last part of the
URL (e.g.  ``http://localhost:8000/hello``), Symfony will display an error
because the route expects a name and you haven't provided it.

Using Formats
-------------

Nowadays, a web application should be able to deliver more than just HTML
pages. From XML for RSS feeds or Web Services, to JSON for Ajax requests,
there are plenty of different formats to choose from. Supporting those formats
in Symfony is straightforward thanks to a special variable called ``_format``
which stores the format requested by the user.

Tweak the ``hello`` route by adding a new ``_format`` variable with ``html``
as its default value::

    // src/AppBundle/Controller/DefaultController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route("/hello/{name}.{_format}", defaults={"_format"="html"}, name="hello")
     */
    public function helloAction($name, $_format)
    {
        return $this->render('default/hello.'.$_format.'.twig', array(
            'name' => $name
        ));
    }

Obviously, when you support several request formats, you have to provide
a template for each of the supported formats. In this case, you should create
a new ``hello.xml.twig`` template:

.. code-block:: xml+php

    <!-- app/Resources/views/default/hello.xml.twig -->
    <hello>
        <name>{{ name }}</name>
    </hello>

Now, when you browse to ``http://localhost:8000/hello/fabien``, you'll see
the regular HTML page because ``html`` is the default format. When visiting
``http://localhost:8000/hello/fabien.html`` you'll get again the HTML page,
this time because you explicitly asked for the ``html`` format. Lastly,
if you visit ``http://localhost:8000/hello/fabien.xml`` you'll see the new
XML template rendered in your browser.

That's all there is to it. For standard formats, Symfony will also
automatically choose the best ``Content-Type`` header for the response.
To restrict the formats supported by a given action, use the ``requirements``
option of the ``@Route()`` annotation::

    // src/AppBundle/Controller/DefaultController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route("/hello/{name}.{_format}",
     *     defaults = {"_format"="html"},
     *     requirements = { "_format" = "html|xml|json" },
     *     name = "hello"
     * )
     */
    public function helloAction($name, $_format)
    {
        return $this->render('default/hello.'.$_format.'.twig', array(
            'name' => $name
        ));
    }

The ``hello`` action will now match URLs like ``/hello/fabien.xml`` or
``/hello/fabien.json``, but it will show a 404 error if you try to get URLs
like ``/hello/fabien.js``, because the value of the ``_format`` variable
doesn't meet its requirements.

.. _redirecting-and-forwarding:

Redirecting
-----------

If you want to redirect the user to another page, use the ``redirectToRoute()``
method::

    // src/AppBundle/Controller/DefaultController.php
    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            return $this->redirectToRoute('hello', array('name' => 'Fabien'));
        }
    }

The ``redirectToRoute()`` method takes as arguments the route name and an
optional array of parameters and redirects the user to the URL generated
with those arguments.

Displaying Error Pages
----------------------

Errors will inevitably happen during the execution of every web application.
In the case of ``404`` errors, Symfony includes a handy shortcut that you
can use in your controllers::

    // src/AppBundle/Controller/DefaultController.php
    // ...

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            // ...
            throw $this->createNotFoundException();
        }
    }

For ``500`` errors, just throw a regular PHP exception inside the controller
and Symfony will transform it into a proper ``500`` error page::

    // src/AppBundle/Controller/DefaultController.php
    // ...

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            // ...
            throw new \Exception('Something went horribly wrong!');
        }
    }

Getting Information from the Request
------------------------------------

Sometimes your controllers need to access the information related to the
user request, such as their preferred language, IP address or the URL query
parameters. To get access to this information, add a new argument of type
``Request`` to the action. The name of this new argument doesn't matter,
but it must be preceded by the ``Request`` type in order to work (don't
forget to add the new ``use`` statement that imports this ``Request`` class)::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction(Request $request)
        {
            // is it an Ajax request?
            $isAjax = $request->isXmlHttpRequest();

            // what's the preferred language of the user?
            $language = $request->getPreferredLanguage(array('en', 'fr'));

            // get the value of a $_GET parameter
            $pageName = $request->query->get('page');

            // get the value of a $_POST parameter
            $pageName = $request->request->get('page');
        }
    }

In a template, you can also access the ``Request`` object via the special
``app.request`` variable automatically provided by Symfony:

.. code-block:: html+twig

    {{ app.request.query.get('page') }}

    {{ app.request.request.get('page') }}

Persisting Data in the Session
------------------------------

Even if the HTTP protocol is stateless, Symfony provides a nice session
object that represents the client (be it a real person using a browser,
a bot, or a web service). Between two requests, Symfony stores the attributes
in a cookie by using native PHP sessions.

Storing and retrieving information from the session can be easily achieved
from any controller::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $session = $request->getSession();

        // store an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // get the value of a session attribute
        $foo = $session->get('foo');

        // use a default value if the attribute doesn't exist
        $foo = $session->get('foo', 'default_value');
    }

You can also store "flash messages" that will auto-delete after the next
request. They are useful when you need to set a success message before
redirecting the user to another page (which will then show the message)::

    public function indexAction(Request $request)
    {
        // ...

        // store a message for the very next request
        $this->addFlash('notice', 'Congratulations, your action succeeded!');
    }

And you can display the flash message in the template like this:

.. code-block:: html+twig

    {% for flashMessage in app.session.flashbag.get('notice') %}
        <div class="flash-notice">
            {{ flashMessage }}
        </div>
    {% endfor %}

Final Thoughts
--------------

That's all there is to it and I'm not even sure you'll have spent the full
10 minutes. You were briefly introduced to bundles in the first part and
all the features you've learned about so far are part of the core FrameworkBundle.
But thanks to bundles, everything in Symfony can be extended or replaced.
That's the topic of the :doc:`next part of this tutorial <the_architecture>`.
