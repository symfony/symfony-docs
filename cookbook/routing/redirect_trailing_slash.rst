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

    // src/AppBundle/Controller/RedirectingController.php
    namespace AppBundle\Controller;

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
            defaults: { _controller: AppBundle:Redirecting:removeTrailingSlash }
            requirements:
                url: .*/$
            methods: [GET]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing">
            <route id="remove_trailing_slash" path="/{url}" methods="GET">
                <default key="_controller">AppBundle:Redirecting:removeTrailingSlash</default>
                <requirement key="url">.*/$</requirement>
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
                    '_controller' => 'AppBundle:Redirecting:removeTrailingSlash',
                ),
                array(
                    'url' => '.*/$',
                ),
                array(),
                '',
                array(),
                array('GET')
            )
        );

.. note::

    Redirecting a POST request does not work well in old browsers. A 302
    on a POST request would send a GET request after the redirection for legacy
    reasons. For that reason, the route here only matches GET requests.

.. caution::

    Make sure to include this route in your routing configuration at the
    very end of your route listing. Otherwise, you risk redirecting real
    routes (including Symfony core routes) that actually *do* have a trailing
    slash in their path.
