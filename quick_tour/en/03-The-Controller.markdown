A Quick Tour of Symfony 2.0: The Controller
===========================================

Still with us after the first two parts? You are already becoming a Symfony
addict! Without further ado, let's discover what controllers can do for you in
this third part.

Formats
-------

Nowadays, a web application should be able to deliver more than just HTML
pages. From XML for RSS feeds or Web Services to JSON for Ajax requests, there
are plenty of different formats to choose from. Supporting those formats in
Symfony is straightforward. Edit `routing.yml` and add a `_format` with a
value of `xml`:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index, _format: xml }

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
      defaults:     { _bundle: HelloBundle, _controller: Hello, _action: index, _format: html }
      requirements: { _format: (html|xml|json) }

The controller will now be called for URLs like `/hello/Fabien.xml` or
`/hello/Fabien.json`. As the default value for `_format` is `html`, the
`/hello/Fabien` and `/hello/Fabien.html` will both match for the `html`
format.

The `requirements` entry defines regular expressions that placeholders must
match. In this example, if you try to request the `/hello/Fabien.js` resource,
you will get a 404 HTTP error, as it does not match the `_route` requirement.

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

This is more useful when a controller needs to send back a JSON response, for
an Ajax request for instance.

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
      if (!$product)
      {
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

    $request->getQueryParameter('page'); // get a $_GET parameter

    $request->getRequestParameter('page'); // get a $_POST parameter

In a template, you can also access the request object via the `request`
helper:

    [php]
    <?php echo $view->request->getParameter('page') ?>

Final Thoughts
--------------

That's all there is to it, and I'm not even sure we have spent the allocated
10 minutes. In the previous part, we have seen how to extend the templating
system with helpers. Extending the controller can also be easily done thanks
to bundles. That's the topic of the next part of this tutorial.
