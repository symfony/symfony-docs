.. index::
   single: VarExporter
   single: Components; VarExporter

The VarExporter Component
=========================

    The VarExporter component exports any serializable PHP data structure to
    plain PHP code and allows to instantiate and populate objects without
    calling their constructors.

Installation
------------

.. code-block:: terminal

    $ composer require --dev symfony/var-exporter

Alternatively, you can clone the `<https://github.com/symfony/var-exporter>`_ repository.

.. include:: /components/require_autoload.rst.inc

Exporting/Serializing Variables
-------------------------------

The main feature of this component is to serialize PHP data structures to plain
PHP code, similar to PHP's :phpfunction:`var_export` function::

    use Symfony\Component\VarExporter\VarExporter;

    $exported = VarExporter::export($someVariable);
    // store the $exported data in some file or cache system for later reuse
    $data = file_put_contents('exported.php', $exported);

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
        protected $foo;
        private $bar;

        protected function setBar($bar)
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

    <?php
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

Instantiating PHP Classes
-------------------------

The other main feature provided by this component is an instantiator which can
create objects and set their properties without calling their constructors or
any other methods::

    use Symfony\Component\VarExporter\Instantiator;

    // creates an empty instance of Foo
    $fooObject = Instantiator::instantiate(Foo::class);

    // creates a Foo instance and sets one of its properties
    $fooObject = Instantiator::instantiate(Foo::class, ['propertyName' => $propertyValue]);

    // creates a Foo instance and sets a private property defined on its parent Bar class
    $fooObject = Instantiator::instantiate(Foo::class, [], [
        Bar::class => ['privateBarProperty' => $propertyValue],
    ]);

Instances of ``ArrayObject``, ``ArrayIterator`` and ``SplObjectHash`` can be
created by using the special ``"\0"`` property name to define their internal value::

    // Creates an SplObjectHash where $info1 is associated to $object1, etc.
    $theObject = Instantiator::instantiate(SplObjectStorage::class, [
        "\0" => [$object1, $info1, $object2, $info2...]
    ]);

    // creates an ArrayObject populated with $inputArray
    $theObject = Instantiator::instantiate(ArrayObject::class, [
        "\0" => [$inputArray]
    ]);

.. _`OPCache`: https://php.net/opcache
.. _`PSR-2`: https://www.php-fig.org/psr/psr-2/
