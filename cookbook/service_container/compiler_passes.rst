How to work with Compiler Passes in Bundles
===========================================

Compiler passes give you an opportunity to manipulate other service
definitions that have been registered with the service container. You
can read about how to create them in the components section ":doc:`/components/dependency_injection/compilation`".
To register a compiler pass from a bundle you need to add it to the build
method of the bundle definition class::

    namespace Acme\MailerBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    use Acme\MailerBundle\DependencyInjection\Compiler\CustomCompilerPass;

    class AcmeMailerBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            $container->addCompilerPass(new CustomCompilerPass());
        }
    }

One of the common tasks for compiler passes is to work with tagged services,
read more about this in the components section  ":doc:`/components/dependency_injection/tags`".
If you are using custom tags in a bundle then by convention, tag names consist
of the name of the bundle (lowercase, underscores as separators), followed
by a dot, and finally the "real" name, so the tag "transport" in the AcmeMailerBundle
should be: ``acme_mailer.transport``.
