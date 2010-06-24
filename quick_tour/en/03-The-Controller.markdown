Symfony2 Quick Tour: The Controller
===================================

Still with us after the first two parts? You are already becoming a Symfony2
addict! Without further ado, let's discover what controllers can do for you.

Formats
-------

Nowadays, a web application should be able to deliver more than just HTML
pages. From XML for RSS feeds or Web Services, to JSON for Ajax requests,
there are plenty of different formats to choose from. Supporting those formats
in Symfony is straightforward. Edit `routing.yml` and add a `_format` with a
value of `xml`:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
        pattern:  /hello/:name
        defaults: { _controller: HelloBundle:Hello:index, _format: xml }

Then, add an `index.xml.php` template along side `index.php`:

    [xml]
    # src/Application/HelloBundle/Resources/views/Hello/index.xml.php
    <hello>
        <name><?php echo $name ?></name>
    </hello>

That's all there is to it. No need to change the controller. For standard
formats, Symfony will also automatically choose the best `Content-Type` header
for the response. If you want to support different formats for a single
action, use the `:_format` placeholder in the pattern instead:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
        pattern:      /hello/:name.:_format
        defaults:     { _controller: HelloBundle:Hello:index, _format: html }
        requirements: { _format: (html|xml|json) }

The controller will now be called for URLs like `/hello/Fabien.xml` or
`/hello/Fabien.json`. As the default value for `_format` is `html`, the
`/hello/Fabien` and `/hello/Fabien.html` will both match for the `html`
format.

The `requirements` entry defines regular expressions that placeholders must
match. In this example, if you try to request the `/hello/Fabien.js` resource,
you will get a 404 HTTP error, as it does not match the `_format` requirement.

The Response Object
-------------------

Now, let's get back to the `Hello` controller.

    [php]
    public function indexAction($name)
    {
        return $this->render('HelloBundle:Hello:index', array('name' => $name));
    }

The `render()` method renders a template and returns a `Response` object. The
response can be tweaked before it is sent to the browser, for instance to
change the default `Content-Type`:

    [php]
    public function indexAction($name)
    {
        $response = $this->render('HelloBundle:Hello:index', array('name' => $name));
        $response->setHeader('Content-Type', 'text/plain');

        return $response;
    }

For simple templates, you can even create a `Response` object by hand and save
some milliseconds:

    [php]
    public function indexAction($name)
    {
        return $this->createResponse('Hello '.$name);
    }

This is really useful when a controller needs to send back a JSON response for
an Ajax request.

Error Management
----------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. This is easily done by throwing a built-in HTTP
exception:

    [php]
    use Symfony\Components\RequestHandler\Exception\NotFoundHttpException;

    public function indexAction()
    {
        $product = // retrieve the object from database
        if (!$product) {
            throw new NotFoundHttpException('The product does not exist.');
        }

        return $this->render(...);
    }

The `NotFoundHttpException` will return a 404 HTTP response back to the
browser. Similarly, `ForbiddenHttpException` returns a 403 error and
`UnauthorizedHttpException` a 401 one. For any other HTTP error code, you can
use the base `HttpException` and pass the HTTP error as the exception code:

    [php]
    throw new HttpException('Unauthorized access.', 401);

Redirecting and Forwarding
--------------------------

If you want to redirect the user to another page, use the `redirect()` method:

    [php]
    $this->redirect($this->generateUrl('hello', array('name' => 'Lucas')));

The `generateUrl()` is the same method as the `generate()` method we used on
the `router` helper before. It takes the route name and an array of parameters
as arguments and returns the associated friendly URL.

You can also easily forward the action to another one with the `forward()`
method. As for the `$view->actions` helper, it makes an internal sub-request,
but it returns the `Response` object to allow for further modification if the
need arises:

    [php]
    $response = $this->forward('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green'));

    // do something with the response or return it directly

The Request Object
------------------

Besides the values of the routing placeholders, the controller also has access
to the `Request` object:

    [php]
    $request = $this->getRequest();

    $request->isXmlHttpRequest(); // is it an Ajax request?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->query->get('page'); // get a $_GET parameter

    $request->request->get('page'); // get a $_POST parameter

In a template, you can also access the request object via the `request`
helper:

    [php]
    <?php echo $view->request->getParameter('page') ?>

The User
--------

Even if the HTTP protocol is stateless, Symfony provides a nice user object
that represents the client (be it a real person using a browser, a bot, or a
web service). Between two requests, Symfony stores the attributes in a cookie
by using the native PHP sessions.

This feature is provided by `FoundationBundle` and it can be enabled by adding the
following line to `config.yml`:

    [yml]
    # hello/config/config.yml
    web.user: ~

Storing and retrieving information from the user can be easily achieved from
any controller:

    [php]
    // store an attribute for reuse during a later user request
    $this->getUser()->setAttribute('foo', 'bar');

    // in another controller for another request
    $foo = $this->getUser()->getAttribute('foo');

    // get/set the user culture
    $this->getUser()->setCulture('fr');

You can also store small messages that will only be available for the very
next request:

    [php]
    // store a message for the very next request
    $this->getUser()->setFlash('notice', 'Congratulations, your action succeeded!');

    // get the message back in the next request
    $notice = $this->getUser()->getFlash('notice');

Final Thoughts
--------------

That's all there is to it, and I'm not even sure we have spent the allocated
10 minutes. In the previous part, we saw how to extend the templating system
with helpers. But everything can extended or replaced in Symfony2 with
bundles. That's the topic of the next part of this tutorial.
