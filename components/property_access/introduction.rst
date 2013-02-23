.. index::
    single: PropertyAccess
    single: Components; PropertyAccess

The PropertyAccess Component
============================

    PropertyAccess component provides function to read and write from/to an
    object or array using a simple string notation.

.. versionadded:: 2.2
    The PropertyAccess Component is new to Symfony 2.2. Previously, the
    ``PropertyPath`` class was located in the ``Form`` component.

Installation
------------

You can install the component in two different ways:

* Use the official Git repository (https://github.com/symfony/PropertyAccess);
* :doc:`Install it via Composer</components/using_components>` * (``symfony/property-access`` on `Packagist`_).

Usage
-----

The entry point of this component is the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccess::getPropertyAccessor`
factory. This factory will create a new instance of the
:class:`Symfony\\Component\\PropertyAccess\PropertyAccessor` class with the
default configuration::

    use Symfony\Component\PropertyAccess\PropertyAccess;

    $accessor = PropertyAccess:getPropertyAccessor();

Reading from arrays
-------------------

You can read an array with the
:method:`Symfony\\Component\\PropertyAccess\PropertyAccessor::getValue`
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

Reading from objects
--------------------

The ``getValue`` method is a very robust method. You can see all features if
you are working with objects.

Using properties
~~~~~~~~~~~~~~~~

We can read properties without the index notation, instead we use the dot
notation::

    // ...
    $person = new Person();
    $person->firstName = 'Wouter';

    echo $accessor->getValue($person, 'first_name'); // 'Wouter'

    $child = new Person();
    $child->firstName = 'Bar';
    $person->children = array($child);

    echo $accessor->getValue($person, 'children[0].first_name'); // 'Bar'

.. caution::

    This option is the last option used by the ``PropertyAccessor``. It tries
    to find the other options before using the property. If you have a public
    property that have a getter to, it will use the getter.

Using getters
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

Using hassers/issers
~~~~~~~~~~~~~~~~~~~~

And it doesn't even stop there. If there is no getter found, the accessor will
look for a isser or hasser. This method is created using the same way as
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

At last, the ``getValue`` can use the magic ``__get`` too::

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

Writing to arrays
-----------------

The ``PropertyAccessor`` class can do more than just reading an array, it can
also write to an array. This can be achieved using the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue`
method::

    // ...
    $person = array();

    $accessor->setValue($person, '[first_name]', 'Wouter');

    echo $accessor->getValue($person, '[first_name]'); // 'Wouter'
    // or
    // echo $person['first_name']; // 'Wouter'

Writing to objects
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

Mixing objects and arrays
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
