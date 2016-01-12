.. index::
   single: Doctrine; Mapping Model classes

How to Provide Model Classes for several Doctrine Implementations
=================================================================

When building a bundle that could be used not only with Doctrine ORM but
also the CouchDB ODM, MongoDB ODM or PHPCR ODM, you should still only
write one model class. The Doctrine bundles provide a compiler pass to
register the mappings for your model classes.

.. note::

    For non-reusable bundles, the easiest option is to put your model classes
    in the default locations: ``Entity`` for the Doctrine ORM or ``Document``
    for one of the ODMs. For reusable bundles, rather than duplicate model classes
    just to get the auto-mapping, use the compiler pass.

.. versionadded:: 2.3
    The base mapping compiler pass was introduced in Symfony 2.3. The Doctrine bundles
    support it from DoctrineBundle >= 1.3.0, MongoDBBundle >= 3.0.0,
    PHPCRBundle >= 1.0.0 and the (unversioned) CouchDBBundle supports the
    compiler pass since the `CouchDB Mapping Compiler Pass pull request`_
    was merged.

In your bundle class, write the following code to register the compiler pass.
This one is written for the CmfRoutingBundle, so parts of it will need to
be adapted for your case::

    use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
    use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
    use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
    use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;

    class CmfRoutingBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);
            // ...

            $modelDir = realpath(__DIR__.'/Resources/config/doctrine/model');
            $mappings = array(
                $modelDir => 'Symfony\Cmf\RoutingBundle\Model',
            );

            $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
            if (class_exists($ormCompilerClass)) {
                $container->addCompilerPass(
                    DoctrineOrmMappingsPass::createXmlMappingDriver(
                        $mappings,
                        array('cmf_routing.model_manager_name'),
                        'cmf_routing.backend_type_orm',
                        array('CmfRoutingBundle' => 'Symfony\Cmf\RoutingBundle\Model')
                ));
            }

            $mongoCompilerClass = 'Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass';
            if (class_exists($mongoCompilerClass)) {
                $container->addCompilerPass(
                    DoctrineMongoDBMappingsPass::createXmlMappingDriver(
                        $mappings,
                        array('cmf_routing.model_manager_name'),
                        'cmf_routing.backend_type_mongodb',
                        array('CmfRoutingBundle' => 'Symfony\Cmf\RoutingBundle\Model')
                ));
            }

            $couchCompilerClass = 'Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass';
            if (class_exists($couchCompilerClass)) {
                $container->addCompilerPass(
                    DoctrineCouchDBMappingsPass::createXmlMappingDriver(
                        $mappings,
                        array('cmf_routing.model_manager_name'),
                        'cmf_routing.backend_type_couchdb',
                        array('CmfRoutingBundle' => 'Symfony\Cmf\RoutingBundle\Model')
                ));
            }

            $phpcrCompilerClass = 'Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass';
            if (class_exists($phpcrCompilerClass)) {
                $container->addCompilerPass(
                    DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                        $mappings,
                        array('cmf_routing.model_manager_name'),
                        'cmf_routing.backend_type_phpcr',
                        array('CmfRoutingBundle' => 'Symfony\Cmf\RoutingBundle\Model')
                ));
            }
        }
    }

Note the :phpfunction:`class_exists` check. This is crucial, as you do not want your
bundle to have a hard dependency on all Doctrine bundles but let the user
decide which to use.

The compiler pass provides factory methods for all drivers provided by Doctrine:
Annotations, XML, Yaml, PHP and StaticPHP. The arguments are:

* A map/hash of absolute directory path to namespace;
* An array of container parameters that your bundle uses to specify the name of
  the Doctrine manager that it is using. In the example above, the CmfRoutingBundle
  stores the manager name that's being used under the ``cmf_routing.model_manager_name``
  parameter. The compiler pass will append the parameter Doctrine is using
  to specify the name of the default manager. The first parameter found is
  used and the mappings are registered with that manager;
* An optional container parameter name that will be used by the compiler
  pass to determine if this Doctrine type is used at all. This is relevant if
  your user has more than one type of Doctrine bundle installed, but your
  bundle is only used with one type of Doctrine;
* A map/hash of aliases to namespace. This should be the same convention used
  by Doctrine auto-mapping. In the example above, this allows the user to call
  ``$om->getRepository('CmfRoutingBundle:Route')``.

.. note::

    The factory method is using the ``SymfonyFileLocator`` of Doctrine, meaning
    it will only see XML and YML mapping files if they do not contain the
    full namespace as the filename. This is by design: the ``SymfonyFileLocator``
    simplifies things by assuming the files are just the "short" version
    of the class as their filename (e.g. ``BlogPost.orm.xml``)

    If you also need to map a base class, you can register a compiler pass
    with the ``DefaultFileLocator`` like this. This code is taken from the
    ``DoctrineOrmMappingsPass`` and adapted to use the ``DefaultFileLocator``
    instead of the ``SymfonyFileLocator``::

        private function buildMappingCompilerPass()
        {
            $arguments = array(array(realpath(__DIR__ . '/Resources/config/doctrine-base')), '.orm.xml');
            $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator', $arguments);
            $driver = new Definition('Doctrine\ORM\Mapping\Driver\XmlDriver', array($locator));

            return new DoctrineOrmMappingsPass(
                $driver,
                array('Full\Namespace'),
                array('your_bundle.manager_name'),
                'your_bundle.orm_enabled'
            );
        }

    Note that you do not need to provide a namespace alias unless your users are
    expected to ask Doctrine for the base classes.

    Now place your mapping file into ``/Resources/config/doctrine-base`` with the
    fully qualified class name, separated by ``.`` instead of ``\``, for example
    ``Other.Namespace.Model.Name.orm.xml``. You may not mix the two as otherwise
    the ``SymfonyFileLocator`` will get confused.

    Adjust accordingly for the other Doctrine implementations.

.. _`CouchDB Mapping Compiler Pass pull request`: https://github.com/doctrine/DoctrineCouchDBBundle/pull/27
