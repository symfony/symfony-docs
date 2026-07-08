The VarExporter Component
=========================

    The VarExporter component exports any serializable PHP data structure to
    plain PHP code and allows you to instantiate and populate objects without
    calling their constructors.

Installation
------------

.. code-block:: terminal

    $ composer require --dev symfony/var-exporter

.. include:: /components/require_autoload.rst.inc

Exporting/Serializing Variables
-------------------------------

The main feature of this component is to serialize PHP data structures to plain
PHP code, similar to PHP's :phpfunction:`var_export` function::

    use Symfony\Component\VarExporter\VarExporter;

    $exported = VarExporter::export($someVariable);
    // store the $exported data in some file or cache system for later reuse
    $data = file_put_contents('exported.php', '<?php return '.$exported.';');

    // later, regenerate the original variable when you need it
    $regeneratedVariable = require 'exported.php';

The reason to use this component instead of ``serialize()`` or ``igbinary`` is
performance: thanks to `OPcache`_, the resulting code is significantly faster
and more memory efficient than using ``unserialize()`` or ``igbinary_unserialize()``.

In addition, there are some minor differences:

* If the original variable defines them, all the semantics associated with
  ``serialize()`` (such as ``__wakeup()``, ``__sleep()``, and ``Serializable``)
  are preserved (``var_export()`` ignores them);
* References involving ``SplObjectStorage``, ``ArrayObject`` or ``ArrayIterator``
  instances are preserved;
* Missing classes throw a ``ClassNotFoundException`` instead of being
  unserialized to ``PHP_Incomplete_Class`` objects;
* ``Reflection*``, ``IteratorIterator`` and ``RecursiveIteratorIterator``
  classes throw an exception when being serialized.

The exported data is a `PSR-2`_ compatible PHP file. Consider for example the
following class hierarchy::

    abstract class AbstractClass
    {
        protected int $foo;
        private int $bar;

        protected function setBar($bar): void
        {
            $this->bar = $bar;
        }
    }

    class ConcreteClass extends AbstractClass
    {
        public function __construct()
        {
            $this->foo = 123;
            $this->setBar(234);
        }
    }

When exporting the ``ConcreteClass`` data with VarExporter, the generated PHP
file looks like this::

    return \Symfony\Component\VarExporter\Internal\Hydrator::hydrate(
        $o = [
            clone (\Symfony\Component\VarExporter\Internal\Registry::$prototypes['Symfony\\Component\\VarExporter\\Tests\\ConcreteClass'] ?? \Symfony\Component\VarExporter\Internal\Registry::p('Symfony\\Component\\VarExporter\\Tests\\ConcreteClass')),
        ],
        null,
        [
            'Symfony\\Component\\VarExporter\\Tests\\AbstractClass' => [
                'foo' => [
                    123,
                ],
                'bar' => [
                    234,
                ],
            ],
        ],
        $o[0],
        []
    );

.. _instantiating-php-classes:

Instantiating & Hydrating PHP Classes
-------------------------------------

.. deprecated:: 8.1

    The :class:`Symfony\\Component\\VarExporter\\Instantiator` and
    :class:`Symfony\\Component\\VarExporter\\Hydrator` classes are deprecated
    since Symfony 8.1, use the ``deepclone_hydrate()`` function instead (see
    :ref:`the dedicated section <deepclone_hydrate>` below).

Instantiator
~~~~~~~~~~~~

This component provides an instantiator, which can create objects and set
their properties without calling their constructors or any other methods::

    use Symfony\Component\VarExporter\Instantiator;

    // creates an empty instance of Foo
    $fooObject = Instantiator::instantiate(Foo::class);

    // creates a Foo instance and sets one of its properties
    $fooObject = Instantiator::instantiate(Foo::class, ['propertyName' => $propertyValue]);

The instantiator can also populate the property of a parent class. Assuming ``Bar``
is the parent class of ``Foo`` and defines a ``privateBarProperty`` attribute::

    use Symfony\Component\VarExporter\Instantiator;

    // creates a Foo instance and sets a private property defined on its parent Bar class
    $fooObject = Instantiator::instantiate(Foo::class, [], [
        Bar::class => ['privateBarProperty' => $propertyValue],
    ]);

Instances of ``ArrayObject``, ``ArrayIterator`` and ``SplObjectHash`` can be
created by using the special ``"\0"`` property name to define their internal value::

    use Symfony\Component\VarExporter\Instantiator;

    // creates an SplObjectStorage where $info1 is associated with $object1, etc.
    $theObject = Instantiator::instantiate(SplObjectStorage::class, [
        "\0" => [$object1, $info1, $object2, $info2...],
    ]);

    // creates an ArrayObject populated with $inputArray
    $theObject = Instantiator::instantiate(ArrayObject::class, [
        "\0" => [$inputArray],
    ]);

Hydrator
~~~~~~~~

Instead of populating objects that don't exist yet (using the instantiator),
sometimes you want to populate properties of an already existing object. This is
the goal of the :class:`Symfony\\Component\\VarExporter\\Hydrator`. Here is a
basic usage of the hydrator populating a property of an object::

    use Symfony\Component\VarExporter\Hydrator;

    $object = new Foo();
    Hydrator::hydrate($object, ['propertyName' => $propertyValue]);

The hydrator can also populate the property of a parent class. Assuming ``Bar``
is the parent class of ``Foo`` and defines a ``privateBarProperty`` attribute::

    use Symfony\Component\VarExporter\Hydrator;

    $object = new Foo();
    Hydrator::hydrate($object, [], [
        Bar::class => ['privateBarProperty' => $propertyValue],
    ]);

    // alternatively, you can use the special "\0" syntax
    Hydrator::hydrate($object, ["\0Bar\0privateBarProperty" => $propertyValue]);

Instances of ``ArrayObject``, ``ArrayIterator`` and ``SplObjectHash`` can be
populated by using the special ``"\0"`` property name to define their internal value::

    use Symfony\Component\VarExporter\Hydrator;

    // creates an SplObjectHash where $info1 is associated with $object1, etc.
    $storage = new SplObjectStorage();
    Hydrator::hydrate($storage, [
        "\0" => [$object1, $info1, $object2, $info2...],
    ]);

    // creates an ArrayObject populated with $inputArray
    $arrayObject = new ArrayObject();
    Hydrator::hydrate($arrayObject, [
        "\0" => [$inputArray],
    ]);

.. _deepclone_hydrate:

deepclone_hydrate
~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``deepclone_hydrate()`` function was introduced in Symfony 8.1.

The ``deepclone_hydrate()`` function replaces both ``Instantiator`` and
``Hydrator``. It is provided by `symfony/polyfill-deepclone`_ or by the
`ext-deepclone`_ PHP extension. Pass a class name to create a new instance,
or an existing object to populate its properties::

    // creates an empty instance of Foo
    $fooObject = deepclone_hydrate(Foo::class);

    // creates a Foo instance and sets its properties
    $fooObject = deepclone_hydrate(Foo::class, ['propertyName' => $propertyValue]);

    // populates an existing instance
    $object = new Foo();
    deepclone_hydrate($object, ['propertyName' => $propertyValue]);

Properties declared on a parent class are set with the special
``"\0ParentClass\0propertyName"`` syntax::

    deepclone_hydrate($object, ["\0Bar\0privateBarProperty" => $propertyValue]);

Deep Cloning
------------

.. versionadded:: 8.1

    The ``DeepCloner`` class was introduced in Symfony 8.1.

In PHP, the native ``clone`` keyword does a **shallow copy**: it copies the object
but leaves nested objects as references to the originals. **Deep cloning** means
recursively cloning all nested objects so the result shares no references with
the original.

The :class:`Symfony\\Component\\VarExporter\\DeepCloner` class deep-clones PHP
values while preserving PHP's copy-on-write semantics for strings and arrays.
Unlike ``unserialize(serialize())``, it does not reallocate strings and
scalar-only arrays, resulting in lower memory usage and better performance.

For one-off cloning, use the static ``deepClone()`` method::

    use Symfony\Component\VarExporter\DeepCloner;

    $clone = DeepCloner::deepClone($originalObject);

When you need to clone the same structure multiple times, create an instance
to amortize the cost of object graph analysis::

    use Symfony\Component\VarExporter\DeepCloner;

    $cloner = new DeepCloner($prototype);

    $clone1 = $cloner->clone();
    $clone2 = $cloner->clone();

The ``isStaticValue()`` method returns ``true`` when the value does not need
cloning (scalars, ``null``, enums, scalar-only arrays)::

    use Symfony\Component\VarExporter\DeepCloner;

    $cloner = new DeepCloner($value);

    if ($cloner->isStaticValue()) {
        // $value contains no objects or references, cloning is a no-op
    }

Use the ``cloneAs()`` method to deep-clone the root object using a different
class (the target class must be compatible with the original, typically in the
same hierarchy)::

    $cloner = new DeepCloner($originalDog);
    $puppy = $cloner->cloneAs(Puppy::class);

``DeepCloner`` instances are serializable. The serialized form deduplicates
class and property names, typically producing a smaller payload than
``serialize($value)`` itself.

The ``toArray()`` method exports the cloner state as a pure array (only scalars
and nested arrays, no objects), making it suitable for any encoder that handles
plain PHP arrays (``json_encode()``, ``var_export()``, etc.). Use ``fromArray()``
to restore the cloner from a previously exported array::

    use Symfony\Component\VarExporter\DeepCloner;

    $cloner = new DeepCloner($originalObject);

    // export to a pure array
    $data = $cloner->toArray();

    // store as JSON, in a cache, or as a PHP array file
    file_put_contents('cloner.json', json_encode($data));

    // later, restore the cloner and produce clones without re-analyzing the object graph
    $data = json_decode(file_get_contents('cloner.json'), true);
    $cloner = DeepCloner::fromArray($data);
    $clone = $cloner->clone();

.. versionadded:: 8.1

    The ``toArray()`` and ``fromArray()`` methods were introduced in Symfony 8.1.

Creating Lazy Objects
---------------------

Lazy objects are objects instantiated empty and populated on demand. This is
particularly useful when, for example, a class has properties that require
heavy computation to determine their values. In such cases, you may want to
trigger the computation only when the property is actually accessed. This way,
the expensive processing is avoided entirely if the property is never used.

Since version 8.4, PHP provides support for lazy objects via the reflection API.
This native API works with concrete classes, but not with abstract or internal ones.
This component provides helpers to generate lazy objects using the decorator
pattern, which also works with abstract classes, internal classes, and interfaces::

    $proxyCode = ProxyHelper::generateLazyProxy(new \ReflectionClass(SomeInterface::class));
    // $proxyCode should be dumped into a file in production environments
    eval('class ProxyDecorator'.$proxyCode);

    $proxy = ProxyDecorator::createLazyProxy(initializer: function (): SomeInterface {
        // use whatever heavy logic you need here
        // to compute the $dependencies of the proxied class
        $instance = new SomeHeavyClass(...$dependencies);
        // call setters, etc. if needed

        return $instance;
    });

Use this mechanism only when native lazy objects cannot be leveraged
(otherwise you'll get a deprecation notice).

.. _`OPcache`: https://www.php.net/opcache
.. _`PSR-2`: https://www.php-fig.org/psr/psr-2/
.. _`symfony/polyfill-deepclone`: https://github.com/symfony/polyfill/tree/main/src/DeepClone
.. _`ext-deepclone`: https://github.com/symfony/php-ext-deepclone
