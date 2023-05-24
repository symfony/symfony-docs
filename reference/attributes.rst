Symfony Attributes Overview
===========================

Attributes are the successor of annotations since PHP 8. Attributes are native
to the language and Symfony takes full advantage of them across the framework
and its different components.

Doctrine Bridge
~~~~~~~~~~~~~~~

* :doc:`UniqueEntity </reference/constraints/UniqueEntity>`
* :ref:`MapEntity <doctrine-entity-value-resolver>`

Command
~~~~~~~

* :ref:`AsCommand <console_registering-the-command>`

Contracts
~~~~~~~~~

* :ref:`Required <autowiring-calls>`
* :ref:`SubscribedService <service-subscribers-service-subscriber-trait>`

Dependency Injection
~~~~~~~~~~~~~~~~~~~~

* :ref:`AsAlias <services-alias>`
* :doc:`AsDecorator </service_container/service_decoration>`
* :ref:`AsTaggedItem <tags_as-tagged-item>`
* :ref:`Autoconfigure <lazy-services_configuration>`
* :ref:`AutoconfigureTag <di-instanceof>`
* :ref:`Autowire <autowire-attribute>`
* :ref:`AutowireCallable <container_closure-as-argument>`
* :doc:`AutowireDecorated </service_container/service_decoration>`
* :doc:`AutowireServiceClosure </service_container/service_closures>`
* :ref:`Exclude <service-psr4-loader>`
* :ref:`TaggedIterator <tags_reference-tagged-services>`
* :ref:`TaggedLocator <service-subscribers-locators_defining-service-locator>`
* :ref:`Target <autowiring-multiple-implementations-same-type>`
* :ref:`When <service-container_limiting-to-env>`

EventDispatcher
~~~~~~~~~~~~~~~

* :ref:`AsEventListener <event-dispatcher_event-listener-attributes>`

FrameworkBundle
~~~~~~~~~~~~~~~

* :ref:`AsRoutingConditionService <routing-matching-expressions>`

HttpKernel
~~~~~~~~~~

* :doc:`AsController </controller/service>`
* :class:`Symfony\\Component\\HttpKernel\\Attribute\\AsPinnedValueResolver`
* :ref:`Cache <http-cache-expiration-intro>`
* :ref:`MapDateTime <functionality-shipped-with-the-httpkernel>`
* :ref:`MapQueryParameter <controller_map-request>`
* :ref:`MapQueryString <controller_map-request>`
* :ref:`MapRequestPayload <controller_map-request>`
* :class:`Symfony\\Component\\HttpKernel\\Attribute\\ValueResolver`
* :ref:`WithHttpStatus <framework_exceptions>`
* :ref:`WithLogLevel <framework_exceptions>`

Messenger
~~~~~~~~~

* :ref:`AsMessageHandler <messenger-handler>`

Routing
~~~~~~~

* :doc:`Route </routing>`

Security
~~~~~~~~

* :ref:`CurrentUser <security-json-login>`
* :ref:`IsGranted <security-securing-controller-annotations>`

Serializer
~~~~~~~~~~

* :ref:`Context <serializer_serializer-context>`
* :ref:`DiscriminatorMap <serializer_interfaces-and-abstract-classes>`
* :ref:`Groups <component-serializer-attributes-groups-annotations>`
* :ref:`Ignore <serializer_ignoring-attributes>`
* :ref:`MaxDepth <serializer_handling-serialization-depth>`
* :ref:`SerializedName <serializer_name-conversion>`
* :ref:`SerializedPath <serializer-enabling-metadata-cache>`

Twig
~~~~

* :ref:`Template <templates-template-attribute>`

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

* :doc:`HasNamedArgument </validation/custom_constraint>`

.. _`AsEntityAutocompleteField`: https://symfony.com/bundles/ux-autocomplete/current/index.html#usage-in-a-form-with-ajax
.. _`AsLiveComponent`: https://symfony.com/bundles/ux-live-component/current/index.html
.. _`AsTwigComponent`: https://symfony.com/bundles/ux-twig-component/current/index.html
.. _`Broadcast`: https://symfony.com/bundles/ux-turbo/current/index.html#broadcast-conventions-and-configuration
