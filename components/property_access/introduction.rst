.. index::
    single: PropertyAccess
    single: Components; PropertyAccess

The PropertyAccess Component
============================

    The PropertyAccess component provides function to read and write from/to an
    object or array using a simple string notation.

.. versionadded:: 2.2
    The PropertyAccess Component is new to Symfony 2.2. Previously, the
    ``PropertyPath`` class was located in the ``Form`` component.

Installation
------------

You can install the component in two different ways:

* Use the official Git repository (https://github.com/symfony/PropertyAccess);
* :doc:`Install it via Composer</components/using_components>` (``symfony/property-access`` on `Packagist`_).

Usage
-----

The entry point of this component is the
:method:`PropertyAccess::getPropertyAccessor<Symfony\\Component\\PropertyAccess\\PropertyAccess::getPropertyAccessor>`
factory. This factory will create a new instance of the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` class with the
default configuration::

    use Symfony\Component\PropertyAccess\PropertyAccess;

    $accessor = PropertyAccess::getPropertyAccessor();

Reading from Arrays
-------------------

You can read an array with the
:method:`PropertyAccessor::getValue<Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue>`
method. This is done using the index notation that is used in PHP::

    // ...
    $person = array(
        'first_name' => 'Wouter',
    );

    echo $accessor->getValue($persons, '[first_name]'); // 'Wouter'
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

Magic Methods
~~~~~~~~~~~~~

At last, ``getValue`` can use the magic ``__get`` method too::

    // ...
    class Person
    {
        private $children = array(
            'wouter' => array(...),
        );

        public function __get($id)
        {
            return $this->children[$id];
        }
    }

    $person = new Person();

    echo $accessor->getValue($person, 'Wouter'); // array(...)

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
can use setters, the magic ``__set`` or properties to set values::

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
            return $this->children;
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

.. _Packagist: https://packagist.org/packages/symfony/property-access
