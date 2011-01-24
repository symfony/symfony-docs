.. index::
   single: Controller
   single: MVC; Controller

The Controller
==============

Still with us after the first two parts? You are already becoming a Symfony2
addict! Without further ado, let's discover what controllers can do for you.

.. index::
   single: Formats
   single: Controller; Formats
   single: Routing; Formats
   single: View; Formats

Using Formats
-------------

Nowadays, a web application should be able to deliver more than just HTML
pages. From XML for RSS feeds or Web Services, to JSON for Ajax requests,
there are plenty of different formats to choose from. Supporting those formats
in Symfony2 is straightforward. Edit ``routing.yml`` and add a ``_format``
with a value of ``xml``:

.. configuration-block::

    .. code-block:: yaml

        # src/Application/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/{name}
            defaults: { _controller: HelloBundle:Hello:index, _format: xml }

    .. code-block:: xml

        <!-- src/Application/HelloBundle/Resources/config/routing.xml -->
        <route id="hello" pattern="/hello/{name}">
            <default key="_controller">HelloBundle:Hello:index</default>
            <default key="_format">xml</default>
        </route>

    .. code-block:: php

        // src/Application/HelloBundle/Resources/config/routing.php
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'HelloBundle:Hello:index',
            '_format'     => 'xml',
        )));

Then, add an ``index.xml.twig`` template along side ``index.html.twig``:

.. code-block:: xml+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.xml.twig -->
    <hello>
        <name>{{ name }}</name>
    </hello>

Finally, as the template needs to be selected according to the format, make
the following changes to the controller:

.. code-block:: php

    // src/Application/HelloBundle/Controller/HelloController.php
    public function indexAction($name, $_format)
    {
        return $this->render(
            'HelloBundle:Hello:index.'.$_format.'.twig',
            array('name' => $name)
        );
    }

That's all there is to it. For standard formats, Symfony2 will automatically
choose the best ``Content-Type`` header for the response. If you want to
support different formats for a single action, use the ``{_format}``
placeholder in the pattern instead:

.. configuration-block::

    .. code-block:: yaml

        # src/Application/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:      /hello/{name}.{_format}
            defaults:     { _controller: HelloBundle:Hello:index, _format: html }
            requirements: { _format: (html|xml|json) }

    .. code-block:: xml

        <!-- src/Application/HelloBundle/Resources/config/routing.xml -->
        <route id="hello" pattern="/hello/{name}.{_format}">
            <default key="_controller">HelloBundle:Hello:index</default>
            <default key="_format">html</default>
            <requirement key="_format">(html|xml|json)</requirement>
        </route>

    .. code-block:: php

        // src/Application/HelloBundle/Resources/config/routing.php
        $collection->add('hello', new Route('/hello/{name}.{_format}', array(
            '_controller' => 'HelloBundle:Hello:index',
            '_format'     => 'html',
        ), array(
            '_format' => '(html|xml|json)',
        )));

The controller will now be called for URLs like ``/hello/Fabien.xml`` or
``/hello/Fabien.json``.

The ``requirements`` entry defines regular expressions that placeholders must
match. In this example, if you try to request the ``/hello/Fabien.js``
resource, you will get a 404 HTTP error, as it does not match the ``_format``
requirement.

.. index::
   single: Response

The Response Object
-------------------

Now, let's get back to the ``Hello`` controller::

    // src/Application/HelloBundle/Controller/HelloController.php

    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index.html.twig', array('name' => $name));
    }

The ``render()`` method renders a template and returns a ``Response`` object.
The response can be tweaked before it is sent to the browser, for instance
let's change the ``Content-Type``::

    public function indexAction($name)
    {
        $response = $this->render('HelloBundle:Hello:index.html.twig', array('name' => $name));
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

For simple templates, you can even create a ``Response`` object by hand and save
some milliseconds::

    public function indexAction($name)
    {
        return new Response('Hello '.$name);
    }

This is really useful when a controller needs to send back a JSON response for
an Ajax request.

.. index::
   single: Exceptions

Managing Errors
---------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. This is easily done by throwing a built-in HTTP
exception::

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    public function indexAction()
    {
        $product = // retrieve the object from database
        if (!$product) {
            throw new NotFoundHttpException('The product does not exist.');
        }

        return $this->render(...);
    }

The ``NotFoundHttpException`` will return a 404 HTTP response back to the
browser.

.. index::
   single: Controller; Redirect
   single: Controller; Forward

Redirecting and Forwarding
--------------------------

If you want to redirect the user to another page, use the ``redirect()`` method::

    return $this->redirect($this->generateUrl('hello', array('name' => 'Lucas')));

The ``generateUrl()`` is the same method as the ``generate()`` method we used
on the ``router`` helper before. It takes the route name and an array of
parameters as arguments and returns the associated friendly URL.

You can also easily forward the action to another one with the ``forward()``
method. As for the ``actions`` helper, it makes an internal sub-request, but it
returns the ``Response`` object to allow for further modification::

    $response = $this->forward('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green'));

    // do something with the response or return it directly

.. index::
   single: Request

The Request Object
------------------

Besides the values of the routing placeholders, the controller also has access
to the ``Request`` object::

    $request = $this->get('request');

    $request->isXmlHttpRequest(); // is it an Ajax request?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->query->get('page'); // get a $_GET parameter

    $request->request->get('page'); // get a $_POST parameter

In a template, you can also access the ``Request`` object via the
``app.request`` variable:

.. code-block:: html+php

    {{ app.request.query.get('page') }}

    {{ app.request.parameter('page') }}

The Session
-----------

Even if the HTTP protocol is stateless, Symfony2 provides a nice session object
that represents the client (be it a real person using a browser, a bot, or a
web service). Between two requests, Symfony2 stores the attributes in a cookie
by using the native PHP sessions.

Storing and retrieving information from the session can be easily achieved
from any controller::

    $session = $this->get('request')->getSession();

    // store an attribute for reuse during a later user request
    $session->set('foo', 'bar');

    // in another controller for another request
    $foo = $session->get('foo');

    // set the user locale
    $session->setLocale('fr');

You can also store small messages that will only be available for the very
next request::

    // store a message for the very next request (in a controller)
    $session->setFlash('notice', 'Congratulations, your action succeeded!');

    // display the message back in the next request (in a template)
    {{ app.session.flash('notice') }}

Final Thoughts
--------------

That's all there is to it, and I'm not even sure we have spent the allocated
10 minutes. We briefly introduced bundles in the first part; and all the
features we've learned about until now are part of the core framework bundle.
But thanks to bundles, everything can be extended or replaced in Symfony2.
That's the topic of the next part of this tutorial.
