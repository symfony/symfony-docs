.. index::
    single: Profiling; Matchers

How to Use Matchers to Enable the Profiler Conditionally
========================================================

.. caution::

    The possibility to use a matcher to enable the profiler conditionally was
    removed in Symfony 4.0.

Symfony Profiler cannot be enabled/disabled conditionally using matchers, because
that feature was removed in Symfony 4.0. However, you can use the ``enable()``
and ``disable()`` methods of the :class:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler`
class in your controllers to manage the profiler programmatically::

    use Symfony\Component\HttpKernel\Profiler\Profiler;
    // ...

    class DefaultController
    {
        // ...

        public function someMethod(Profiler $profiler)
        {
            // for this particular controller action, the profiler is disabled
            $profiler->disable();

            // ...
        }
    }

In order for the profiler to be injected into your controller you need to
create an alias pointing to the existing ``profiler`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Component\HttpKernel\Profiler\Profiler: '@profiler'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpKernel\Profiler\Profiler" alias="profiler" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpKernel\Profiler\Profiler;

        $container->setAlias(Profiler::class, 'profiler');
