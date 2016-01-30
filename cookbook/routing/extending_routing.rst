Extending the Routing System
============================

The routing system has several extension points:

* You can use the ``@ParamConverter`` mechanism of FrameworkExtraBundle_
  to convert controller arguments into objects.
* You can write :doc:`custom route loaders <custom_route_loader>`;
* You can write your own ``Router`` instance and
  :doc:`combine several routers <multiple_routers>`;
* Some bundles overwrite core routing services to alter the route loading
  process, e.g. JmsI18nRoutingBundle_.

.. _FrameworkExtraBundle: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _JMSI18nRoutingBundle: https://github.com/schmittjoh/JMSI18nRoutingBundle
