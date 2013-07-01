The Dependency Injection Tags
=============================

Dependency Injection Tags are little strings that can be applied to a service
to "flag" it to be used in some special way. For example, if you have a service
that you would like to register as a listener to one of Symfony's core events,
you can flag it with the ``kernel.event_listener`` tag.

You can learn a little bit more about "tags" by reading the ":ref:`book-service-container-tags`"
section of the Service Container chapter.

Below is information about all of the tags available inside Symfony2. There
may also be tags in other bundles you use that aren't listed here.

+-----------------------------------+---------------------------------------------------------------------------+
| Tag Name                          | Usage                                                                     |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.asset`_                  | Register an asset to the current asset manager                            |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.factory_worker`_         | Add a factory worker                                                      |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.filter`_                 | Register a filter                                                         |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.formula_loader`_         | Add a formula loader to the current asset manager                         |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.formula_resource`_       | Adds a resource to the current asset manager                              |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.templating.php`_         | Remove this service if php templating is disabled                         |
+-----------------------------------+---------------------------------------------------------------------------+
| `assetic.templating.twig`_        | Remove this service if twig templating is disabled                        |
+-----------------------------------+---------------------------------------------------------------------------+
| `data_collector`_                 | Create a class that collects custom data for the profiler                 |
+-----------------------------------+---------------------------------------------------------------------------+
| `doctrine.event_listener`_        | Add a Doctrine event listener                                             |
+-----------------------------------+---------------------------------------------------------------------------+
| `doctrine.event_subscriber`_      | Add a Doctrine event subscriber                                           |
+-----------------------------------+---------------------------------------------------------------------------+
| `form.type`_                      | Create a custom form field type                                           |
+-----------------------------------+---------------------------------------------------------------------------+
| `form.type_extension`_            | Create a custom "form extension"                                          |
+-----------------------------------+---------------------------------------------------------------------------+
| `form.type_guesser`_              | Add your own logic for "form type guessing"                               |
+-----------------------------------+---------------------------------------------------------------------------+
| `kernel.cache_clearer`_           | Register your service to be called during the cache clearing process      |
+-----------------------------------+---------------------------------------------------------------------------+
| `kernel.cache_warmer`_            | Register your service to be called during the cache warming process       |
+-----------------------------------+---------------------------------------------------------------------------+
| `kernel.event_listener`_          | Listen to different events/hooks in Symfony                               |
+-----------------------------------+---------------------------------------------------------------------------+
| `kernel.event_subscriber`_        | To subscribe to a set of different events/hooks in Symfony                |
+-----------------------------------+---------------------------------------------------------------------------+
| `kernel.fragment_renderer`_       | Add new HTTP content rendering strategies                                 |
+-----------------------------------+---------------------------------------------------------------------------+
| `monolog.logger`_                 | Logging with a custom logging channel                                     |
+-----------------------------------+---------------------------------------------------------------------------+
| `monolog.processor`_              | Add a custom processor for logging                                        |
+-----------------------------------+---------------------------------------------------------------------------+
| `routing.loader`_                 | Register a custom service that loads routes                               |
+-----------------------------------+---------------------------------------------------------------------------+
| `security.voter`_                 | Add a custom voter to Symfony's authorization logic                       |
+-----------------------------------+---------------------------------------------------------------------------+
| `security.remember_me_aware`_     | To allow remember me authentication                                       |
+-----------------------------------+---------------------------------------------------------------------------+
| `serializer.encoder`_             | Register a new encoder in the ``serializer`` service                      |
+-----------------------------------+---------------------------------------------------------------------------+
| `serializer.normalizer`_          | Register a new normalizer in the ``serializer`` service                   |
+-----------------------------------+---------------------------------------------------------------------------+
| `swiftmailer.plugin`_             | Register a custom SwiftMailer Plugin                                      |
+-----------------------------------+---------------------------------------------------------------------------+
| `templating.helper`_              | Make your service available in PHP templates                              |
+-----------------------------------+---------------------------------------------------------------------------+
| `translation.loader`_             | Register a custom service that loads translations                         |
+-----------------------------------+---------------------------------------------------------------------------+
| `translation.extractor`_          | Register a custom service that extracts translation messages from a file  |
+-----------------------------------+---------------------------------------------------------------------------+
| `translation.dumper`_             | Register a custom service that dumps translation messages                 |
+-----------------------------------+---------------------------------------------------------------------------+
| `twig.extension`_                 | Register a custom Twig Extension                                          |
+-----------------------------------+---------------------------------------------------------------------------+
| `twig.loader`_                    | Register a custom service that loads Twig templates                       |
+-----------------------------------+---------------------------------------------------------------------------+
| `validator.constraint_validator`_ | Create your own custom validation constraint                              |
+-----------------------------------+---------------------------------------------------------------------------+
| `validator.initializer`_          | Register a service that initializes objects before validation             |
+-----------------------------------+---------------------------------------------------------------------------+

assetic.asset
-------------

**Purpose**: Register an asset with the current asset manager

assetic.factory_worker
----------------------

**Purpose**: Add a factory worker

A Factory worker is a class implementing ``Assetic\Factory\Worker\WorkerInterface``.
Its ``process($asset)`` method is called for each asset after asset creation.
You can modify an asset or even return a new one.

In order to add a new worker, first create a class::

    use Assetic\Asset\AssetInterface;
    use Assetic\Factory\Worker\WorkerInterface;

    class MyWorker implements WorkerInterface
    {
        public function process(AssetInterface $asset)
        {
            // ... change $asset or return a new one
        }

    }

And then add register it as a tagged service:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme.my_worker:
                class: MyWorker
                tags:
                    - { name: assetic.factory_worker }

    .. code-block:: xml

        <service id="acme.my_worker" class="MyWorker>
            <tag name="assetic.factory_worker" />
        </service>

    .. code-block:: php

        $container
            ->register('acme.my_worker', 'MyWorker')
            ->addTag('assetic.factory_worker')
        ;

assetic.filter
--------------

**Purpose**: Register a filter

AsseticBundle uses this tag to register common filters. You can also use
this tag to register your own filters.

First, you need to create a filter::

    use Assetic\Asset\AssetInterface;
    use Assetic\Filter\FilterInterface;

    class MyFilter implements FilterInterface
    {
        public function filterLoad(AssetInterface $asset)
        {
            $asset->setContent('alert("yo");' . $asset->getContent());
        }

        public function filterDump(AssetInterface $asset)
        {
            // ...
        }
    }

Second, define a service:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme.my_filter:
                class: MyFilter
                tags:
                    - { name: assetic.filter, alias: my_filter }

    .. code-block:: xml

        <service id="acme.my_filter" class="MyFilter">
            <tag name="assetic.filter" alias="my_filter" />
        </service>

    .. code-block:: php

        $container
            ->register('acme.my_filter', 'MyFilter')
            ->addTag('assetic.filter', array('alias' => 'my_filter'))
        ;

Finally, apply the filter:

.. code-block:: jinja

    {% javascripts
        '@AcmeBaseBundle/Resources/public/js/global.js'
        filter='my_filter'
    %}
        <script src="{{ asset_url }}"></script>
    {% endjavascripts %}

You can also apply your filter via the ``assetic.filters.my_filter.apply_to``
config option as it's described here: :doc:`/cookbook/assetic/apply_to_option`.
In order to do that, you must define your filter service in a separate xml
config file and point to this file's path via the ``assetic.filters.my_filter.resource``
configuration key.

assetic.formula_loader
----------------------

**Purpose**: Add a formula loader to the current asset manager

A Formula loader is a class implementing
``Assetic\\Factory\Loader\\FormulaLoaderInterface`` interface. This class
is responsible for loading assets from a particular kind of resources (for
instance, twig template). Assetic ships loaders for php and twig templates.

An ``alias`` attribute defines the name of the loader.

assetic.formula_resource
------------------------

**Purpose**: Adds a resource to the current asset manager

A resource is something formulae can be loaded from. For instance, twig
templates are resources.

assetic.templating.php
----------------------

**Purpose**: Remove this service if php templating is disabled

The tagged service will be removed from the container if the
``framework.templating.engines`` config section does not contain php.

assetic.templating.twig
-----------------------

**Purpose**: Remove this service if twig templating is disabled

The tagged service will be removed from the container if
``framework.templating.engines`` config section does not contain twig.

data_collector
--------------

**Purpose**: Create a class that collects custom data for the profiler

For details on creating your own custom data collection, read the cookbook
article: :doc:`/cookbook/profiler/data_collector`.

doctrine.event_listener
-----------------------

**Purpose**: Add a Doctrine event listener

For details on creating Doctrine event listeners, read the cookbook article:
:doc:`/cookbook/doctrine/event_listeners_subscribers`.

doctrine.event_subscriber
-------------------------

**Purpose**: Add a Doctrine event subscriber

For details on creating Doctrine event subscribers, read the cookbook article:
:doc:`/cookbook/doctrine/event_listeners_subscribers`.

.. _dic-tags-form-type:

form.type
---------

**Purpose**: Create a custom form field type

For details on creating your own custom form type, read the cookbook article:
:doc:`/cookbook/form/create_custom_field_type`.

form.type_extension
-------------------

**Purpose**: Create a custom "form extension"

Form type extensions are a way for you took "hook into" the creation of any
field in your form. For example, the addition of the CSRF token is done via
a form type extension (:class:`Symfony\\Component\\Form\\Extension\\Csrf\\Type\\FormTypeCsrfExtension`).

A form type extension can modify any part of any field in your form. To create
a form type extension, first create a class that implements the
:class:`Symfony\\Component\\Form\\FormTypeExtensionInterface` interface.
For simplicity, you'll often extend an
:class:`Symfony\\Component\\Form\\AbstractTypeExtension` class instead of
the interface directly::

    // src/Acme/MainBundle/Form/Type/MyFormTypeExtension.php
    namespace Acme\MainBundle\Form\Type;

    use Symfony\Component\Form\AbstractTypeExtension;

    class MyFormTypeExtension extends AbstractTypeExtension
    {
        // ... fill in whatever methods you want to override
        // like buildForm(), buildView(), finishView(), setDefaultOptions()
    }

In order for Symfony to know about your form extension and use it, give it
the `form.type_extension` tag:

.. configuration-block::

    .. code-block:: yaml

        services:
            main.form.type.my_form_type_extension:
                class: Acme\MainBundle\Form\Type\MyFormTypeExtension
                tags:
                    - { name: form.type_extension, alias: field }

    .. code-block:: xml

        <service id="main.form.type.my_form_type_extension" class="Acme\MainBundle\Form\Type\MyFormTypeExtension">
            <tag name="form.type_extension" alias="field" />
        </service>

    .. code-block:: php

        $container
            ->register('main.form.type.my_form_type_extension', 'Acme\MainBundle\Form\Type\MyFormTypeExtension')
            ->addTag('form.type_extension', array('alias' => 'field'))
        ;

The ``alias`` key of the tag is the type of field that this extension should
be applied to. For example, to apply the extension to any form/field, use the
"form" value.

form.type_guesser
-----------------

**Purpose**: Add your own logic for "form type guessing"

This tag allows you to add your own logic to the :ref:`Form Guessing<book-forms-field-guessing>`
process. By default, form guessing is done by "guessers" based on the validation
metadata and Doctrine metadata (if you're using Doctrine).

To add your own form type guesser, create a class that implements the
:class:`Symfony\\Component\\Form\\FormTypeGuesserInterface` interface. Next,
tag its service definition with ``form.type_guesser`` (it has no options).

To see an example of how this class might look, see the ``ValidatorTypeGuesser``
class in the ``Form`` component.

kernel.cache_clearer
--------------------

**Purpose**: Register your service to be called during the cache clearing process

Cache clearing occurs whenever you call ``cache:clear`` command. If your
bundle caches files, you should add custom cache clearer for clearing those
files during the cache clearing process.

In order to register your custom cache clearer, first you must create a
service class::

    // src/Acme/MainBundle/Cache/MyClearer.php
    namespace Acme\MainBundle\Cache;

    use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

    class MyClearer implements CacheClearerInterface
    {
        public function clear($cacheDir)
        {
            // clear your cache
        }

    }

Then register this class and tag it with ``kernel.cache:clearer``:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_cache_clearer:
                class: Acme\MainBundle\Cache\MyClearer
                tags:
                    - { name: kernel.cache_clearer }

    .. code-block:: xml

        <service id="my_cache_clearer" class="Acme\MainBundle\Cache\MyClearer">
            <tag name="kernel.cache_clearer" />
        </service>

    .. code-block:: php

        $container
            ->register('my_cache_clearer', 'Acme\MainBundle\Cache\MyClearer')
            ->addTag('kernel.cache_clearer')
        ;

kernel.cache_warmer
-------------------

**Purpose**: Register your service to be called during the cache warming process

Cache warming occurs whenever you run the ``cache:warmup`` or ``cache:clear``
task (unless you pass ``--no-warmup`` to ``cache:clear``). The purpose is
to initialize any cache that will be needed by the application and prevent
the first user from any significant "cache hit" where the cache is generated
dynamically.

To register your own cache warmer, first create a service that implements
the :class:`Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface` interface::

    // src/Acme/MainBundle/Cache/MyCustomWarmer.php
    namespace Acme\MainBundle\Cache;

    use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

    class MyCustomWarmer implements CacheWarmerInterface
    {
        public function warmUp($cacheDir)
        {
            // do some sort of operations to "warm" your cache
        }

        public function isOptional()
        {
            return true;
        }
    }

The ``isOptional`` method should return true if it's possible to use the
application without calling this cache warmer. In Symfony 2.0, optional warmers
are always executed anyways, so this function has no real effect.

To register your warmer with Symfony, give it the kernel.cache_warmer tag:

.. configuration-block::

    .. code-block:: yaml

        services:
            main.warmer.my_custom_warmer:
                class: Acme\MainBundle\Cache\MyCustomWarmer
                tags:
                    - { name: kernel.cache_warmer, priority: 0 }

    .. code-block:: xml

        <service id="main.warmer.my_custom_warmer" class="Acme\MainBundle\Cache\MyCustomWarmer">
            <tag name="kernel.cache_warmer" priority="0" />
        </service>

    .. code-block:: php

        $container
            ->register('main.warmer.my_custom_warmer', 'Acme\MainBundle\Cache\MyCustomWarmer')
            ->addTag('kernel.cache_warmer', array('priority' => 0))
        ;

The ``priority`` value is optional, and defaults to 0. This value can be
from -255 to 255, and the warmers will be executed in the order of their
priority.

.. _dic-tags-kernel-event-listener:

kernel.event_listener
---------------------

**Purpose**: To listen to different events/hooks in Symfony

This tag allows you to hook your own classes into Symfony's process at different
points.

For a full example of this listener, read the :doc:`/cookbook/service_container/event_listener`
cookbook entry.

For another practical example of a kernel listener, see the cookbook
article: :doc:`/cookbook/request/mime_type`.

Core Event Listener Reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When adding your own listeners, it might be useful to know about the other
core Symfony listeners and their priorities.

.. note::

    All listeners listed here may not be listening depending on your environment,
    settings and bundles. Additionally, third-party bundles will bring in
    additional listener not listed here.

kernel.request
..............

+-------------------------------------------------------------------------------------------+-----------+
| Listener Class Name                                                                       | Priority  |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`                  | 1024      |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListener`             | 192       |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener`                 | 128       |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\RouterListener`                    | 32        |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener`                    | 16        |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Component\\Security\\Http\\Firewall`                                     | 8         |
+-------------------------------------------------------------------------------------------+-----------+

kernel.controller
.................

+-------------------------------------------------------------------------------------------+----------+
| Listener Class Name                                                                       | Priority |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RequestDataCollector`            | 0        |
+-------------------------------------------------------------------------------------------+----------+

kernel.response
...............

+-------------------------------------------------------------------------------------------+----------+
| Listener Class Name                                                                       | Priority |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\EsiListener`                       | 0        |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener`                  | 0        |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Bundle\\SecurityBundle\\EventListener\\ResponseListener`                 | 0        |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`                  | -100     |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListener`             | -128     |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`       | -128     |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\StreamedResponseListener`          | -1024    |
+-------------------------------------------------------------------------------------------+----------+

kernel.exception
................

+-------------------------------------------------------------------------------------------+----------+
| Listener Class Name                                                                       | Priority |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`                  | 0        |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`                 | -128     |
+-------------------------------------------------------------------------------------------+----------+

kernel.terminate
................

+-------------------------------------------------------------------------------------------+----------+
| Listener Class Name                                                                       | Priority |
+-------------------------------------------------------------------------------------------+----------+
| :class:`Symfony\\Bundle\\SwiftmailerBundle\\EventListener\\EmailSenderListener`           | 0        |
+-------------------------------------------------------------------------------------------+----------+

.. _dic-tags-kernel-event-subscriber:

kernel.event_subscriber
-----------------------

**Purpose**: To subscribe to a set of different events/hooks in Symfony

To enable a custom subscriber, add it as a regular service in one of your
configuration, and tag it with ``kernel.event_subscriber``:

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.subscriber.your_subscriber_name:
                class: Fully\Qualified\Subscriber\Class\Name
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <service id="kernel.subscriber.your_subscriber_name" class="Fully\Qualified\Subscriber\Class\Name">
            <tag name="kernel.event_subscriber" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.subscriber.your_subscriber_name', 'Fully\Qualified\Subscriber\Class\Name')
            ->addTag('kernel.event_subscriber')
        ;

.. note::

    Your service must implement the :class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`
    interface.

.. note::

    If your service is created by a factory, you **MUST** correctly set the ``class``
    parameter for this tag to work correctly.

kernel.fragment_renderer
------------------------

**Purpose**: Add a new HTTP content rendering strategy.

To add a new rendering strategy - in addition to the core strategies like
``EsiFragmentRenderer`` - create a class that implements
:class:`Symfony\\Component\\HttpKernel\\Fragment\\FragmentRendererInterface`,
register it as a service, then tag it with ``kernel.fragment_renderer``.

.. _dic_tags-monolog:

monolog.logger
--------------

**Purpose**: To use a custom logging channel with Monolog

Monolog allows you to share its handlers between several logging channels.
The logger service uses the channel ``app`` but you can change the
channel when injecting the logger in a service.

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Fully\Qualified\Loader\Class\Name
                arguments: ["@logger"]
                tags:
                    - { name: monolog.logger, channel: acme }

    .. code-block:: xml

        <service id="my_service" class="Fully\Qualified\Loader\Class\Name">
            <argument type="service" id="logger" />
            <tag name="monolog.logger" channel="acme" />
        </service>

    .. code-block:: php

        $definition = new Definition('Fully\Qualified\Loader\Class\Name', array(new Reference('logger'));
        $definition->addTag('monolog.logger', array('channel' => 'acme'));
        $container->register('my_service', $definition);

.. note::

    This works only when the logger service is a constructor argument,
    not when it is injected through a setter.

.. _dic_tags-monolog-processor:

monolog.processor
-----------------

**Purpose**: Add a custom processor for logging

Monolog allows you to add processors in the logger or in the handlers to add
extra data in the records. A processor receives the record as an argument and
must return it after adding some extra data in the ``extra`` attribute of
the record.

Let's see how you can use the built-in ``IntrospectionProcessor`` to add
the file, the line, the class and the method where the logger was triggered.

You can add a processor globally:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor');
        $container->register('my_service', $definition);

.. tip::

    If your service is not a callable (using ``__invoke``) you can add the
    ``method`` attribute in the tag to use a specific method.

You can add also a processor for a specific handler by using the ``handler``
attribute:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor, handler: firephp }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" handler="firephp" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor', array('handler' => 'firephp');
        $container->register('my_service', $definition);

You can also add a processor for a specific logging channel by using the ``channel``
attribute. This will register the processor only for the ``security`` logging
channel used in the Security component:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor, channel: security }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" channel="security" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor', array('channel' => 'security');
        $container->register('my_service', $definition);

.. note::

    You cannot use both the ``handler`` and ``channel`` attributes for the
    same tag as handlers are shared between all channels.

routing.loader
--------------

**Purpose**: Register a custom service that loads routes

To enable a custom routing loader, add it as a regular service in one
of your configuration, and tag it with ``routing.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            routing.loader.your_loader_name:
                class: Fully\Qualified\Loader\Class\Name
                tags:
                    - { name: routing.loader }

    .. code-block:: xml

        <service id="routing.loader.your_loader_name" class="Fully\Qualified\Loader\Class\Name">
            <tag name="routing.loader" />
        </service>

    .. code-block:: php

        $container
            ->register('routing.loader.your_loader_name', 'Fully\Qualified\Loader\Class\Name')
            ->addTag('routing.loader')
        ;

For more information, see :doc:`/cookbook/routing/custom_route_loader`.

security.remember_me_aware
--------------------------

**Purpose**: To allow remember me authentication

This tag is used internally to allow remember-me authentication to work. If
you have a custom authentication method where a user can be remember-me authenticated,
then you may need to use this tag.

If your custom authentication factory extends
:class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\AbstractFactory`
and your custom authentication listener extends
:class:`Symfony\\Component\\Security\\Http\\Firewall\\AbstractAuthenticationListener`,
then your custom authentication listener will automatically have this tagged
applied and it will function automatically.

security.voter
--------------

**Purpose**: To add a custom voter to Symfony's authorization logic

When you call ``isGranted`` on Symfony's security context, a system of "voters"
is used behind the scenes to determine if the user should have access. The
``security.voter`` tag allows you to add your own custom voter to that system.

For more information, read the cookbook article: :doc:`/cookbook/security/voters`.

.. _reference-dic-tags-serializer-encoder:

serializer.encoder
------------------

**Purpose**: Register a new encoder in the ``serializer`` service

The class that's tagged should implement the :class:`Symfony\\Component\\Serializer\\Encoder\\EncoderInterface`
and :class:`Symfony\\Component\\Serializer\\Encoder\\DecoderInterface`.

For more details, see :doc:`/cookbook/serializer`.

.. _reference-dic-tags-serializer-normalizer:

serializer.normalizer
---------------------

**Purpose**: Register a new normalizer in the Serializer service

The class that's tagged should implement the :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface`.

For more details, see :doc:`/cookbook/serializer`.

swiftmailer.plugin
------------------

**Purpose**: Register a custom SwiftMailer Plugin

If you're using a custom SwiftMailer plugin (or want to create one), you can
register it with SwiftMailer by creating a service for your plugin and tagging
it with ``swiftmailer.plugin`` (it has no options).

A SwiftMailer plugin must implement the ``Swift_Events_EventListener`` interface.
For more information on plugins, see `SwiftMailer's Plugin Documentation`_.

Several SwiftMailer plugins are core to Symfony and can be activated via
different configuration. For details, see :doc:`/reference/configuration/swiftmailer`.

templating.helper
-----------------

**Purpose**: Make your service available in PHP templates

To enable a custom template helper, add it as a regular service in one
of your configuration, tag it with ``templating.helper`` and define an
``alias`` attribute (the helper will be accessible via this alias in the
templates):

.. configuration-block::

    .. code-block:: yaml

        services:
            templating.helper.your_helper_name:
                class: Fully\Qualified\Helper\Class\Name
                tags:
                    - { name: templating.helper, alias: alias_name }

    .. code-block:: xml

        <service id="templating.helper.your_helper_name" class="Fully\Qualified\Helper\Class\Name">
            <tag name="templating.helper" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('templating.helper.your_helper_name', 'Fully\Qualified\Helper\Class\Name')
            ->addTag('templating.helper', array('alias' => 'alias_name'))
        ;

translation.loader
------------------

**Purpose**: To register a custom service that loads translations

By default, translations are loaded form the filesystem in a variety of different
formats (YAML, XLIFF, PHP, etc). If you need to load translations from some
other source, first create a class that implements the
:class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface::

    // src/Acme/MainBundle/Translation/MyCustomLoader.php
    namespace Acme\MainBundle\Translation;

    use Symfony\Component\Translation\Loader\LoaderInterface;
    use Symfony\Component\Translation\MessageCatalogue;

    class MyCustomLoader implements LoaderInterface
    {
        public function load($resource, $locale, $domain = 'messages')
        {
            $catalogue = new MessageCatalogue($locale);

            // some how load up some translations from the "resource"
            // then set them into the catalogue
            $catalogue->set('hello.world', 'Hello World!', $domain);

            return $catalogue;
        }
    }

Your custom loader's ``load`` method is responsible for returning a
:Class:`Symfony\\Component\\Translation\\MessageCatalogue`.

Now, register your loader as a service and tag it with ``translation.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            main.translation.my_custom_loader:
                class: Acme\MainBundle\Translation\MyCustomLoader
                tags:
                    - { name: translation.loader, alias: bin }

    .. code-block:: xml

        <service id="main.translation.my_custom_loader" class="Acme\MainBundle\Translation\MyCustomLoader">
            <tag name="translation.loader" alias="bin" />
        </service>

    .. code-block:: php

        $container
            ->register('main.translation.my_custom_loader', 'Acme\MainBundle\Translation\MyCustomLoader')
            ->addTag('translation.loader', array('alias' => 'bin'))
        ;

The ``alias`` option is required and very important: it defines the file
"suffix" that will be used for the resource files that use this loader. For
example, suppose you have some custom ``bin`` format that you need to load.
If you have a ``bin`` file that contains French translations for the ``messages``
domain, then you might have a file ``app/Resources/translations/messages.fr.bin``.

When Symfony tries to load the ``bin`` file, it passes the path to your custom
loader as the ``$resource`` argument. You can then perform any logic you need
on that file in order to load your translations.

If you're loading translations from a database, you'll still need a resource
file, but it might either be blank or contain a little bit of information
about loading those resources from the database. The file is key to trigger
the ``load`` method on your custom loader.

translation.extractor
---------------------

**Purpose**: To register a custom service that extracts messages from a file

.. versionadded:: 2.1
   The ability to add message extractors is new in Symfony 2.1.

When executing the ``translation:update`` command, it uses extractors to
extract translation messages from a file. By default, the Symfony2 framework
has a :class:`Symfony\\Bridge\\TwigBridge\\Translation\\TwigExtractor` and a
:class:`Symfony\\Bundle\\FrameworkBundle\\Translation\\PhpExtractor`, which
help to find and extract translation keys from Twig templates and PHP files.

You can create your own extractor by creating a class that implements
:class:`Symfony\\Component\\Translation\\Extractor\\ExtractorInterface` and
tagging the service with ``translation.extractor``. The tag has one required
option: ``alias``, which defines the name of the extractor::

    // src/Acme/DemoBundle/Translation/FooExtractor.php
    namespace Acme\DemoBundle\Translation;

    use Symfony\Component\Translation\Extractor\ExtractorInterface;
    use Symfony\Component\Translation\MessageCatalogue;

    class FooExtractor implements ExtractorInterface
    {
        protected $prefix;

        /**
         * Extracts translation messages from a template directory to the catalogue.
         */
        public function extract($directory, MessageCatalogue $catalogue)
        {
            // ...
        }

        /**
         * Sets the prefix that should be used for new found messages.
         */
        public function setPrefix($prefix)
        {
            $this->prefix = $prefix;
        }
    }

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo.translation.extractor.foo:
                class: Acme\DemoBundle\Translation\FooExtractor
                tags:
                    - { name: translation.extractor, alias: foo }

    .. code-block:: xml

        <service id="acme_demo.translation.extractor.foo"
            class="Acme\DemoBundle\Translation\FooExtractor">
            <tag name="translation.extractor" alias="foo" />
        </service>

    .. code-block:: php

        $container->register(
            'acme_demo.translation.extractor.foo',
            'Acme\DemoBundle\Translation\FooExtractor'
        )
            ->addTag('translation.extractor', array('alias' => 'foo'));

translation.dumper
------------------

**Purpose**: To register a custom service that dumps messages to a file

.. versionadded:: 2.1
   The ability to add message dumpers is new in Symfony 2.1.

After an `Extractor <translation.extractor>`_ has extracted all messages from
the templates, the dumpers are executed to dump the messages to a translation
file in a specific format.

Symfony2 already comes with many dumpers:

* :class:`Symfony\\Component\\Translation\\Dumper\\CsvFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\IcuResFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\IniFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\MoFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\PoFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\QtFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\XliffFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\YamlFileDumper`

You can create your own dumper by extending
:class:`Symfony\\Component\\Translation\\Dumper\\FileDumper` or implementing
:class:`Symfony\\Component\\Translation\\Dumper\\DumperInterface` and tagging
the service with ``translation.dumper``. The tag has one option: ``alias``
This is the name that's used to determine which dumper should be used.

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo.translation.dumper.json:
                class: Acme\DemoBundle\Translation\JsonFileDumper
                tags:
                    - { name: translation.dumper, alias: json }

    .. code-block:: xml

        <service id="acme_demo.translation.dumper.json"
            class="Acme\DemoBundle\Translation\JsonFileDumper">
            <tag name="translation.dumper" alias="json" />
        </service>

    .. code-block:: php

        $container->register(
            'acme_demo.translation.dumper.json',
            'Acme\DemoBundle\Translation\JsonFileDumper'
        )
            ->addTag('translation.dumper', array('alias' => 'json'));

.. _reference-dic-tags-twig-extension:

twig.extension
--------------

**Purpose**: To register a custom Twig Extension

To enable a Twig extension, add it as a regular service in one of your
configuration, and tag it with ``twig.extension``:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.your_extension_name:
                class: Fully\Qualified\Extension\Class\Name
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.your_extension_name" class="Fully\Qualified\Extension\Class\Name">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.your_extension_name', 'Fully\Qualified\Extension\Class\Name')
            ->addTag('twig.extension')
        ;

For information on how to create the actual Twig Extension class, see
`Twig's documentation`_ on the topic or read the cookbook article:
:doc:`/cookbook/templating/twig_extension`

Before writing your own extensions, have a look at the
`Twig official extension repository`_ which already includes several
useful extensions. For example ``Intl`` and its ``localizeddate`` filter
that formats a date according to user's locale. These official Twig extensions
also have to be added as regular services:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.intl:
                class: Twig_Extensions_Extension_Intl
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.intl" class="Twig_Extensions_Extension_Intl">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.intl', 'Twig_Extensions_Extension_Intl')
            ->addTag('twig.extension')
        ;

twig.loader
-----------

**Purpose**: Register a custom service that loads Twig templates

By default, Symfony uses only one `Twig Loader`_ -
:class:`Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader`. If you need
to load Twig templates from another resource, you can create a service for
the new loader and tag it with ``twig.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme.demo_bundle.loader.some_twig_loader:
                class: Acme\DemoBundle\Loader\SomeTwigLoader
                tags:
                    - { name: twig.loader }

    .. code-block:: xml

        <service id="acme.demo_bundle.loader.some_twig_loader" class="Acme\DemoBundle\Loader\SomeTwigLoader">
            <tag name="twig.loader" />
        </service>

    .. code-block:: php

        $container
            ->register('acme.demo_bundle.loader.some_twig_loader', 'Acme\DemoBundle\Loader\SomeTwigLoader')
            ->addTag('twig.loader')
        ;

validator.constraint_validator
------------------------------

**Purpose**: Create your own custom validation constraint

This tag allows you to create and register your own custom validation constraint.
For more information, read the cookbook article: :doc:`/cookbook/validation/custom_constraint`.

validator.initializer
---------------------

**Purpose**: Register a service that initializes objects before validation

This tag provides a very uncommon piece of functionality that allows you
to perform some sort of action on an object right before it's validated.
For example, it's used by Doctrine to query for all of the lazily-loaded
data on an object before it's validated. Without this, some data on a Doctrine
entity would appear to be "missing" when validated, even though this is not
really the case.

If you do need to use this tag, just make a new class that implements the
:class:`Symfony\\Component\\Validator\\ObjectInitializerInterface` interface.
Then, tag it with the ``validator.initializer`` tag (it has no options).

For an example, see the ``EntityInitializer`` class inside the Doctrine Bridge.

.. _`Twig's documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`Twig official extension repository`: https://github.com/fabpot/Twig-extensions
.. _`KernelEvents`: https://github.com/symfony/symfony/blob/2.2/src/Symfony/Component/HttpKernel/KernelEvents.php
.. _`SwiftMailer's Plugin Documentation`: http://swiftmailer.org/docs/plugins.html
.. _`Twig Loader`: http://twig.sensiolabs.org/doc/api.html#loaders
