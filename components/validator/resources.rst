.. index::
    single: Validator; Loading Resources

Loading Resources
=================

The Validator component uses metadata to validate a value. This metadata defines
how a class, array or any other value should be validated. When validating a
class, the metadata is defined by the class itself. When validating simple values,
the metadata must be passed to the validation methods.

Class metadata can be defined in a configuration file or in the class itself.
The Validator component collects that metadata using a set of loaders.

.. seealso::

    You'll learn how to define the metadata in :doc:`metadata`.

The StaticMethodLoader
----------------------

The most basic loader is the
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\StaticMethodLoader`.
This loader gets the metadata by calling a static method of the class. The name
of the method is configured using the
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addMethodMapping`
method of the validator builder::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->addMethodMapping('loadValidatorMetadata')
        ->getValidator();

In this example, the validation metadata is retrieved executing the
``loadValidatorMetadata()`` method of the class::

    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    class User
    {
        protected $name;

        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addPropertyConstraint('name', new Assert\NotBlank());
            $metadata->addPropertyConstraint('name', new Asert\Length(array(
                'min' => 5,
                'max' => 20,
            )));
        }
    }

.. tip::

    Instead of calling ``addMethodMapping()`` multiple times to add several
    method names, you can also use
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addMethodMappings`
    to set an array of supported method names.

The File Loaders
----------------

The component also provides two file loaders, one to load YAML files and one to
load XML files. Use
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addYamlMapping` or
:method:`Symfony\\Component\\Validator\\ValidatorBuilder::addXmlMapping` to
configure the locations of these files::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        ->addYamlMapping('config/validation.yml')
        ->getValidator();

.. note::

    If you want to load YAML mapping files then you will also need to install
    :doc:`the Yaml component </components/yaml>`.

.. tip::

    Just like with the method mappings, you can also use
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addYamlMappings` and
    :method:`Symfony\\Component\\Validator\\ValidatorBuilder::addXmlMappings`
    to configure an array of file paths.

The AnnotationLoader
--------------------

At last, the component provides an
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\AnnotationLoader` to get
the metadata from the annotations of the class. Annotations are defined as ``@``
prefixed classes included in doc block comments (``/** ... */``). For example::

    use Symfony\Component\Validator\Constraints as Assert;
    // ...

    class User
    {
        /**
        * @Assert\NotBlank()
        */
        protected $name;
    }

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
    ``doctrine/annotations`` and ``doctrine/cache`` packages from `Packagist`_.

Using Multiple Loaders
----------------------

The component provides a
:class:`Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain` class to
execute several loaders sequentially in the same order they were defined:

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

Using many loaders to load metadata from different places is convenient, but it
can slow down your application because each file needs to be parsed, validated
and converted into a :class:`Symfony\\Component\\Validator\\Mapping\\ClassMetadata`
instance.

To solve this problem, call the :method:`Symfony\\Component\\Validator\\ValidatorBuilder::setMetadataCache`
method of the Validator builder and pass your own caching class (which must
implement :class:`Symfony\\Component\\Validator\\Mapping\\Cache\\CacheInterface`)::

    use Symfony\Component\Validator\Validation;

    $validator = Validation::createValidatorBuilder()
        // ... add loaders
        ->setMetadataCache(new SomeImplementCacheInterface());
        ->getValidator();

.. note::

    The loaders already use a singleton load mechanism. That means that the
    loaders will only load and parse a file once and put that in a property,
    which will then be used the next time it is asked for metadata. However,
    the Validator still needs to merge all metadata of one class from every
    loader when it is requested.

Using a Custom MetadataFactory
------------------------------

All the loaders and the cache are passed to an instance of
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
    and caches using the ``add*Mapping()`` methods anymore. You now have to
    inject them into your custom metadata factory yourself.

.. _`Packagist`: https://packagist.org
