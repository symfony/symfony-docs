How to Work with Compiler Passes
================================

Compiler passes give you an opportunity to manipulate other
:doc:`service definitions </dmanifestor/symfony-docs>` that have been
registered with the service container.

.. _kernel-as-compiler-pass:

If your compiler pass is relatively small, you can define it inside the
application's ``Kernel`` class instead of creating a
:ref:`separate compiler pass class <components-di-separate-compiler-passes>`.

To do so, make your kernel implement :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
and add the compiler pass code inside the ``process()`` method::

    // src/Kernel.php
    namespace App;

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel implements CompilerPassInterface
    {
        use MicroKernelTrait;

        // ...

        public function process(ContainerBuilder $container): void
        {
            // in this method you can manipulate the service container:
            // for example, changing some container service:
            $container->getDefinition('app.some_private_service')->setPublic(true);

            // or processing tagged services:
            foreach ($container->findTaggedServiceIds('some_tag') as $id => $tags) {
                // ...
            }
        }
    }

If you create separate compiler pass classes, enable them in the ``build()``
method of the application kernel::

    // src/Kernel.php
    namespace App;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        // ...

        protected function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass(new CustomPass());
        }
    }

Working with Compiler Passes in Bundles
---------------------------------------

If your compiler pass is relatively small, you can add it directly in the main
bundle class. To do so, make your bundle implement the
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
and place the compiler pass code inside the ``process()`` method of the main
bundle class::

    // src/MyBundle/MyBundle.php
    namespace App\MyBundle;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class MyBundle extends AbstractBundle implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container): void
        {
            // in this method you can manipulate the service container:
            // for example, changing some container service:
            $container->getDefinition('app.some_private_service')->setPublic(true);

            // or processing tagged services:
            foreach ($container->findTaggedServiceIds('some_tag') as $id => $tags) {
                // ...
            }
        }
    }

Alternatively, when using :ref:`separate compiler pass classes <components-di-separate-compiler-passes>`,
bundles can enable them in the ``build()`` method of their main bundle class::

    // src/MyBundle/MyBundle.php
    namespace App\MyBundle;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class MyBundle extends AbstractBundle
    {
        public function build(ContainerBuilder $container): void
        {
            parent::build($container);

            $container->addCompilerPass(new CustomPass());
        }
    }

If you are using custom :doc:`service tags </service_container/tags>` in a
bundle, the convention is to format tag names by starting with the bundle's name
in lowercase (using underscores as separators), followed by a dot, and finally
the specific tag name. For example, to introduce a "transport" tag in your
AcmeMailerBundle, you would name it ``acme_mailer.transport``.
