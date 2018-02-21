.. index::
    single: Routing; Redirect URLs with a trailing slash

Redirect URLs with a Trailing Slash
===================================

The goal of this article is to demonstrate how to redirect URLs with a
trailing slash to the same URL without a trailing slash
(for example ``/en/blog/`` to ``/en/blog``).

Create a controller that will match any URL with a trailing slash, remove
the trailing slash (keeping query parameters if any) and redirect to the
new URL with a 308 (*HTTP Permanent Redirect*) response status code::

    // src/Controller/RedirectingController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;

    class RedirectingController extends Controller
    {
        public function removeTrailingSlash(Request $request)
        {
            $pathInfo = $request->getPathInfo();
            $requestUri = $request->getRequestUri();

            $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

            // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
            // that it does not allow changing the request method (e.g. from POST to GET)
            return $this->redirect($url, 308);
        }
    }

After that, create a route to this controller that's matched whenever a URL
with a trailing slash is requested. Be sure to put this route last in your
system, as explained below:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/RedirectingController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Routing\Annotation\Route;

        class RedirectingController extends Controller
        {
            /**
             * @Route("/{url}", name="remove_trailing_slash",
             *     requirements={"url" = ".*\/$"})
             */
            public function removeTrailingSlash(Request $request)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        remove_trailing_slash:
            path: /{url}
            controller: App\Controller\RedirectingController::removeTrailingSlash
            requirements:
                url: .*/$

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing">
            <route id="remove_trailing_slash" path="/{url}" methods="GET">
                <default key="_controller">App\Controller\RedirectingController::removeTrailingSlash</default>
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
                    '_controller' => 'App\Controller\RedirectingController::removeTrailingSlash',
                ),
                array(
                    'url' => '.*/$',
                )
            )
        );

.. caution::

    Make sure to include this route in your routing configuration at the
    very end of your route listing. Otherwise, you risk redirecting real
    routes (including Symfony core routes) that actually *do* have a trailing
    slash in their path.
