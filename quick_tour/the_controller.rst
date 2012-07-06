The Controller
==============

Still with us after the first two parts? You are already becoming a Symfony2
addict! Without further ado, let's discover what controllers can do for you.

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

By using the request format (as defined by the ``_format`` value), Symfony2
automatically selects the right template, here ``hello.xml.twig``:

.. code-block:: xml+php

    <!-- src/Acme/DemoBundle/Resources/views/Demo/hello.xml.twig -->
    <hello>
        <name>{{ name }}</name>
    </hello>

That's all there is to it. For standard formats, Symfony2 will also
automatically choose the best ``Content-Type`` header for the response. If
you want to support different formats for a single action, use the ``{_format}``
placeholder in the route pattern instead::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route("/hello/{name}.{_format}", defaults={"_format"="html"}, requirements={"_format"="html|xml|json"}, name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

The controller will now be called for URLs like ``/demo/hello/Fabien.xml`` or
``/demo/hello/Fabien.json``.

The ``requirements`` entry defines regular expressions that placeholders must
match. In this example, if you try to request the ``/demo/hello/Fabien.js``
resource, you will get a 404 HTTP error, as it does not match the ``_format``
requirement.

Redirecting and Forwarding
--------------------------

If you want to redirect the user to another page, use the ``redirect()``
method::

    return $this->redirect($this->generateUrl('_demo_hello', array('name' => 'Lucas')));

The ``generateUrl()`` is the same method as the ``path()`` function we used in
templates. It takes the route name and an array of parameters as arguments and
returns the associated friendly URL.

You can also easily forward the action to another one with the ``forward()``
method. Internally, Symfony makes a "sub-request", and returns the ``Response``
object from that sub-request::

    $response = $this->forward('AcmeDemoBundle:Hello:fancy', array('name' => $name, 'color' => 'green'));

    // ... do something with the response or return it directly

Getting information from the Request
------------------------------------

Besides the values of the routing placeholders, the controller also has access
to the ``Request`` object::

    $request = $this->getRequest();

    $request->isXmlHttpRequest(); // is it an Ajax request?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->query->get('page'); // get a $_GET parameter

    $request->request->get('page'); // get a $_POST parameter

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

    $session = $this->getRequest()->getSession();

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

This is useful when you need to set a success message before redirecting
the user to another page (which will then show the message).

Securing Resources
------------------

The Symfony Standard Edition comes with a simple security configuration that
fits most common needs:

.. code-block:: yaml

    # app/config/security.yml
    security:
        encoders:
            Symfony\Component\Security\Core\User\User: plaintext

        role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

        providers:
            in_memory:
                users:
                    user:  { password: userpass, roles: [ 'ROLE_USER' ] }
                    admin: { password: adminpass, roles: [ 'ROLE_ADMIN' ] }

        firewalls:
            dev:
                pattern:  ^/(_(profiler|wdt)|css|images|js)/
                security: false

            login:
                pattern:  ^/demo/secured/login$
                security: false

            secured_area:
                pattern:    ^/demo/secured/
                form_login:
                    check_path: /demo/secured/login_check
                    login_path: /demo/secured/login
                logout:
                    path:   /demo/secured/logout
                    target: /demo/

This configuration requires users to log in for any URL starting with
``/demo/secured/`` and defines two valid users: ``user`` and ``admin``.
Moreover, the ``admin`` user has a ``ROLE_ADMIN`` role, which includes the
``ROLE_USER`` role as well (see the ``role_hierarchy`` setting).

.. tip::

    For readability, passwords are stored in clear text in this simple
    configuration, but you can use any hashing algorithm by tweaking the
    ``encoders`` section.

Going to the ``http://localhost/Symfony/web/app_dev.php/demo/secured/hello``
URL will automatically redirect you to the login form because this resource is
protected by a ``firewall``.

You can also force the action to require a given role by using the ``@Secure``
annotation on the controller::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use JMS\SecurityExtraBundle\Annotation\Secure;

    /**
     * @Route("/hello/admin/{name}", name="_demo_secured_hello_admin")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function helloAdminAction($name)
    {
        return array('name' => $name);
    }

Now, log in as ``user`` (who does *not* have the ``ROLE_ADMIN`` role) and
from the secured hello page, click on the "Hello resource secured" link.
Symfony2 should return a 403 HTTP status code, indicating that the user
is "forbidden" from accessing that resource.

.. note::

    The Symfony2 security layer is very flexible and comes with many different
    user providers (like one for the Doctrine ORM) and authentication providers
    (like HTTP basic, HTTP digest, or X509 certificates). Read the
    ":doc:`/book/security`" chapter of the book for more information
    on how to use and configure them.

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

In this example, the resource will be cached for a day. But you can also use
validation instead of expiration or a combination of both if that fits your
needs better.

Resource caching is managed by the Symfony2 built-in reverse proxy. But because 
caching is managed using regular HTTP cache headers, you can replace the 
built-in reverse proxy with Varnish or Squid and easily scale your application.

.. note::

    But what if you cannot cache whole pages? Symfony2 still has the solution
    via Edge Side Includes (ESI), which are supported natively. Learn more by
    reading the ":doc:`/book/http_cache`" chapter of the book.

Final Thoughts
--------------

That's all there is to it, and I'm not even sure we have spent the full
10 minutes. We briefly introduced bundles in the first part, and all the
features we've learned about so far are part of the core framework bundle.
But thanks to bundles, everything in Symfony2 can be extended or replaced.
That's the topic of the :doc:`next part of this tutorial<the_architecture>`.
