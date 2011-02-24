.. index::
   single: Controller; As Services

How to define Controllers as Services
=====================================

In the book, you've learned how easily a controller can be used when it
extends the base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class. While
this works fine, controllers can also be specified as services.

To refer to a controller that's defined as a service, use the single colon (:)
notation. For example, suppose we've defined a service called
``my_controller`` and we want to forward to a method called ``indexAction()``
inside the service::

    $this->forward('my_controller:indexAction', array('foo' => $bar));

You need to use the same notation when defining the route ``_controller``
value:

.. code-block:: yaml

    my_controller:
        pattern:   /
        defaults:  { _controller: my_controller:indexAction }

To use a controller in this way, it must be defined in the service container
configuration. For more information, see the :doc:`Service Container
</book/service_container>` chapter.

When using a controller defined as a service, it will most likely not extend
the base ``Controller`` class. Instead of relying on its shortcut methods,
you'll interact directly with the services that you need. Fortunately, this is
usually pretty easy and the base ``Controller`` class itself is a great source
on how to perform many common tasks.

.. note::

    Specifying a controller as a service takes a little bit more work. The
    primary advantage is that the entire controller or any services passed to
    the controller can be modified via the service container configuration.
    This is especially useful when developing an open-source bundle or any
    bundle that will be used in many different projects. So, even if you don't
    specify your controllers as services, you'll likely see this done in some
    open-source Symfony2 bundles.
