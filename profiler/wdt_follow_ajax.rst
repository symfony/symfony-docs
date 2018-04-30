.. index::
    single: Profiling: WDT Auto-update after AJAX Request

How to make the Web Debug Toolbar Auto-update after AJAX Requests
================================================================

For single page applications it would be more convenient if the toolbar 
showed the information for the most recent AJAX request instead of the 
initial page load.

By setting the `Symfony-Debug-Toolbar-Replace` header to a value of `1` in the
AJAX request, the toolbar will be automatically reloaded for the request. The
header can be set on the response object::

    $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);

Only setting the header during development
-------------------------------------------

Ideally this header should only be set during development and not for
production. This can be accomplished by setting the header in a
:ref:`kernel.response <component-http-kernel-kernel-response>` event listener::

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
    }

.. seealso::

    Read more Symfony events :ref:`/reference/events`.

If you are using Symfony Flex, you should define your event listener service in the 
``config/services_dev.yml`` file so that it only exists in the ``dev`` environment.

.. seealso::

    Read more on creating dev only services :ref:`/configuration/configuration_organization`.
