.. index::
   single: DependencyInjection; Compiler passes
   single: Service Container; Compiler passes

How to Work with Compiler Passes
================================

Compiler passes give you an opportunity to manipulate other
:doc:`service definitions </service_container/definitions>` that have been
registered with the service container. You can read about how to create them in
the components section ":ref:`components-di-separate-compiler-passes`".

Compiler passes are registered in the ``build()`` method of the application kernel::

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

One of the most common use-cases of compiler passes is to work with :doc:`tagged
services </service_container/tags>`. In those cases, instead of creating a
compiler pass, you can make the kernel implement
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
and process the services inside the ``process()`` method::

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

        public function process(ContainerBuilder $container)
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

Working with Compiler Passes in Bundles
---------------------------------------

:doc:`Bundles </bundles>` can define compiler passes in the ``build()`` method of
the main bundle class (this is not needed when implementing the ``process()``
method in the extension)::

    // src/MyBundle/MyBundle.php
    namespace App\MyBundle;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class MyBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            $container->addCompilerPass(new CustomPass());
        }
    }

If you are using custom :doc:`service tags </service_container/tags>` in a
bundle then by convention, tag names consist of the name of the bundle
(lowercase, underscores as separators), followed by a dot, and finally the
"real" name. For example, if you want to introduce some sort of "transport" tag
in your AcmeMailerBundle, you should call it ``acme_mailer.transport``.
