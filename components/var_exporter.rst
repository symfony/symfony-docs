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
is the parent class of ``Foo`` and defines a ``privateBarProperty`` property::

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
is the parent class of ``Foo`` and defines a ``privateBarProperty`` property::

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

Legacy Creation of Lazy Objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using a PHP version earlier than 8.4, native lazy objects are not available.
In these cases, the VarExporter component provides two traits that help you
implement lazy-loading mechanisms in your classes.

.. _var-exporter_ghost-objects:

LazyGhostTrait
..............

.. deprecated:: 7.3

    ``LazyGhostTrait`` is deprecated since Symfony 7.3. Use PHP 8.4's native lazy
    objects instead. Note that using the trait with PHP versions earlier than 8.4
    does not trigger a deprecation, to ease the transition.

Ghost objects are empty objects whose properties are populated the first
time any method is called. Thanks to :class:`Symfony\\Component\\VarExporter\\LazyGhostTrait`,
implementing the lazy mechanism is easier. The ``MyLazyObject::populateHash()``
method will be called only when the object is actually used and needs to be
initialized::

    namespace App\Hash;

    use Symfony\Component\VarExporter\LazyGhostTrait;

    class HashProcessor
    {
        use LazyGhostTrait;

        // This property may require a heavy computation to have its value
        public readonly string $hash;

        public function __construct()
        {
            self::createLazyGhost(initializer: $this->populateHash(...), instance: $this);
        }

        private function populateHash(array $data): void
        {
            // Compute $this->hash value with the passed data
        }
    }

:class:`Symfony\\Component\\VarExporter\\LazyGhostTrait` also allows you to
convert non-lazy classes to lazy ones::

    namespace App\Hash;

    use Symfony\Component\VarExporter\LazyGhostTrait;

    class HashProcessor
    {
        public readonly string $hash;

        public function __construct(array $data)
        {
            $this->populateHash($data);
        }

        private function populateHash(array $data): void
        {
            // ...
        }

        public function validateHash(): bool
        {
            // ...
        }
    }

    class LazyHashProcessor extends HashProcessor
    {
        use LazyGhostTrait;
    }

    $processor = LazyHashProcessor::createLazyGhost(initializer: function (HashProcessor $instance): void {
        // Do any operation you need here: call setters, getters, methods to validate the hash, etc.
        $data = /** Retrieve required data to compute the hash */;
        $instance->__construct(...$data);
        $instance->validateHash();
    });

As long as you never query the ``$processor->hash`` value, heavy methods will never be
triggered. But still, the ``$processor`` object exists and can be used in your
code, passed to methods, functions, etc.

Ghost objects unfortunately can't work with abstract classes or internal PHP
classes. Nevertheless, the VarExporter component covers this need with the help
of :ref:`Virtual Proxies <var-exporter_virtual-proxies>`.

.. _var-exporter_virtual-proxies:

LazyProxyTrait
..............

.. deprecated:: 7.3

    ``LazyProxyTrait`` is deprecated since Symfony 7.3. Use PHP 8.4's native lazy
    objects instead. Note that using the trait with PHP versions earlier than 8.4
    does not trigger a deprecation, to ease the transition.

The purpose of virtual proxies is the same as
:ref:`ghost objects <var-exporter_ghost-objects>`, but their internal behavior is
completely different. Where ghost objects require extending a base class, virtual
proxies take advantage of the **Liskov Substitution principle**. This principle
states that if two objects implement the same interface, you can swap
between the different implementations without breaking your application. This is
what virtual proxies take advantage of. To use virtual proxies, you may use
:class:`Symfony\\Component\\VarExporter\\ProxyHelper` to generate the proxy class
code::

    namespace App\Hash;

    use Symfony\Component\VarExporter\ProxyHelper;

    interface ProcessorInterface
    {
        public function getHash(): bool;
    }

    abstract class AbstractProcessor implements ProcessorInterface
    {
        protected string $hash;

        public function getHash(): bool
        {
            return $this->hash;
        }
    }

    class HashProcessor extends AbstractProcessor
    {
        public function __construct(array $data)
        {
            $this->populateHash($data);
        }

        private function populateHash(array $data): void
        {
            // ...
        }
    }

    $proxyCode = ProxyHelper::generateLazyProxy(new \ReflectionClass(AbstractProcessor::class));
    // $proxyCode contains the actual proxy and the reference to LazyProxyTrait.
    // In production env, this should be dumped into a file to avoid calling eval().
    eval('class HashProcessorProxy'.$proxyCode);

    $processor = HashProcessorProxy::createLazyProxy(initializer: function (): ProcessorInterface {
        $data = /** Retrieve required data to compute the hash */;
        $instance = new HashProcessor(...$data);

        // Do any operation you need here: call setters, getters, methods to validate the hash, etc.

        return $instance;
    });

Just like ghost objects, as long as you never query ``$processor->hash``, its value
will not be computed. The main difference with ghost objects is that this time,
a proxy of an abstract class was created. This also works with internal PHP classes.

.. _`OPcache`: https://www.php.net/opcache
.. _`PSR-2`: https://www.php-fig.org/psr/psr-2/
