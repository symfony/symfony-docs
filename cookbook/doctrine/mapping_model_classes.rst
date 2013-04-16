.. index::
   single: Doctrine; Mapping Model classes

How to provide model classes for several Doctrine implementations
=================================================================

When building a bundle that could be used not only with Doctrine ORM but
also the CouchDB ODM, MongoDB ODM or PHPCR ODM, you should still only
write one model class. The Doctrine bundles provide a compiler pass to
register the mappings for your model classes.

.. note::

    For non-reusable bundles, the easiest is to put your model classes in
    the default locations. ``Entity`` for Doctrine ORM, ``Document`` for one
    of the ODMs. For reusable bundles, rather than duplicate model classes
    just to get the auto mapping, use the compiler pass.

.. versionadded:: 2.3
    The mapping compiler pass was added in  Symfony 2.3 and DoctrineBundle 1.2.1.


In your bundle class, write the following code to register the compiler pass::

    use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

    class FOSUserBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);
            $container->addCompilerPass(new ValidationPass());

            $compilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection'
                . '\Compiler\DoctrineOrmMappingsPass';
            if (class_exists($compilerClass)) {
                $modelDir = realpath(__DIR__.'/Resources/config/doctrine/model');
                $mappings = array(
                    $modelDir => 'FOS\UserBundle\Model',
                );
                $container->addCompilerPass(
                    DoctrineOrmMappingsPass::createXmlMappingDriver(
                        $mappings, 'fos_user.backend_type_orm'
                ));
            }

            // TODO: couch, mongo
        }
    }

The compiler pass provides factory methods for all drivers: Annotations, XML, Yaml, PHP and StaticPHP.
