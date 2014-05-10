.. index::
    single: PropertyAccess
    single: Components; PropertyAccess

The PropertyAccess Component
============================

    The PropertyAccess component provides function to read and write from/to an
    object or array using a simple string notation.

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer</components/using_components>` (``symfony/property-access`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/PropertyAccess).

Usage
-----

The entry point of this component is the
:method:`PropertyAccess::createPropertyAccessor<Symfony\\Component\\PropertyAccess\\PropertyAccess::createPropertyAccessor>`
factory. This factory will create a new instance of the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` class with the
default configuration::

    use Symfony\Component\PropertyAccess\PropertyAccess;

    $accessor = PropertyAccess::createPropertyAccessor();

.. versionadded:: 2.3
    The :method:`Symfony\\Component\\PropertyAccess\\PropertyAccess::createPropertyAccessor`
    method was introduced in Symfony 2.3. Previously, it was called ``getPropertyAccessor()``.

Reading from Arrays
-------------------

You can read an array with the
:method:`PropertyAccessor::getValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue>`
method. This is done using the index notation that is used in PHP::

    // ...
    $person = array(
        'first_name' => 'Wouter',
    );

    echo $accessor->getValue($person, '[first_name]'); // 'Wouter'
    echo $accessor->getValue($person, '[age]'); // null

As you can see, the method will return ``null`` if the index does not exists.

You can also use multi dimensional arrays::

    // ...
    $persons = array(
        array(
            'first_name' => 'Wouter',
        ),
        array(
            'first_name' => 'Ryan',
        )
    );

    echo $accessor->getValue($persons, '[0][first_name]'); // 'Wouter'
    echo $accessor->getValue($persons, '[1][first_name]'); // 'Ryan'

Reading from Objects
--------------------

The ``getValue`` method is a very robust method, and you can see all of its
features when working with objects.

Accessing public Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To read from properties, use the "dot" notation::

    // ...
    $person = new Person();
    $person->firstName = 'Wouter';

    echo $accessor->getValue($person, 'firstName'); // 'Wouter'

    $child = new Person();
    $child->firstName = 'Bar';
    $person->children = array($child);

    echo $accessor->getValue($person, 'children[0].firstName'); // 'Bar'

.. caution::

    Accessing public properties is the last option used by ``PropertyAccessor``.
    It tries to access the value using the below methods first before using
    the property directly. For example, if you have a public property that
    has a getter method, it will use the getter.

Using Getters
~~~~~~~~~~~~~

The ``getValue`` method also supports reading using getters. The method will
be created using common naming conventions for getters. It camelizes the
property name (``first_name`` becomes ``FirstName``) and prefixes it with
``get``. So the actual method becomes ``getFirstName``::

    // ...
    class Person
    {
        private $firstName = 'Wouter';

        public function getFirstName()
        {
            return $this->firstName;
        }
    }

    $person = new Person();

    echo $accessor->getValue($person, 'first_name'); // 'Wouter'

Using Hassers/Issers
~~~~~~~~~~~~~~~~~~~~

And it doesn't even stop there. If there is no getter found, the accessor will
look for an isser or hasser. This method is created using the same way as
getters, this means that you can do something like this::

    // ...
    class Person
    {
        private $author = true;
        private $children = array();

        public function isAuthor()
        {
            return $this->author;
        }

        public function hasChildren()
        {
            return 0 !== count($this->children);
        }
    }

    $person = new Person();

    if ($accessor->getValue($person, 'author')) {
        echo 'He is an author';
    }
    if ($accessor->getValue($person, 'children')) {
        echo 'He has children';
    }

This will produce: ``He is an author``

Magic ``__get()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~

The ``getValue`` method can also use the magic ``__get`` method::

    // ...
    class Person
    {
        private $children = array(
            'Wouter' => array(...),
        );

        public function __get($id)
        {
            return $this->children[$id];
        }
    }

    $person = new Person();

    echo $accessor->getValue($person, 'Wouter'); // array(...)

Magic ``__call()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~~

At last, ``getValue`` can use the magic ``__call`` method, but you need to
enable this feature by using :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    class Person
    {
        private $children = array(
            'wouter' => array(...),
        );

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return isset($this->children[$property])
                    ? $this->children[$property]
                    : null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }
    }

    $person = new Person();

    // Enable magic __call
    $accessor = PropertyAccess::getPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    echo $accessor->getValue($person, 'wouter'); // array(...)

.. versionadded:: 2.3
    The use of magic ``__call()`` method was introduced in Symfony 2.3.

.. caution::

    The ``__call`` feature is disabled by default, you can enable it by calling
    :method:`PropertyAccessorBuilder::enableMagicCallEnabled<Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::enableMagicCallEnabled>`
    see `Enable other Features`_.

Writing to Arrays
-----------------

The ``PropertyAccessor`` class can do more than just read an array, it can
also write to an array. This can be achieved using the
:method:`PropertyAccessor::setValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue>`
method::

    // ...
    $person = array();

    $accessor->setValue($person, '[first_name]', 'Wouter');

    echo $accessor->getValue($person, '[first_name]'); // 'Wouter'
    // or
    // echo $person['first_name']; // 'Wouter'

Writing to Objects
------------------

The ``setValue`` method has the same features as the ``getValue`` method. You
can use setters, the magic ``__set`` method or properties to set values::

    // ...
    class Person
    {
        public $firstName;
        private $lastName;
        private $children = array();

        public function setLastName($name)
        {
            $this->lastName = $name;
        }

        public function __set($property, $value)
        {
            $this->$property = $value;
        }

        // ...
    }

    $person = new Person();

    $accessor->setValue($person, 'firstName', 'Wouter');
    $accessor->setValue($person, 'lastName', 'de Jong');
    $accessor->setValue($person, 'children', array(new Person()));

    echo $person->firstName; // 'Wouter'
    echo $person->getLastName(); // 'de Jong'
    echo $person->children; // array(Person());

You can also use ``__call`` to set values but you need to enable the feature,
see `Enable other Features`_.

.. code-block:: php

    // ...
    class Person
    {
        private $children = array();

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return isset($this->children[$property])
                    ? $this->children[$property]
                    : null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }

    }

    $person = new Person();

    // Enable magic __call
    $accessor = PropertyAccess::getPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    $accessor->setValue($person, 'wouter', array(...));

    echo $person->getWouter(); // array(...)

Checking Property Paths
-----------------------

.. versionadded:: 2.5
    The
    :method:`PropertyAccessor::isReadable <Symfony\\Component\\PropertyAccess\\PropertyAccessor::isReadable>`
    and
    :method:`PropertyAccessor::isWritable <Symfony\\Component\\PropertyAccess\\PropertyAccessor::isWritable>`
    methods were introduced in Symfony 2.5.

When you want to check whether
:method:`PropertyAccessor::getValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue>`
can safely be called without actually calling that method, you can use
:method:`PropertyAccessor::isReadable<Symfony\\Component\\PropertyAccess\\PropertyAccessor::isReadable>`
instead::

    $person = new Person();

    if ($accessor->isReadable($person, 'firstName') {
        // ...
    }

The same is possible for :method:`PropertyAccessor::setValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue>`:
Call the
:method:`PropertyAccessor::isWritable<Symfony\\Component\\PropertyAccess\\PropertyAccessor::isWritable>`
method to find out whether a property path can be updated::

    $person = new Person();

    if ($accessor->isWritable($person, 'firstName') {
        // ...
    }

Mixing Objects and Arrays
-------------------------

You can also mix objects and arrays::

    // ...
    class Person
    {
        public $firstName;
        private $children = array();

        public function setChildren($children)
        {
            $this->children = $children;
        }

        public function getChildren()
        {
            return $this->children;
        }
    }

    $person = new Person();

    $accessor->setValue($person, 'children[0]', new Person);
    // equal to $person->getChildren()[0] = new Person()

    $accessor->setValue($person, 'children[0].firstName', 'Wouter');
    // equal to $person->getChildren()[0]->firstName = 'Wouter'

    echo 'Hello '.$accessor->getValue($person, 'children[0].firstName'); // 'Wouter'
    // equal to $person->getChildren()[0]->firstName

Enable other Features
~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` can be
configured to enable extra features. To do that you could use the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    $accessorBuilder = PropertyAccess::createPropertyAccessorBuilder();

    // Enable magic __call
    $accessorBuilder->enableMagicCall();

    // Disable magic __call
    $accessorBuilder->disableMagicCall();

    // Check if magic __call handling is enabled
    $accessorBuilder->isMagicCallEnabled() // true or false

    // At the end get the configured property accessor
    $accessor = $accessorBuilder->getPropertyAccessor();

    // Or all in one
    $accessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

Or you can pass parameters directly to the constructor (not the recommended way)::

    // ...
    $accessor = new PropertyAccessor(true) // this enables handling of magic __call


.. _Packagist: https://packagist.org/packages/symfony/property-access
