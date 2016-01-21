.. index::
    single: DependencyInjection; Synthetic Services

How to Inject Instances into the Container
------------------------------------------

When using the container in your application, you sometimes need to inject
an instance instead of configuring the container to create a new instance.

For instance, if you're using the :doc:`HttpKernel </components/http_kernel/introduction>`
component with the DependencyInjection component, then the ``kernel``
service is injected into the container from within the ``Kernel`` class::

    // ...
    abstract class Kernel implements KernelInterface, TerminableInterface
    {
        // ...
        protected function initializeContainer()
        {
            // ...
            $this->container->set('kernel', $this);

            // ...
        }
    }

The ``kernel`` service is called a synthetic service. This service has to
be configured in the container, so the container knows the service does
exist during compilation (otherwise, services depending on this ``kernel``
service will get a "service does not exist" error).

In order to do so, you have to use
:method:`Definition::setSynthetic() <Symfony\\Component\\DependencyInjection\\Definition::setSynthetic>`::

    use Symfony\Component\DependencyInjection\Definition;

    // synthetic services don't specify a class
    $kernelDefinition = new Definition();
    $kernelDefinition->setSynthetic(true);

    $container->setDefinition('your_service', $kernelDefinition);

Now, you can inject the instance in the container using
:method:`Container::set() <Symfony\\Component\\DependencyInjection\\Container::set>`::

    $yourService = new YourObject();
    $container->set('your_service', $yourService);

``$container->get('your_service')`` will now return the same instance as
``$yourService``.
