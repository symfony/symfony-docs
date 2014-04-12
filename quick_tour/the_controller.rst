The Controller
==============

Still here after the first two parts? You are already becoming a Symfony2
addict! Without further ado, discover what controllers can do for you.

Using Formats
-------------

Nowadays, a web application should be able to deliver more than just HTML
pages. From XML for RSS feeds or Web Services, to JSON for Ajax requests,
there are plenty of different formats to choose from. Supporting those formats
in Symfony2 is straightforward. Tweak the route by adding a default value of
``xml`` for the ``_format`` variable::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route("/hello/{name}", defaults={"_format"="xml"}, name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

By using the request format (as defined by the special ``_format`` variable),
Symfony2 automatically selects the right template, here ``hello.xml.twig``:

.. code-block:: xml+php

    <!-- src/Acme/DemoBundle/Resources/views/Demo/hello.xml.twig -->
    <hello>
        <name>{{ name }}</name>
    </hello>

That's all there is to it. For standard formats, Symfony2 will also
automatically choose the best ``Content-Type`` header for the response. If
you want to support different formats for a single action, use the ``{_format}``
placeholder in the route path instead::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route(
     *     "/hello/{name}.{_format}",
     *     defaults = { "_format" = "html" },
     *     requirements = { "_format" = "html|xml|json" },
     *     name = "_demo_hello"
     * )
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

The controller will now match URLs like ``/demo/hello/Fabien.xml`` or
``/demo/hello/Fabien.json``.

The ``requirements`` entry defines regular expressions that variables must
match. In this example, if you try to request the ``/demo/hello/Fabien.js``
resource, you will get a 404 HTTP error, as it does not match the ``_format``
requirement.

Redirecting and Forwarding
--------------------------

If you want to redirect the user to another page, use the ``redirect()``
method::

    return $this->redirect($this->generateUrl('_demo_hello', array('name' => 'Lucas')));

The ``generateUrl()`` is the same method as the ``path()`` function used in the
templates. It takes the route name and an array of parameters as arguments and
returns the associated friendly URL.

You can also internally forward the action to another using the ``forward()``
method::

    return $this->forward('AcmeDemoBundle:Hello:fancy', array(
        'name'  => $name,
        'color' => 'green'
    ));

Displaying Error Pages
----------------------

Errors will inevitably happen during the execution of every web application.
In the case of ``404`` errors, Symfony includes a handy shortcut that you can
use in your controllers::

    throw $this->createNotFoundException();

For ``500`` errors, just throw a regular PHP exception inside the controller and
Symfony will transform it into a proper ``500`` error page::

    throw new \Exception('Something went wrong!');

Getting Information from the Request
------------------------------------

Symfony automatically injects the ``Request`` object when the controller has an
argument that's type hinted with ``Symfony\Component\HttpFoundation\Request``::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $request->isXmlHttpRequest(); // is it an Ajax request?

        $request->getPreferredLanguage(array('en', 'fr'));

        $request->query->get('page');   // get a $_GET parameter

        $request->request->get('page'); // get a $_POST parameter
    }

In a template, you can also access the ``Request`` object via the
``app.request`` variable:

.. code-block:: html+jinja

    {{ app.request.query.get('page') }}

    {{ app.request.parameter('page') }}

Persisting Data in the Session
------------------------------

Even if the HTTP protocol is stateless, Symfony2 provides a nice session object
that represents the client (be it a real person using a browser, a bot, or a
web service). Between two requests, Symfony2 stores the attributes in a cookie
by using native PHP sessions.

Storing and retrieving information from the session can be easily achieved
from any controller::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $session = $this->request->getSession();

        // store an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // get the value of a session attribute
        $foo = $session->get('foo');

        // use a default value if the attribute doesn't exist
        $foo = $session->get('foo', 'default_value');
    }

You can also store "flash messages" that will auto-delete after the next request.
They are useful when you need to set a success message before redirecting the
user to another page (which will then show the message)::

    // store a message for the very next request (in a controller)
    $session->getFlashBag()->add('notice', 'Congratulations, your action succeeded!');

.. code-block:: html+jinja

    {# display the flash message in the template #}
    <div>{{ app.session.flashbag.get('notice') }}</div>

Caching Resources
-----------------

As soon as your website starts to generate more traffic, you will want to
avoid generating the same resource again and again. Symfony2 uses HTTP cache
headers to manage resources cache. For simple caching strategies, use the
convenient ``@Cache()`` annotation::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template()
     * @Cache(maxage="86400")
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

In this example, the resource will be cached for a day (``86400`` seconds).
Resource caching is managed by Symfony2 itself. But because caching is managed
using standard HTTP cache headers, you can use Varnish or Squid without having
to modify a single line of code in your application.

Final Thoughts
--------------

That's all there is to it, and I'm not even sure you'll have spent the full
10 minutes. You were briefly introduced to bundles in the first part, and all the
features you've learned about so far are part of the core framework bundle.
But thanks to bundles, everything in Symfony2 can be extended or replaced.
That's the topic of the :doc:`next part of this tutorial<the_architecture>`.
