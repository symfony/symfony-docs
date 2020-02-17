.. index::
   single: DependencyInjection; Factories

Using a Factory to Create Services
==================================

Symfony's Service Container provides multiple features to control the creation
of objects, allowing you to specify arguments passed to the constructor as well
as calling methods and setting parameters.

However, sometimes you need to apply the `factory design pattern`_ to delegate
the object creation to some special object called "the factory". In those cases,
the service container can call a method on your factory to create the object
rather than directly instantiating the class.

Static Factories
----------------

Suppose you have a factory that configures and returns a new ``NewsletterManager``
object by calling the static ``createNewsletterManager()`` method::

    // src/Email\NewsletterManagerStaticFactory.php
    namespace App\Email;

    // ...

    class NewsletterManagerStaticFactory
    {
        public static function createNewsletterManager(): NewsletterManager
        {
            $newsletterManager = new NewsletterManager();

            // ...

            return $newsletterManager;
        }
    }

To make the ``NewsletterManager`` object available as a service, use the
``factory`` option to define which method of which class must be called to
create its object:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterManager:
                # the first argument is the class and the second argument is the static method
                factory: ['App\Email\NewsletterManagerStaticFactory', 'createNewsletterManager']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Email\NewsletterManager">
                    <!-- the first argument is the class and the second argument is the static method -->
                    <factory class="App\Email\NewsletterManagerStaticFactory" method="createNewsletterManager"/>

                    <!-- if the factory class is the same as the service class, you can omit
                         the 'class' attribute and define just the 'method' attribute:

                         <factory method="createNewsletterManager"/>
                    -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterManager;
        use App\Email\NewsletterManagerStaticFactory;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(NewsletterManager::class)
                // the first argument is the class and the second argument is the static method
                ->factory([NewsletterManagerStaticFactory::class, 'createNewsletterManager']);
        };


.. note::

    When using a factory to create services, the value chosen for class
    has no effect on the resulting service. The actual class name
    only depends on the object that is returned by the factory. However,
    the configured class name may be used by compiler passes and therefore
    should be set to a sensible value.

Non-Static Factories
--------------------

If your factory is using a regular method instead of a static one to configure
and create the service, instantiate the factory itself as a service too.
Configuration of the service container then looks like this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # first, create a service for the factory
            App\Email\NewsletterManagerFactory: ~

            # second, use the factory service as the first argument of the 'factory'
            # option and the factory method as the second argument
            App\Email\NewsletterManager:
                factory: ['@App\Email\NewsletterManagerFactory', 'createNewsletterManager']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- first, create a service for the factory -->
                <service id="App\Email\NewsletterManagerFactory"/>

                <!-- second, use the factory service as the first argument of the 'factory'
                     option and the factory method as the second argument -->
                <service id="App\Email\NewsletterManager">
                    <factory service="App\Email\NewsletterManagerFactory"
                        method="createNewsletterManager"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterManager;
        use App\Email\NewsletterManagerFactory;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            // first, create a service for the factory
            $services->set(NewsletterManagerFactory::class);

            // second, use the factory service as the first argument of the 'factory'
            // method and the factory method as the second argument
            $services->set(NewsletterManager::class)
                // In versions earlier to Symfony 5.1 the service() function was called ref()
                ->factory([service(NewsletterManagerFactory::class), 'createNewsletterManager']);
        };

.. _factories-invokable:

Invokable Factories
-------------------

Suppose you now change your factory method to ``__invoke()`` so that your
factory service can be used as a callback::

    // src/Email/InvokableNewsletterManagerFactory.php
    namespace App\Email;

    // ...
    class InvokableNewsletterManagerFactory
    {
        public function __invoke(): NewsletterManager
        {
            $newsletterManager = new NewsletterManager();

            // ...

            return $newsletterManager;
        }
    }

Services can be created and configured via invokable factories by omitting the
method name:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterManager:
                class:   App\Email\NewsletterManager
                factory: '@App\Email\NewsletterManagerFactory'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Email\NewsletterManager"
                         class="App\Email\NewsletterManager">
                    <factory service="App\Email\NewsletterManagerFactory"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterManager;
        use App\Email\NewsletterManagerFactory;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(NewsletterManager::class)
                ->factory(service(NewsletterManagerFactory::class));
        };

.. _factories-passing-arguments-factory-method:

Passing Arguments to the Factory Method
---------------------------------------

.. tip::

    Arguments to your factory method are :ref:`autowired <services-autowire>` if
    that's enabled for your service.

If you need to pass arguments to the factory method you can use the ``arguments``
option. For example, suppose the ``createNewsletterManager()`` method in the
previous examples takes the ``templating`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterManager:
                factory:   ['@App\Email\NewsletterManagerFactory', createNewsletterManager]
                arguments: ['@templating']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Email\NewsletterManager">
                    <factory service="App\Email\NewsletterManagerFactory" method="createNewsletterManager"/>
                    <argument type="service" id="templating"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterManager;
        use App\Email\NewsletterManagerFactory;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(NewsletterManager::class)
                ->factory([service(NewsletterManagerFactory::class), 'createNewsletterManager'])
                ->args([service('templating')])
            ;
        };
        
Usage example
--------------------

The following example is intended to show how to create and use a factory method in Symfony framework.
Suppose you want to realize the factory method pattern for services, that describe two delivery methods - DPD and SmartPost.

Services (subclasses) definition::

    // src/Deliveries/DPD.php
    namespace App\Deliveries;

    class DPD
    {
        public $costLabel;
    
        public function cost() {
            return 100;
        }
    }
    
    // src/Deliveries/SmartPost.php
    namespace App\Deliveries;

    class SmartPost
    {
        public $costLabel;
    
        public function cost() {
            return 200;
        }
    }

Factory definition::

    // src/Factories/DeliveryFactory.php
    namespace App\Factories;
    
    abstract class DeliveryFactory
    {
        public static function create($deliveryMethod)
        {
            $delivery = new $deliveryMethod;
            $delivery->costLabel = 'Delivery cost is: ';
    
            return $delivery;
        }
        
        abstract public function price();
    }
    
As you can see, ``DeliveryFactory`` doesn't specify the exact class of the object that will be created.
    
Next, use settings similar to those in the sections above. These settings allow you to define a factory method for subclasses without explicitly extending the abstract class (i.e., without ``class DPD extends DeliveryFactory``)!

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Deliveries\DPD:
                factory:   ['@App\Factories\DeliveryFactory', create]
                arguments:
                    $deliveryMethod: 'App\Deliveries\DPD'
            App\Deliveries\SmartPost:
                factory:   ['@App\Factories\DeliveryFactory', create]
                arguments:
                    $deliveryMethod: 'App\Deliveries\SmartPost'
            

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Deliveries\DPD">
                    <factory service="App\Factories\DeliveryFactory" method="create"/>
                    <argument key="$deliveryMethod">App\Deliveries\DPD</argument>
                </service>
                <service id="App\Deliveries\SmartPost">
                    <factory service="App\Factories\DeliveryFactory" method="create"/>
                    <argument key="$deliveryMethod">App\Deliveries\SmartPost</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Deliveries\DPD;
        use App\Deliveries\SmartPost;
        use App\Factories\DeliveryFactory;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(DPD::class)
                ->factory([ref(DeliveryFactory::class), 'create'])
                ->arg('$deliveryMethod', 'App\Deliveries\DPD')
            ;
            $services->set(SmartPost::class)
                ->factory([ref(DeliveryFactory::class), 'create'])
                ->arg('$deliveryMethod', 'App\Deliveries\SmartPost')
            ;
        };

Now we can use our services as usual (via dependency injection). The only difference is that object instances are created in the factory. Let's get those services in controller::

    /**
     * @Route("/get-deliveries-cost", methods={"GET"})
     */
    public function getDeliveriesCost(DPD $dpd, SmartPost $smartPost)
    {
        // ...
        
        // $dpd->costLabel is fulfilled in factory method.
        $dpdCost = $dpd->costLabel . $dpd->cost();
        $smartPostCost = $smartPost->costLabel . $smartPost->cost();
        
        // ...
    }

.. _`factory design pattern`: https://en.wikipedia.org/wiki/Factory_(object-oriented_programming)
