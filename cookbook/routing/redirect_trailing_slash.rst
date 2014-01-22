.. index::
    single: Routing; Redirect URLs with a trailing slash

Redirect URLs with a Trailing Slash
===================================

The goal of this cookbook is to demonstrate how to redirect URLs with a
trailing slash to the same URL without a trailing slash
(for example ``/en/blog/`` to ``/en/blog``).

Create a controller that will match any URL with a trailing slash, remove
the trailing slash (keeping query parameters if any) and redirect to the
new URL with a 301 response status code::

    // src/Acme/DemoBundle/Controller/RedirectingController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;

    class RedirectingController extends Controller
    {
        public function removeTrailingSlashAction(Request $request)
        {
            $pathInfo = $request->getPathInfo();
            $requestUri = $request->getRequestUri();

            $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

            return $this->redirect($url, 301);
        }
    }

After that, create a route to this controller that's matched whenever a URL
with a trailing slash is requested. Be sure to put this route last in your
system, as explained below:

.. configuration-block::

    .. code-block:: yaml

        remove_trailing_slash:
            path: /{url}
            defaults: { _controller: AcmeDemoBundle:Redirecting:removeTrailingSlash }
            requirements:
                url: .*/$
                _method: GET

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing">
            <route id="remove_trailing_slash" path="/{url}">
                <default key="_controller">AcmeDemoBundle:Redirecting:removeTrailingSlash</default>
                <requirement key="url">.*/$</requirement>
                <requirement key="_method">GET</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add(
            'remove_trailing_slash',
            new Route(
                '/{url}',
                array(
                    '_controller' => 'AcmeDemoBundle:Redirecting:removeTrailingSlash',
                ),
                array(
                    'url' => '.*/$',
                    '_method' => 'GET',
                )
            )
        );

.. note::

    Redirecting a POST request does not work well in old browsers. A 302
    on a POST request would send a GET request after the redirection for legacy
    reasons. For that reason, the route here only matches GET requests.

.. caution::

    Make sure to include this route in your routing configuration at the
    very end of your route listing. Otherwise, you risk redirecting real
    routes (including Symfony2 core routes) that actually *do* have a trailing
    slash in their path.
