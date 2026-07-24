How to Forward Requests to another Controller
=============================================

Though not very common, you can also forward to another controller internally
with the ``forward()`` method provided by the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController`
class.

Instead of redirecting the user's browser, this makes an "internal" sub-request
and calls the defined controller. The ``forward()`` method returns the
:class:`Symfony\\Component\\HttpFoundation\\Response` object that is returned
from *that* controller::

    public function index(string $name): Response
    {
        $response = $this->forward('App\Controller\OtherController::fancy', [
            'name'  => $name,
            'color' => 'green',
        ]);

        // ... further modify the response or return it directly

        return $response;
    }

The array passed to the method becomes the arguments for the resulting controller.
The target controller method might look something like this::

    public function fancy(string $name, string $color): Response
    {
        // ... create and return a Response object
    }

Like when creating a controller for a route, the order of the arguments of the
target method (``fancy()`` in the above example) doesn't matter: the matching is done by name.

.. note::

    Twig's ``app.current_route``, ``app.current_route_parameters``, and
    ``_route_params`` will be empty after such a ``->forward()`` call. However,
    you can set them manually by adding ``_route`` and ``_route_params`` keys
    to the array passed as the second argument of ``forward()``::

        $response = $this->forward('App\Controller\OtherController::fancy', [
            // ...
            '_route' => '...',
            '_route_params' => ['...' => '...'],
        ]);
