Symfony Attributes Overview
===========================

Attributes are the successor of annotations since PHP 8. Attributes are native
to the language and Symfony takes full advantage of them across the framework
and its different components.

Doctrine Bridge
~~~~~~~~~~~~~~~

* :doc:`UniqueEntity </reference/constraints/UniqueEntity>`

Command
~~~~~~~

* :ref:`AsCommand <console_registering-the-command>`

Contracts
~~~~~~~~~

* :ref:`Required <autowiring-calls>`
* :ref:`SubscribedService <service-subscribers-service-subscriber-trait>`

Dependency Injection
~~~~~~~~~~~~~~~~~~~~

* :ref:`AsTaggedItem <tags_as-tagged-item>`
* :ref:`Autoconfigure <lazy-services_configuration>`
* :ref:`AutoconfigureTag <di-instanceof>`
* :ref:`TaggedIterator <tags_reference-tagged-services>`
* :ref:`TaggedLocator <service-subscribers-locators_defining-service-locator>`
* :ref:`Target <autowiring-multiple-implementations-same-type>`
* :ref:`When <service-container_limiting-to-env>`

EventDispatcher
~~~~~~~~~~~~~~~

* :ref:`AsEventListener <event-dispatcher_event-listener-attributes>`

HttpKernel
~~~~~~~~~~

* :doc:`AsController </controller/service>`

Messenger
~~~~~~~~~

* :ref:`AsMessageHandler <messenger-handler>`

Routing
~~~~~~~

* :doc:`Route </routing>`

Security
~~~~~~~~

* :ref:`CurrentUser <security-json-login>`

Serializer
~~~~~~~~~~

* :ref:`Context <serializer_serializer-context>`
* :ref:`DiscriminatorMap <serializer_interfaces-and-abstract-classes>`
* :ref:`Groups <component-serializer-attributes-groups-annotations>`
* :ref:`Ignore <serializer_ignoring-attributes>`
* :ref:`MaxDepth <serializer_handling-serialization-depth>`
* :ref:`SerializedName <serializer_name-conversion>`

Symfony UX
~~~~~~~~~~

* `AsEntityAutocompleteField`_
* `AsLiveComponent`_
* `AsTwigComponent`_
* `Broadcast`_

Validator
~~~~~~~~~

Each validation constraint comes with a PHP attribute. See
:doc:`/reference/constraints` for a full list of validation constraints.

.. _`AsEntityAutocompleteField`: https://symfony.com/bundles/ux-autocomplete/current/index.html#usage-in-a-form-with-ajax
.. _`AsLiveComponent`: https://symfony.com/bundles/ux-live-component/current/index.html
.. _`AsTwigComponent`: https://symfony.com/bundles/ux-twig-component/current/index.html
.. _`Broadcast`: https://symfony.com/bundles/ux-turbo/current/index.html#broadcast-conventions-and-configuration
