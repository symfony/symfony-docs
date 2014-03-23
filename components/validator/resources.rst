.. index::
    single: Validator; Loading Resources

Loading Resources
=================

The Validator uses metadata to validate a value. This metadata defines how a
class, array or any other value should be validated. When validating a class,
each class contains its own specific metadata. When validating another value,
the metadata to passed to the validate methods.

Class metadata should be defined somewhere in a configuration file, or in the
class itself. The ``Validator`` needs to be able to retrieve this metadata
from the file or class. To do that, it uses a set of loaders.

.. seealso::

    You'll learn how to define the metadata in :doc:`metadata`.

The StaticMethodLoader
----------------------

The easiest loader is the
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\StaticMethodLoader`.
This loader will call a static method of the class in order to get the
metadata for that class. The name of the method is configured using the
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addMethodMapping`
method of the Validator builder::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->addMethodMapping('loadValidatorMetadata')
        ->getValidator();

Now, the retrieved ``Validator`` tries to find the ``loadValidatorMetadata()``
method of the validated class to load its metadata.

.. tip::

    You can call this method multiple times to add multiple supported method
    names. You can also use
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addMethodMappings`
    to set an array of supported method names.

The FileLoaders
---------------

The component also provides 2 file loaders, one to load Yaml files and one to
load XML files. Use 
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addYamlMapping` or
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addXmlMapping` to
configure the locations of these files::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->addYamlMapping('config/validation.yml')
        ->getValidator();

.. tip::

    Just like with the method mappings, you can also use 
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addYamlMappings` and
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addXmlMappings`
    to configure an array of file paths.

The AnnotationLoader
--------------------

At last, the component provides an
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\AnnotationLoader`.
This loader will parse the annotations of a class. Annotations are placed in
PHPdoc comments (`/** ... */`) and start with an ``@``. For instance::

    // ...

    /**
     * @Assert\NotBlank()
     */
    protected $name;

To enable the annotation loader, call the 
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::enableAnnotationMapping`
method. It takes an optional annotation reader instance, which defaults to
``Doctrine\Common\Annotations\AnnotationReader``::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->getValidator();

To disable the annotation loader after it was enabled, call
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::disableAnnotationMapping`.

.. note::

    In order to use the annotation loader, you should have installed the
    ``doctrine/annotations`` and ``doctrine/cache`` packages of Packagist.

Using Multiple Loaders
----------------------

The component provides a 
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain` class to
chain multiple loaders. This means you can configure as many loaders as you
want at the same time.

The ``ValidatorBuilder`` will already take care of this when you configure
multiple mappings::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->addMethodMapping('loadValidatorMetadata')
        ->addXmlMapping('config/validation.xml')
        ->getValidator();

Caching
-------

Using many loaders to load metadata from different places is very easy for the
developer, but it can easily slow down your application since each file needs
to be parsed, validated and converted to a
:class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadata` instance. To
solve this problems, you can configure a cacher which will be used to cache
the ``ClassMetadata`` after it was loaded.

The Validator component comes with a
:class:`Symfony\\Component\\Validator\\Mapping\\Cache\\ApcCache`
implementation. You can easily create other cachers by creating a class which
implements :class:`Symfony\\Component\\Validator\\Mapping\\Cache\\CacheInterface`.

.. note::

    The loader already use a singleton load mechanism. That means that they
    will only load and parse a file once and put that in a property, which
    will be used on the next time. However, the Validator still needs to
    merge all metadata of one class from every loader when it is requested.

To set a cacher, call the
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::setMetadataCache` of
the Validator builder::

    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Mapping\Cache\ApcCache;

    $validator = Validation::createValidatorBuilder()
        // ... add loaders
        ->setMetadataCache(new ApcCache('some_apc_prefix'));
        ->getValidator();

Using a Custom MetadataFactory
------------------------------

All loaders and the cacher are passed to an instance of
:class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory`. This
class is responsible for creating a ``ClassMetadata`` instance from all the
configured resources.

You can also use a custom metadata factory implementation by creating a class
which implements
:class:`Symfony\\Component\\Validator\\MetadataFactoryInterface`. You can set
this custom implementation using 
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::setMetadataFactory`::

    use Acme\Validation\CustomMetadataFactory;
    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->setMetadataFactory(new CustomMetadataFactory(...));
        ->getValidator();

.. caution::

    Since you are using a custom metadata factory, you can't configure loaders
    and cachers using the helper methods anymore. You now have to inject them
    into your custom metadata factory yourself.
