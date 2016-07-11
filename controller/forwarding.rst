.. index::
    single: Controller; Forwarding

How to Forward Requests to another Controller
=============================================

Though not very common, you can also forward to another controller internally
with the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::forward`
method. Instead of redirecting the user's browser, this makes an "internal"
sub-request and calls the defined controller. The ``forward()`` method returns
the :class:`Symfony\Component\HttpFoundation\Response` object that is returned
from *that* controller::

    public function indexAction($name)
    {
        $response = $this->forward('AppBundle:Something:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ));

        // ... further modify the response or return it directly

        return $response;
    }

The array passed to the method becomes the arguments for the resulting controller.
The target controller method might look something like this::

    public function fancyAction($name, $color)
    {
        // ... create and return a Response object
    }

Just like when creating a controller for a route, the order of the arguments
of ``fancyAction()`` doesn't matter: the matching is done by name.
